<?php

namespace App\Http\Controllers;

use App\Models\AccountCode;
use App\Models\AllotmentClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function destroy(AccountCode $account_code): RedirectResponse
    {
        $account_code->delete();

        return redirect()->route('account_codes.index')->with('status', 'Account Code <strong>' . $account_code->code . '</strong> with description <strong>' . $account_code->description . '</strong> has been deleted successfully!');
    }
}
