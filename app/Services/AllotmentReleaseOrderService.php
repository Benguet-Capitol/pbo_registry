<?php

namespace App\Services;

use App\Models\AllotmentReleaseOrder;
use App\Models\AllotmentReleaseOrderItem;
use App\Models\Appropriation;
use App\Models\Employee;
use App\Models\OfficeAllotmentClass;
use App\Models\Supplemental;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Shared ARO number generation, release-amount computation, and item
 * persistence logic — used both by AllotmentReleaseOrderController's manual
 * create/edit flow and by the auto-creation triggered from Appropriation and
 * Supplemental creation (see createFromNewFunding()).
 */
class AllotmentReleaseOrderService
{
    /**
     * Office (id 12) that Provincial Budget Officer signatories are sourced from.
     */
    public const PBO_OFFICE_ID = '12';

    /**
     * ARO No. format is {ClassPrefix}-{Year}-{Month}-{Sequence}, but the
     * Sequence itself is one continuous counter per year — shared across
     * every office, allotment class, and month, not scoped to the class
     * prefix in the number. E.g. PS-2026-08-001 can be followed by
     * MOOE-2026-08-002 or CO-2026-09-003; only a new year resets it to 001.
     */
    public function generateAroNo(int $officeAllotmentClassesId, Carbon $dateOfIssue, bool $lock = true): string
    {
        $officeAllotmentClass = OfficeAllotmentClass::with('allotmentClass')->findOrFail($officeAllotmentClassesId);
        $classCode = optional($officeAllotmentClass->allotmentClass)->class;
        // Capital Outlay's ARO No. prefix reads "CapEx", not the raw "CO" class code.
        $classPrefix = $classCode === 'CO' ? 'CapEx' : ($classCode ?? 'ARO');

        $year = (int) $dateOfIssue->format('Y');
        $month = $dateOfIssue->format('m');

        $query = AllotmentReleaseOrder::where('year', $year);
        if ($lock) {
            $query->lockForUpdate();
        }

        $lastSequence = $query->pluck('aro_no')->map(function ($aroNo) {
            $segments = explode('-', $aroNo);

            return (int) end($segments);
        })->max() ?? 0;

        $nextSequence = str_pad((string) ($lastSequence + 1), 3, '0', STR_PAD_LEFT);

        return "{$classPrefix}-{$year}-{$month}-{$nextSequence}";
    }

    /**
     * Fixes ARO Nos. that were generated before the CapEx prefix rule
     * existed — e.g. CO-2026-08-004 for a Capital Outlay ARO gets corrected
     * to CapEx-2026-08-004 on save, leaving the year/month/sequence intact.
     */
    public function normalizeAroNoPrefix(string $aroNo, int $officeAllotmentClassesId): string
    {
        $officeAllotmentClass = OfficeAllotmentClass::with('allotmentClass')->find($officeAllotmentClassesId);
        $classCode = optional(optional($officeAllotmentClass)->allotmentClass)->class;
        $expectedPrefix = $classCode === 'CO' ? 'CapEx' : ($classCode ?? 'ARO');

        $segments = explode('-', $aroNo);

        if (($segments[0] ?? null) === $classCode && $classCode !== $expectedPrefix) {
            $segments[0] = $expectedPrefix;

            return implode('-', $segments);
        }

        return $aroNo;
    }

    /**
     * The Employee seeded as the default Provincial Budget Officer signatory
     * (may be null if that designation isn't seeded yet under the PBO office).
     */
    public function defaultPbo(): ?Employee
    {
        return Employee::where('office', self::PBO_OFFICE_ID)
            ->where('designation', 'Provincial Budget Officer')
            ->first();
    }

    /**
     * The Employee seeded as the default Provincial Governor signatory (may
     * be null if that designation isn't seeded yet).
     */
    public function defaultGovernor(): ?Employee
    {
        return Employee::where('designation', 'Provincial Governor')->first();
    }

