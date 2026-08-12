<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\Sector;

class SectorController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = $request->input('per_page', 10); // Default to 10 rows per page
        $search = $request->input('search');

        // Get sorting parameters from query string, default to 'id' and 'desc'
        $sortBy = $request->query('sort_by', 'id');
        $sortOrder = $request->query('sort_order', 'asc');

        // Query sectors
        $query = Sector::query();

        if ($search) {
            $query->where('sector', 'like', "%{$search}%")
                ->orWhere('sector_code', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%");
        }

        // Apply sorting before pagination
        if ($perPage == 'all') {
            $sectors = $query->orderBy($sortBy, $sortOrder)->get();
        } else {
            $sectors = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);
        }

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'Sectors']
        ];

        return view('sectors.index', compact('sectors', 'perPage', 'search', 'sortBy', 'sortOrder', 'breadcrumb'))
            ->with('status', session('status'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sector' => 'required|string|max:255',
            'sector_code' => 'required|string|max:255',
            'code' => 'required|string|max:255'
        ]);

        $sector = Sector::create($validated);

        return redirect()->back()->with('status', 'Sector: <strong>' . $sector->sector . '</strong> with Sector Code: <strong>' . $sector->sector_code . '</strong> has been created successfully!');
    }

    public function update(Request $request, Sector $sector): RedirectResponse
    {
        $validated = $request->validate([
            'edit_sector' => 'required|string|max:255' . $sector->id,
            'edit_sector_code' => 'required|string|max:255',
            'edit_code' => 'required|string|max:255'
        ]);

        $sector->update([
            'sector' => $validated['edit_sector'],
            'sector_code' => $validated['edit_sector_code'],
            'code' => $validated['edit_code']
        ]);

        return redirect()->back()->with('status', 'Sector: <strong>' . $sector->sector . '</strong> with Sector Code: <strong>' . $sector->sector_code . '</strong> has been updated successfully!');
    }

    public function destroy(Sector $sector): RedirectResponse
    {
        $sector->delete();

        return redirect()->back()->with('status', 'Sector: <strong>' . $sector->sector . '</strong> with Sector Code: <strong>' . $sector->sector_code . '</strong> has been deleted successfully!');
    }
}
