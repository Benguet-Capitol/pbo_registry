<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FundSource;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FundSourceController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 'all'); // Default to showing all rows
        $search = $request->input('search');

        // Get sorting parameters from query string, default to 'id' and 'desc'
        $sortBy = $request->query('sort_by', 'id');
        $sortOrder = $request->query('sort_order', 'asc');

        // Defer the heavy fund sources query on first load; the page's own AJAX fetch re-requests it with a loading state.
        $fundSourcesLoading = ! $request->ajax();

        if (! $fundSourcesLoading) {

        // Query Fund Sources
        $query = FundSource::query();

        if ($search) {
            $query->where('category', 'like', "%{$search}%")
                ->orWhere('source', 'like', "%{$search}%");
        }

        // Apply sorting before pagination
        if ($perPage == 'all') {
            $fund_sources = $query->orderBy($sortBy, $sortOrder)->get();
        } else {
            $fund_sources = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);
        }

        } else {
            $fund_sources = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(),
                0,
                is_numeric($perPage) ? (int) $perPage : 10,
                1,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'Fund Sources']
        ];

        return view('fund_sources.index', compact('fund_sources', 'perPage', 'search', 'sortBy', 'sortOrder', 'breadcrumb', 'fundSourcesLoading'))->with('status', session('status'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category' => 'required|string|max:255',
            'source' => 'required|string|max:255' 
        ]);

        $fund_source = FundSource::create($validated);

        return redirect()->back()->with('status', 'Fund Source: <strong>' . $fund_source->source . '</strong> with Category: <strong>' . $fund_source->category . '</strong> has been created successfully!');
    }

    public function update(Request $request, FundSource $fund_source): RedirectResponse
    {
        $validated = $request->validate([
            'edit_category' => 'required|string|max:255',
            'edit_source' => 'required|string|max:255'
        ]);

        $fund_source->update([
            'category' => $validated['edit_category'],
            'source' => $validated['edit_source']
        ]);

        return redirect()->back()->with('status', 'Fund Source: <strong>' . $fund_source->source . '</strong> with Category: <strong>' . $fund_source->category . '</strong> has been updated successfully!');
    }
    
    public function destroy(FundSource $fund_source): RedirectResponse
    {
        $fund_source->delete();

        return redirect()->back()->with('status', 'Fund Source: <strong>' . $fund_source->source . '</strong> with Category: <strong>' . $fund_source->category . '</strong> has been deleted successfully!');
    }
}
