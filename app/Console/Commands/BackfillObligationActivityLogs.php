<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\Obligation;
use Illuminate\Console\Command;

class BackfillObligationActivityLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backfill-obligation-activity-logs {--dry-run : Preview the results without writing any changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill activity_logs.obligation_id for pre-existing logs by matching the OBR# embedded in their description text against currently-existing obligations only. Rows whose obr_no is ambiguous (matches zero or multiple current obligations) are left untouched.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        // Cache every obr_no currently in use, grouped, so we can tell in O(1) whether
        // a given obr_no unambiguously identifies exactly one obligation right now.
        $obligationsByObrNo = Obligation::select('id', 'obr_no')->get()->groupBy('obr_no');

        $candidates = ActivityLog::whereNull('obligation_id')
            ->where('description', 'like', '%OBR#%')
            ->get(['id', 'description']);

        $this->info("Scanning {$candidates->count()} log(s) with no obligation_id that mention \"OBR#\"...");

        $backfilled = 0;
        $skippedNoMatch = 0;
        $skippedAmbiguous = 0;
        $skippedNoObrToken = 0;

        foreach ($candidates as $log) {
            if (! preg_match('/OBR#\s*([^\s,;]+)/', $log->description, $matches)) {
                $skippedNoObrToken++;
                continue;
            }

            $obrNo = $matches[1];
            $matchingObligations = $obligationsByObrNo->get($obrNo);

            if (! $matchingObligations || $matchingObligations->isEmpty()) {
                $skippedNoMatch++;
                continue;
            }

            if ($matchingObligations->count() > 1) {
                $skippedAmbiguous++;
                continue;
            }

            $obligationId = $matchingObligations->first()->id;

            if (! $dryRun) {
                $log->update(['obligation_id' => $obligationId]);
            }

            $backfilled++;
        }

        $this->newLine();
        $this->line(($dryRun ? '[DRY RUN] Would backfill' : 'Backfilled').": {$backfilled}");
        $this->line("Skipped (obr_no not found among current obligations, e.g. deleted-and-not-recreated): {$skippedNoMatch}");
        $this->line("Skipped (obr_no matches more than one current obligation, ambiguous): {$skippedAmbiguous}");
        $this->line("Skipped (description had no readable OBR# token, e.g. old Disbursement logs never included one): {$skippedNoObrToken}");

        if ($dryRun) {
            $this->newLine();
            $this->comment('Run again without --dry-run to apply these changes.');
        }

        return self::SUCCESS;
    }
}
