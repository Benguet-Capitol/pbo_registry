<?php

namespace App\Http\Controllers;

use App\Models\Fund;
use App\Models\Office;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OfficeController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search');
        $sortBy = $request->query('sort_by', 'id');
        $sortOrder = $request->query('sort_order', 'asc');

        $query = Office::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('office_name', 'like', "%{$search}%")
                    ->orWhere('office_abbreviation', 'like', "%{$search}%")
                    ->orWhere('sub_office', 'like', "%{$search}%")
                    ->orWhere('fund', 'like', "%{$search}%")
                    ->orWhere('fpp_code', 'like', "%{$search}%")
                    ->orWhere('responsibility_code', 'like', "%{$search}%")
                    ->orWhere('ppa_code', 'like', "%{$search}%")
                    ->orWhere('mfo_services', 'like', "%{$search}%")
                    ->orWhere('branch', 'like', "%{$search}%");
            });
        }

        $offices = $perPage == 'all'
            ? $query->orderBy($sortBy, $sortOrder)->get()
            : $query->orderBy($sortBy, $sortOrder)->paginate($perPage);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('offices.partials.table', compact('offices'))->render()
            ]);
        }

        $funds = Fund::all()->sortBy('fund');

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'Offices']
        ];

        return view('offices.index', compact('offices', 'perPage', 'search', 'sortBy', 'sortOrder', 'funds', 'breadcrumb'))
            ->with('status', session('status'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'office_name' => 'required|string|max:255',
            'office_abbreviation' => 'required|string|max:255',
            'sub_office' => 'nullable|string|max:255',
            'fund' => 'required|string|max:255',
            'fpp_code' => 'nullable|string|max:255',
            'responsibility_code' => 'nullable|string|max:255',
            'ppa_code' => 'nullable|string|max:255',
            'branch' => 'required|string|max:255',
        ]);

        Office::create([
            'office_name' => $validated['office_name'],
            'office_abbreviation' => $validated['office_abbreviation'],
            'sub_office' => $validated['sub_office'],
            'fund' => $validated['fund'],
            'fpp_code' => $validated['fpp_code'],
            'responsibility_code' => $validated['responsibility_code'],
            'ppa_code' => $validated['ppa_code'],
            'branch' => $validated['branch'],
        ]);

        return redirect()->back()->with('status', 'Office <strong>' . $validated['office_name'] . '</strong> with abbreviation <strong>' . $validated['office_abbreviation'] . '</strong> has been created successfully!');
    }

    public function edit(Office $office): View
    {
        $funds = Fund::all()->sortBy('fund');

        $breadcrumb = [
            ['label' => 'Dashboard', 'route' => route('dashboard')],
            ['label' => 'Offices', 'route' => route('offices.index')],
            ['label' => 'Edit Offices']
        ];

        return view('offices.edit', [
            'office' => $office,
            'breadcrumb' => $breadcrumb,
        ], compact('funds'));
    }

    public function update(Request $request, Office $office): RedirectResponse
    {
        $validated = $request->validate([
            'edit_office_name' => 'required|string|max:255',
            'edit_office_abbreviation' => 'required|string|max:255',
            'edit_sub_office' => 'nullable|string|max:255',
            'edit_fund' => 'required|string|max:255',
            'edit_fpp_code' => 'nullable|string|max:255',
            'edit_responsibility_code' => 'nullable|string|max:255',
            'edit_ppa_code' => 'nullable|string|max:255',
            'edit_branch' => 'required|string|max:255',
        ]);

        $office->update([
            'office_name' => $validated['edit_office_name'],
            'office_abbreviation' => $validated['edit_office_abbreviation'],
            'sub_office' => $validated['edit_sub_office'],
            'fund' => $validated['edit_fund'],
            'fpp_code' => $validated['edit_fpp_code'],
            'responsibility_code' => $validated['edit_responsibility_code'],
            'ppa_code' => $validated['edit_ppa_code'],
            'branch' => $validated['edit_branch'],
        ]);

        return redirect()->back()->with('status', 'Office <strong>' . $validated['edit_office_name'] . '</strong> with abbreviation <strong>' . $validated['edit_office_abbreviation'] . '</strong> has been updated successfully!');
    }

    public function destroy(Office $office): RedirectResponse
    {
        $office->delete();

        return redirect()->back()->with('status', 'Office <strong>' . $office->office_name . '</strong> with abbreviation <strong>' . $office->office_abbreviation . '</strong> has been deleted successfully!');
    }
}
