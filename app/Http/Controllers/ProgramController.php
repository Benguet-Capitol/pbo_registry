<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProgramController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $perPage = $request->input('per_page', 10); // Default to 10 rows per page
        $search = $request->input('search');

        // Get sorting parameters from query string, default to 'id' and 'desc'
        $sortBy = $request->query('sort_by', 'id');
        $sortOrder = $request->query('sort_order', 'asc');

        // Query programs
        $query = Program::query();

        if ($search) {
            $query->where('id', 'like', "%{$search}%")
                ->orWhere('program', 'like', "%{$search}%");
        }

        // Apply sorting before pagination
        if ($perPage == 'all') {
            $programs = $query->orderBy($sortBy, $sortOrder)->get();
        } else {
            $programs = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);
        }

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'Programs']
        ];

        return view('programs.index', compact('programs', 'perPage', 'search', 'sortBy', 'sortOrder', 'breadcrumb'))
            ->with('status', session('status'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'program' => 'required|string|max:255',
        ]);

        $program = Program::create($request->all());

        return redirect()->route('programs.index')
            ->with('status', 'Program / Project / Activity: <strong>' . $program->program . '</strong> created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Program $program)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Program $program)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Program $program): RedirectResponse
    {
        $validated = $request->validate([
            'edit_program' => 'required|string|max:255',
        ]);

        $program->update([
            'program' => $validated['edit_program'],
        ]);

        return redirect()->route('programs.index')
            ->with('status', 'Program / Project / Activity: <strong>' . $program->program . '</strong> has been updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Program $program)
    {
        $program->delete();

        return redirect()->route('programs.index')
            ->with('status', 'Program / Project / Activity: <strong>' . $program->program . '</strong> has been deleted successfully.');
    }
}
