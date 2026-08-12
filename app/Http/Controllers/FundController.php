<?php

namespace App\Http\Controllers;

use App\Models\Appropriation;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\Fund;
use App\Models\ObligationAmount;
use App\Models\Office;
use App\Models\OfficeAllotmentClass;
use App\Models\Realignment;
use App\Models\Supplemental;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FundController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10); // Default to 10 rows per page
        $search = $request->input('search');

        // Get sorting parameters from query string, default to 'id' and 'desc'
        $sortBy = $request->query('sort_by', 'id');
        $sortOrder = $request->query('sort_order', 'asc');

        // Query Funds
        $query = Fund::query();

        if ($search) {
            $query->where('fund', 'like', "%{$search}%")
                ->orWhere('fund_type', 'like', "%{$search}%")
                ->orWhere('fund_code', 'like', "%{$search}%");
        }

        // Apply sorting before pagination
        if ($perPage == 'all') {
            $funds = $query->orderBy($sortBy, $sortOrder)->get();
        } else {
            $funds = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);
        }

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'Funds']
        ];

        return view('funds.index', compact('funds', 'perPage', 'search', 'sortBy', 'sortOrder', 'breadcrumb'))->with('status', session('status'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'fund' => 'required|string|max:255|unique:funds,fund',
            'fund_type' => 'required|string|max:255',
            'fund_code' => 'required|string|max:255'
        ]);

        $fund = Fund::create($validated);

        return redirect()->back()->with('status', 'Fund: <strong>' . $fund->fund . '</strong> with Fund Code: <strong>' . $fund->fund_code . '</strong> has been created successfully!');
    }

    public function update(Request $request, Fund $fund): RedirectResponse
    {
        $validated = $request->validate([
            'edit_fund' => 'required|string|max:255|unique:funds,fund,' . $fund->id,
            'edit_fund_type' => 'required|string|max:255',
            'edit_fund_code' => 'required|string|max:255'
        ]);

        $fund->update([
            'fund' => $validated['edit_fund'],
            'fund_type' => $validated['edit_fund_type'],
            'fund_code' => $validated['edit_fund_code']
        ]);

        return redirect()->back()->with('status', 'Fund: <strong>' . $fund->fund . '</strong> with Fund Code: <strong>' . $fund->fund_code . '</strong> has been updated successfully!');
    }

    public function destroy(Request $request, Fund $fund): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Store details before deletion
            $fundCode = $fund->fund;

            // System Validation: Check if fund is in use
            // Note: Using 'fund' column to match relationships

            // 1. Check if fund is used in Office Allotment Classes
            $officeAllotmentClassesCount = OfficeAllotmentClass::where('fund', $fundCode)->count();
            
            if ($officeAllotmentClassesCount > 0) {
                DB::rollBack();
                return redirect()->route('funds.index', array_filter($request->only(['per_page', 'search'])))
                    ->with('error', 
                        "Cannot delete Fund <strong>{$fundCode}</strong>. " .
                        "This fund is currently assigned to <strong>{$officeAllotmentClassesCount}</strong> Office Allotment Class(es). " .
                        "Please remove or reassign the office allotment classes first before deleting this fund."
                    );
            }

            // 2. Check if fund is used in Offices
            $officesCount = Office::where('fund', $fundCode)->count();
            
            if ($officesCount > 0) {
                DB::rollBack();
                return redirect()->route('funds.index', array_filter($request->only(['per_page', 'search'])))
                    ->with('error', 
                        "Cannot delete Fund <strong>{$fundCode}</strong>. " .
                        "This fund is currently assigned to <strong>{$officesCount}</strong> Office(s). " .
                        "Please remove or reassign the offices first before deleting this fund."
                    );
            }

            // 3. Check if fund is referenced through Office Allotment Classes in Appropriations
            $appropriationsCount = Appropriation::whereHas('officeAllotmentClass', function($query) use ($fundCode) {
                $query->where('fund', $fundCode);
            })->count();
            
            if ($appropriationsCount > 0) {
                DB::rollBack();
                return redirect()->route('funds.index', array_filter($request->only(['per_page', 'search'])))
                    ->with('error', 
                        "Cannot delete Fund <strong>{$fundCode}</strong>. " .
                        "This fund has <strong>{$appropriationsCount}</strong> appropriation(s) associated with it through Office Allotment Classes. " .
                        "Please reassign or remove the appropriations first before deleting this fund."
                    );
            }

            // 4. Check if fund is referenced in Obligations through Appropriations
            $obligationsCount = ObligationAmount::whereHas('appropriation.officeAllotmentClass', function($query) use ($fundCode) {
                $query->where('fund', $fundCode);
            })->distinct('obligation_id')->count('obligation_id');
            
            if ($obligationsCount > 0) {
                DB::rollBack();
                return redirect()->route('funds.index', array_filter($request->only(['per_page', 'search'])))
                    ->with('error', 
                        "Cannot delete Fund <strong>{$fundCode}</strong>. " .
                        "This fund has <strong>{$obligationsCount}</strong> obligation(s) associated with it through appropriations. " .
                        "Please delete the related obligations first before removing this fund."
                    );
            }

            // 5. Check if fund is referenced in Realignments through Appropriations
            $realignmentsCount = Realignment::whereHas('appropriation.officeAllotmentClass', function($query) use ($fundCode) {
                $query->where('fund', $fundCode);
            })->count();
            
            if ($realignmentsCount > 0) {
                DB::rollBack();
                return redirect()->route('funds.index', array_filter($request->only(['per_page', 'search'])))
                    ->with('error', 
                        "Cannot delete Fund <strong>{$fundCode}</strong>. " .
                        "This fund has <strong>{$realignmentsCount}</strong> realignment/augmentation(s) associated with it. " .
                        "Please delete the related realignments first before removing this fund."
                    );
            }

            // 6. Check if fund is referenced in Supplementals through Appropriations
            $supplementalsCount = Supplemental::whereHas('appropriation.officeAllotmentClass', function($query) use ($fundCode) {
                $query->where('fund', $fundCode);
            })->count();
            
            if ($supplementalsCount > 0) {
                DB::rollBack();
                return redirect()->route('funds.index', array_filter($request->only(['per_page', 'search'])))
                    ->with('error', 
                        "Cannot delete Fund <strong>{$fundCode}</strong>. " .
                        "This fund has <strong>{$supplementalsCount}</strong> supplemental/reversion(s) associated with it. " .
                        "Please delete the related supplementals first before removing this fund."
                    );
            }

            // All validations passed - proceed with deletion
            $fund->delete();

            DB::commit();

            return redirect()->route('funds.index', array_filter($request->only(['per_page', 'search'])))
                ->with('status', 
                    'Fund: <strong>' . $fundCode . '</strong> has been deleted successfully!'
                );

        } catch (\Throwable $e) {
            DB::rollBack();
            
            Log::error('Fund Delete Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'fund_id' => $fund->id ?? null,
                'fund_code' => $fund->fund ?? null,
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            return redirect()->route('funds.index', array_filter($request->only(['per_page', 'search'])))
                ->with('error', 'An error occurred while deleting the fund: ' . $e->getMessage());
        }
    }


}