    /**
     * Fiscal quarter (1-4) for a given date.
     */
    public function currentQuarter(Carbon $date): int
    {
        return match (true) {
            $date->month <= 3 => 1,
            $date->month <= 6 => 2,
            $date->month <= 9 => 3,
            default => 4,
        };
    }

    /**
     * Sum of whichever quarterN columns fall after the given quarter — shared
     * by both Annual (Appropriation) and Supplemental Budget (Supplemental)
     * records since both tables have identically-named quarter1..quarter4 columns.
     */
    public function calculateForLaterRelease($record, int $currentQuarter): float
    {
        $forLater = 0;
        if ($currentQuarter < 2) {
            $forLater += (float) $record->quarter2;
        }
        if ($currentQuarter < 3) {
            $forLater += (float) $record->quarter3;
        }
        if ($currentQuarter < 4) {
            $forLater += (float) $record->quarter4;
        }

        return $forLater;
    }

    /**
     * Total already committed (sum of this_release) against a given
     * appropriation across every *other* existing ARO — used both to guard
     * against double-releasing the same money and to show a live "Balance"
     * in the create/edit Account Codes table.
     *
     * Annual Budget and Reenacted Budget releases draw from the appropriation's
     * own authorized amount, while Supplemental Budget releases draw from a
     * separate Supplemental amount for the same account code — so a Supplemental
     * release must not count against (or be blocked by) an Annual/Reenacted
     * release on the same appropriation_id, and vice versa.
     *
     * A Supplemental Budget release also draws from ONE specific board/
     * Sanggunian resolution (SB No. / supplemental_no) — two different SB Nos.
     * against the same appropriation are two unrelated pots of money, so
     * $supplementalNo (when given) additionally scopes the sum to AROs sharing
     * that same SB No., instead of pooling every Supplemental Budget release
     * ever made against this appropriation regardless of which resolution
     * funded it.
     */
    public function alreadyCommittedForAppropriation(
        int $appropriationId,
        string $fundSource,
        ?int $excludeAroId = null,
        ?string $supplementalNo = null
    ): float {
        $sourceGroup = $fundSource === 'Supplemental Budget'
            ? ['Supplemental Budget']
            : ['Annual Budget', 'Reenacted Budget'];

        return (float) AllotmentReleaseOrderItem::where('appropriation_id', $appropriationId)
            ->when($excludeAroId, fn ($q) => $q->where('allotment_release_order_id', '!=', $excludeAroId))
            ->whereHas('allotmentReleaseOrder', function ($q) use ($sourceGroup, $fundSource, $supplementalNo) {
                $q->whereIn('fund_source', $sourceGroup);

                if ($fundSource === 'Supplemental Budget' && $supplementalNo) {
                    $q->where('supplemental_no', $supplementalNo);
                }
            })
            ->sum('this_release');
    }

    /**
     * The existing ARO (if any) that already released money for the given
     * appropriation under a specific Supplemental batch (SB No. / basis_no) —
     * used when a Supplemental is edited, to prompt "edit that ARO too?"
     * instead of silently offering to create a duplicate one. Null basis_no
     * (SB No. not yet filled in) can never match an ARO, since an ARO's own
     * supplemental_no is required whenever its fund_source is Supplemental
     * Budget — see validateAro().
     *
     * Deliberately does NOT filter by office_allotment_classes_id: a Special
     * Education Fund ARO consolidates account codes across every SEF office
     * sharing the same Allotment Class/year (see getAppropriationsByClass()'s
     * sefOfficeAllotmentClasses()), anchored at whichever one of those offices
     * the user happened to pick when creating it — so the anchor can differ
     * from $appropriationId's own office. $appropriationId already narrows
     * this to one specific account code, and requiring it to actually be an
     * item on the ARO is definitive proof the money was released through it —
     * an office match on top of that is redundant for a normal (non-SEF)
     * office and wrongly exclusionary for a SEF one.
     */
    public function findAroForSupplemental(int $appropriationId, ?string $basisNo): ?AllotmentReleaseOrder
    {
        if (! $basisNo) {
            return null;
        }

        return AllotmentReleaseOrder::where('fund_source', 'Supplemental Budget')
            ->where('supplemental_no', $basisNo)
            ->whereHas('items', fn ($q) => $q->where('appropriation_id', $appropriationId))
            ->first();
    }

