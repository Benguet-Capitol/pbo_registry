<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\Fund;


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

        return redirect()->route('funds.index')->with('status', 'Fund: <strong>' . $fund->fund . '</strong> with Fund Code: <strong>' . $fund->fund_code . '</strong> has been created successfully!');
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

        return redirect()->route('funds.index')->with('status', 'Fund: <strong>' . $fund->fund . '</strong> with Fund Code: <strong>' . $fund->fund_code . '</strong> has been updated successfully!');
    }

    public function destroy(Fund $fund): RedirectResponse
    {
        $fund->delete();

        return redirect()->route('funds.index')->with('status', 'Fund: <strong>' . $fund->fund . '</strong> has been deleted successfully!');
    }


}
