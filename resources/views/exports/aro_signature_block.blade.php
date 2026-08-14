{{--
    Amount In Words / certification / Signatories / closing footer — the Excel
    equivalent of allotment_release_orders/partials/signature_block.blade.php,
    rendered as table rows instead of divs so it slots into the export's single
    continuous <table>.
--}}
<tr><td colspan="7" style="font-weight: bold; font-size: 10px;">AMOUNT IN WORDS:</td></tr>
<tr><td colspan="7" style="text-decoration: underline; font-size: 10px;">{{ strtoupper(\App\Helpers\NumberToWords::convert((float) $total)) }}</td></tr>
<tr><td colspan="7"></td></tr>
<tr>
    <td colspan="7" style="font-size: 8px;">
        The allotments herein released shall be used solely for the purposes indicated, and disbursements thereto shall be made in accordance with existing budgeting, accounting, and auditing rules and regulations. It is primary responsibility of the head of the Department/Office or Unit concerned to keep expenditures within the limits of the amount alloted.
    </td>
</tr>
<tr><td colspan="7"></td></tr>
<tr>
    <td colspan="3" style="font-weight: bold; font-size: 10px;">Recommended by:</td>
    <td></td>
    <td colspan="3" style="font-weight: bold; font-size: 10px;">Approved by:</td>
</tr>
<tr><td colspan="7"></td></tr>
<tr><td colspan="7"></td></tr>
<tr>
    {{-- text-transform:uppercase alone doesn't survive HTML-to-Excel conversion (Excel
         cells only ever store literal text), so the casing is applied in PHP here
         rather than relying on the CSS property like the Print view can. --}}
    <td colspan="3" style="text-align: center; font-weight: bold; font-size: 10px;">{{ strtoupper($aro->provincialBudgetOfficer->name ?? 'N/A') }}</td>
    <td></td>
    <td colspan="3" style="text-align: center; font-weight: bold; font-size: 10px;">{{ strtoupper($aro->provincialGovernor->name ?? $aro->provincial_governor_name ?? 'N/A') }}</td>
</tr>
<tr>
    <td colspan="3" style="text-align: center; font-size: 10px;">{{ $aro->provincial_budget_officer_title }}</td>
    <td></td>
    <td colspan="3" style="text-align: center; font-size: 10px;">{{ $aro->provincial_governor_title }}</td>
</tr>
<tr>
    <td colspan="3"></td>
    <td></td>
    <td colspan="3" style="text-align: center; border-top: 1px solid #000; font-size: 10px;">Local Chief Executive</td>
</tr>
<tr><td colspan="7"></td></tr>
<tr><td colspan="7" style="text-align: center; font-size: 8px;">ARO No: {{ $aro->aro_no }}</td></tr>
<tr><td colspan="7" style="text-align: center; font-size: 8px;">Date of Issue: {{ $aro->date_of_issue->format('F j, Y') }}</td></tr>
<tr><td colspan="7" style="text-align: center; font-size: 8px;">Page {{ $totalPages }} of {{ $totalPages }} Page{{ $totalPages > 1 ? 's' : '' }}</td></tr>