    /**
     * Persist the checked account-code rows for an ARO, computing For Later
     * Release / Previously Released Amount / This Release server-side —
     * never trusting client-submitted computed values.
     *
     * Callers must ensure the ARO's own prior items (if any) have already
     * been deleted/don't yet exist, so alreadyCommittedForAppropriation()
     * here naturally reflects every *other* ARO's usage without needing to
     * exclude this ARO's id.
     */
    public function saveItems(AllotmentReleaseOrder $aro, array $validated): void
    {
        $dateOfIssue = Carbon::parse($validated['date_of_issue']);
        $quarter = $this->currentQuarter($dateOfIssue);

        foreach ($validated['appropriation_id'] as $index => $appropriationId) {
            $appropriation = Appropriation::with('officeAllotmentClass.offices')->findOrFail($appropriationId);
            $authorizedAppropriation = (float) ($validated['authorized_appropriation'][$index] ?? 0);

            // Use the appropriation's OWN office/allotment class here, not the ARO's
            // anchor office_allotment_classes_id — for a SEF-consolidated ARO, a
            // checked row's appropriation can belong to a *different* SEF office than
            // the one the user picked to open the ARO, and this Supplemental lookup
            // must still resolve against that row's real office to find its amount.
            $sourceRecord = $validated['fund_source'] === 'Supplemental Budget'
                ? Supplemental::where('appropriations_id', $appropriationId)
                    ->where('office_allotment_classes_id', $appropriation->office_allotment_class_id)
                    ->where('basis_no', $validated['supplemental_no'])
                    ->first()
                : $appropriation;

            $officeLabel = optional($appropriation->officeAllotmentClass->offices)->office_name;

            // Guard against two AROs releasing the same money: this row's
            // authorized amount can't exceed what's left after every other
            // existing ARO's usage of this appropriation.
            $sourceAmount = $sourceRecord
                ? (float) ($sourceRecord instanceof Supplemental ? $sourceRecord->amount : $sourceRecord->appropriation)
                : $authorizedAppropriation;
            $alreadyCommitted = $this->alreadyCommittedForAppropriation(
                $appropriationId,
                $validated['fund_source'],
                supplementalNo: $validated['supplemental_no'] ?? null,
            );
            $balance = $sourceAmount - $alreadyCommitted;

            if ($authorizedAppropriation > $balance + 0.005) {
                throw new \RuntimeException(
                    "Account Code {$appropriation->account_code} has only ".number_format(max($balance, 0), 2).
                    ' remaining balance (already committed to other ARO(s)), but '.
                    number_format($authorizedAppropriation, 2).' was entered.'
                );
            }

            $forLaterRelease = $sourceRecord ? $this->calculateForLaterRelease($sourceRecord, $quarter) : 0;
            $thisRelease = $authorizedAppropriation - $forLaterRelease;

            $previouslyReleased = (float) AllotmentReleaseOrderItem::where('appropriation_id', $appropriationId)
                ->whereHas('allotmentReleaseOrder', function ($q) use ($aro, $dateOfIssue) {
                    $q->where('id', '!=', $aro->id)
                        ->where('year', $dateOfIssue->year)
                        ->where('date_of_issue', '<', $dateOfIssue);
                })
                ->sum('this_release');

            // A single ARO can roll its checked account codes under multiple PPA
            // Codes (each with its own Subtotal on the printed form) — each row
            // types its own PPA Code, defaulting client-side to the ARO's header
            // PPA Code, but falling back here too in case that's ever blank.
            $rowPpaCode = $validated['row_ppa_code'][$index] ?? null;

            AllotmentReleaseOrderItem::create([
                'allotment_release_order_id' => $aro->id,
                'appropriation_id' => $appropriationId,
                'ppa_code' => $rowPpaCode ?: ($validated['ppa_code'] ?? null),
                'account_code' => $appropriation->account_code,
                'ppa_description' => $appropriation->description,
                'programs' => $appropriation->programs,
                'office_label' => $officeLabel,
                'authorized_appropriation' => $authorizedAppropriation,
                'for_later_release' => $forLaterRelease,
                'previously_released_amount' => $previouslyReleased,
                'this_release' => $thisRelease,
            ]);
        }
    }

