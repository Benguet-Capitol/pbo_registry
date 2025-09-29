<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AllotmentClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;


class AllotmentClassController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = $request->input('per_page', 10); // Default to 10 rows per page
        $search = $request->input('search');

        // Get sorting parameters from query string, default to 'id' and 'desc'
        $sortBy = $request->query('sort_by', 'id');
        $sortOrder = $request->query('sort_order', 'asc');

        // Query allotment classes
        $query = AllotmentClass::query();

        if ($search) {
            $query->where('code', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('category', 'like', "%{$search}%");
        }

        // Apply sorting before pagination
        if ($perPage == 'all') {
            $allotment_classes = $query->orderBy($sortBy, $sortOrder)->get();
        } else {
            $allotment_classes = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);
            
        }

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'Allotment Classes']
        ];

        return view('allotment_classes.index', compact('allotment_classes', 'perPage', 'search', 'sortBy', 'sortOrder', 'breadcrumb'))
            ->with('status', session('status'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class' => 'required|string|max:255|unique:allotment_classes,class',
            'description' => 'required|string|max:255',
            'category' => 'required|string|max:255',
        ]);

        AllotmentClass::create($validated);

        return redirect()->route('allotment_classes.index')->with('status', 'Allotment Class <strong>' . $validated['description'] . '</strong> with category <strong>' . $validated['category'] . '</strong> has been created successfully!');
    }

    public function update(Request $request, AllotmentClass $allotment_class): RedirectResponse
    {
        $validated = $request->validate([
            'edit_class' => 'required|string|max:255|unique:allotment_classes,class,' . $allotment_class->id,
            'edit_description' => 'required|string|max:255',
            'edit_category' => 'required|string|max:255',
        ]);

        $allotment_class->update([
            'class' => $validated['edit_class'],
            'description' => $validated['edit_description'],
            'category' => $validated['edit_category']
        ]);

        return redirect()->route('allotment_classes.index')->with('status', 'Allotment Class <strong>' . $validated['edit_description'] . '</strong> with category <strong>' . $validated['edit_category'] . '</strong> has been updated successfully!');
    }

    public function destroy(AllotmentClass $allotment_class): RedirectResponse
    {
        $allotment_class->delete();

        return redirect()->route('allotment_classes.index')->with('status', 'Allotment Class <strong>' . $allotment_class->description . '</strong> with category <strong>' . $allotment_class->category . '</strong> has been deleted successfully!');
    }
}
