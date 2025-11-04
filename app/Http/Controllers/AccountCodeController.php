<?php

namespace App\Http\Controllers;

use App\Models\AccountCode;
use App\Models\AllotmentClass;
use App\Models\Appropriation;
use App\Models\ObligationAmount;
use App\Models\Realignment;
use App\Models\Supplemental;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AccountCodeController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10); // Default to 10 rows per page
        $search = $request->input('search');

        // Get sorting parameters from query string, default to 'id' and 'desc'
        $sortBy = $request->query('sort_by', 'code');
        $sortOrder = $request->query('sort_order', 'asc');

        // Query account codes
        $query = AccountCode::with('allotmentClass');

        if ($search) {
            $query->where('code', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('class', 'like', "%{$search}%");
        }

        // Apply sorting before pagination
        if ($perPage == 'all') {
            $account_codes = $query->orderBy($sortBy, $sortOrder)->get();
        } else {
            $account_codes = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);
        }

        // Get funds and sort by fund (locally)
        $allotment_classes = AllotmentClass::all()->sortBy('id');

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'Accounts']
        ];

        return view('account_codes.index', compact('account_codes', 'perPage', 'search', 'sortBy', 'sortOrder', 'allotment_classes', 'breadcrumb'))->with('status', session('status'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|max:255|unique:account_codes,code',
            'description' => 'required|string|max:255',
            'class' => 'required|string|max:255'
        ]);

        $account_code = AccountCode::create($validated);

        return redirect()->route('account_codes.index')->with('status', 'Account Code <strong>' . $account_code->code . '</strong> with description <strong>' . $account_code->description . '</strong> has been created successfully!');
    }

    public function edit(AccountCode $account_code): View
    {
        // Get allotment_class and sort by description (locally)
        $allotment_classes = AllotmentClass::all()->sortBy('id');

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'Account Codes', 'route' => route('account_codes.index')],
            ['label' => 'Edit Accounts']
        ];

        return view('account_codes.edit', compact('account_code', 'allotment_classes', 'breadcrumb'));
    }

    public function update(Request $request, AccountCode $account_code): RedirectResponse
    {
        $validated = $request->validate([
            'edit_code' => 'required|string|max:255',
            'edit_description' => 'required|string|max:255',
            'edit_class' => 'required|string|max:255'
        ]);

        $account_code->update([
            'code' => $validated['edit_code'],
            'description' => $validated['edit_description'],
            'class' => $validated['edit_class']
        ]);

        return redirect()->route('account_codes.index')->with('status', 'Account Code <strong>' . $account_code->code . '</strong> with description <strong>' . $account_code->description . '</strong> has been updated successfully!');
    }

    public function destroy(Request $request, AccountCode $account_code): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Store details before deletion
            $code = $account_code->code;
            $description = $account_code->description;

            // System Validation: Check if account code is in use

            // 1. Check if account code is used in Appropriations (Registry Accounts)
            $appropriationsCount = Appropriation::where('account_code', $code)->count();
            
            if ($appropriationsCount > 0) {
                DB::rollBack();
                return redirect()->route('account_codes.index', array_filter($request->only(['per_page', 'search'])))
                    ->with('error', 
                        "Cannot delete Account Code <strong>{$code}</strong>. " .
                        "This account code is currently used in <strong>{$appropriationsCount}</strong> Registry Account(s) (Appropriations). " .
                        "Please remove or reassign the appropriations first before deleting this account code."
                    );
            }

            // 2. Check if account code is referenced in Obligations
            // This checks if any appropriation with this account code has obligations
            $obligationsCount = ObligationAmount::whereHas('appropriation', function($query) use ($code) {
                $query->where('account_code', $code);
            })->distinct('obligation_id')->count('obligation_id');
            
            if ($obligationsCount > 0) {
                DB::rollBack();
                return redirect()->route('account_codes.index', array_filter($request->only(['per_page', 'search'])))
                    ->with('error', 
                        "Cannot delete Account Code <strong>{$code}</strong>. " .
                        "This account code has <strong>{$obligationsCount}</strong> obligation(s) associated with it through Registry Accounts. " .
                        "Please delete the related obligations first before removing this account code."
                    );
            }

            // 3. Optional: Check for Realignments
            $realignmentsCount = Realignment::whereHas('appropriation', function($query) use ($code) {
                $query->where('account_code', $code);
            })->count();
            
            if ($realignmentsCount > 0) {
                DB::rollBack();
                return redirect()->route('account_codes.index', array_filter($request->only(['per_page', 'search'])))
                    ->with('error', 
                        "Cannot delete Account Code <strong>{$code}</strong>. " .
                        "This account code has <strong>{$realignmentsCount}</strong> realignment/augmentation(s) associated with it. " .
                        "Please delete the related realignments first before removing this account code."
                    );
            }

            // 4. Optional: Check for Supplementals
            $supplementalsCount = Supplemental::whereHas('appropriation', function($query) use ($code) {
                $query->where('account_code', $code);
            })->count();
            
            if ($supplementalsCount > 0) {
                DB::rollBack();
                return redirect()->route('account_codes.index', array_filter($request->only(['per_page', 'search'])))
                    ->with('error', 
                        "Cannot delete Account Code <strong>{$code}</strong>. " .
                        "This account code has <strong>{$supplementalsCount}</strong> supplemental/reversion(s) associated with it. " .
                        "Please delete the related supplementals first before removing this account code."
                    );
            }

            // All validations passed - proceed with deletion
            $account_code->delete();

            DB::commit();

            return redirect()->route('account_codes.index', array_filter($request->only(['per_page', 'search'])))
                ->with('status', 
                    'Account Code <strong>' . $code . '</strong> with description <strong>' . $description . '</strong> has been deleted successfully!'
                );

        } catch (\Throwable $e) {
            DB::rollBack();
            
            Log::error('Account Code Delete Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'account_code_id' => $account_code->id ?? null,
                'code' => $account_code->code ?? null,
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);
            
            return redirect()->route('account_codes.index', array_filter($request->only(['per_page', 'search'])))
                ->with('error', 'An error occurred while deleting the account code: ' . $e->getMessage());
        }
    }
}