    /**
     * Auto-creates an ARO releasing the full amount of one or more freshly
     * created Appropriation rows (Annual/Reenacted Budget) or Supplemental
     * rows (Supplemental Budget), triggered right after Appropriation/
     * Supplemental creation — see AppropriationController::store() and
     * SupplementalController::store().
     *
     * Returns null (creates nothing) when the ARO can't be validly built:
     * - Supplemental Budget requires a non-blank $supplementalNo (it becomes
     *   the ARO's supplemental_no, which saveItems() matches against each
     *   Supplemental row's basis_no — see that method's Supplemental lookup).
     * - Both signatories (Provincial Budget Officer, Provincial Governor)
     *   must already be seeded as Employees, since neither Appropriation nor
     *   Supplemental forms collect that data.
     *
     * @param  array<int, array{appropriation_id:int, authorized_appropriation:float}>  $items
     */
    public function createFromNewFunding(
        int $officeAllotmentClassesId,
        Carbon $dateOfIssue,
        string $fundSource,
        array $items,
        ?string $supplementalNo,
        int $createdBy
    ): ?AllotmentReleaseOrder {
        if ($items === []) {
            return null;
        }

        if ($fundSource === 'Supplemental Budget' && ! $supplementalNo) {
            return null;
        }

        $pbo = $this->defaultPbo();
        $governor = $this->defaultGovernor();

        if (! $pbo || ! $governor) {
            return null;
        }

        $officeAllotmentClass = OfficeAllotmentClass::with('offices')->findOrFail($officeAllotmentClassesId);
        $ppaCode = optional($officeAllotmentClass->offices)->ppa_code;

        return DB::transaction(function () use (
            $officeAllotmentClassesId, $dateOfIssue, $fundSource, $items, $supplementalNo, $createdBy, $pbo, $governor, $ppaCode
        ) {
            $aro = AllotmentReleaseOrder::create([
                'aro_no' => $this->generateAroNo($officeAllotmentClassesId, $dateOfIssue),
                'date_of_issue' => $dateOfIssue,
                'year' => $dateOfIssue->year,
                'office_allotment_classes_id' => $officeAllotmentClassesId,
                'fund_source' => $fundSource,
                'supplemental_no' => $supplementalNo,
                'ppa_code' => $ppaCode,
                'provincial_budget_officer_id' => $pbo->id,
                'provincial_budget_officer_title' => 'Provincial Budget Officer',
                'provincial_governor_id' => $governor->id,
                'provincial_governor_name' => null,
                'provincial_governor_title' => 'Provincial Governor',
                'created_by' => $createdBy,
            ]);

            $this->saveItems($aro, [
                'date_of_issue' => $dateOfIssue->toDateString(),
                'fund_source' => $fundSource,
                'supplemental_no' => $supplementalNo,
                'ppa_code' => $ppaCode,
                'appropriation_id' => array_column($items, 'appropriation_id'),
                'authorized_appropriation' => array_column($items, 'authorized_appropriation'),
                'row_ppa_code' => array_fill(0, count($items), null),
            ]);

            return $aro;
        });
    }
}
