<table style="font-family: Tahoma, sans-serif; font-size: 11px;">
    <tr><td colspan="9" style="text-align:center; font-weight:bold; font-size:14px; color:#008000;">{{ strtoupper($officeName) }}</td></tr>
    <tr><td colspan="9" style="text-align:center; font-weight:bold; color:#0000FF;">{{ $allotmentClassName }}</td></tr>
    <tr><td colspan="9"></td></tr>

    @foreach($sections as $section)
        <tr><td colspan="9" style="text-align:center; font-weight:bold; color:#FF0000;">{{ $section['account_label'] }}</td></tr>
        <tr style="font-weight:bold; background-color:#D9D9D9;">
            <td style="border:1px solid #000; text-align:center; font-weight:bold;">NO.</td>
            <td style="border:1px solid #000; text-align:center; font-weight:bold;">NAME</td>
            <td style="border:1px solid #000; text-align:center; font-weight:bold;">POSITION TITLE</td>
            <td style="border:1px solid #000; text-align:center; font-weight:bold;">SALARY GRADE</td>
            <td style="border:1px solid #000; text-align:center; font-weight:bold;">PERIOD</td>
            <td style="border:1px solid #000; text-align:center; font-weight:bold;">BASIS</td>
            <td style="border:1px solid #000; text-align:center; font-weight:bold;">REMARKS</td>
            <td style="border:1px solid #000; text-align:center; font-weight:bold;">MONTHLY RATE</td>
            <td style="border:1px solid #000; text-align:center; font-weight:bold;">TOTAL</td>
        </tr>

        @if($section['cos_list']->count() > 0)
            @foreach($section['cos_list'] as $j => $cos)
            <tr>
                <td style="border:1px solid #000; text-align:center;">{{ $j + 1 }}</td>
                <td style="border:1px solid #000;">{{ $cos->employee_name }}</td>
                <td style="border:1px solid #000;">{{ $cos->position_title }}</td>
                <td style="border:1px solid #000; text-align:center;">{{ $cos->salary_grade }}</td>
                <td style="border:1px solid #000; text-align:center;">
                    @if($cos->from_date && $cos->to_date)
                        {{ \Carbon\Carbon::parse($cos->from_date)->format('F j') }} to {{ \Carbon\Carbon::parse($cos->to_date)->format('F j, Y') }}
                    @endif
                </td>
                <td style="border:1px solid #000;">{{ $cos->basis }}</td>
                <td style="border:1px solid #000;">{{ $cos->remarks }}</td>
                <td style="border:1px solid #000; text-align:right;">{{ $cos->monthly_rate }}</td>
                <td style="border:1px solid #000; text-align:right;">{{ $cos->annual_rate }}</td>
            </tr>
            @endforeach
        @else
            <tr><td colspan="9" style="border:1px solid #000; text-align:center; font-style:italic;">No Contract of Service employees under this account.</td></tr>
        @endif

        <tr>
            <td colspan="8" style="text-align:right; font-weight:bold;">Total</td>
            <td style="text-align:right; font-weight:bold; border-bottom:1px solid #000;">{{ $section['total_annual_rate'] }}</td>
        </tr>
        <tr><td colspan="9"></td></tr>
        <tr>
            <td colspan="8" style="text-align:right;">Appropriation</td>
            <td style="text-align:right; border-bottom:1px solid #000;">{{ $section['total_appropriation'] }}</td>
        </tr>
        <tr>
            <td colspan="8" style="text-align:right;">Less: Payment of Services</td>
            <td style="text-align:right; border-bottom:1px solid #000;">{{ $section['total_annual_rate'] }}</td>
        </tr>
        <tr>
            <td colspan="8" style="text-align:right; font-weight:bold;">Balance</td>
            <td style="text-align:right; font-weight:bold; border-bottom:1px solid #000;">{{ $section['total_appropriation'] - $section['total_annual_rate'] }}</td>
        </tr>
        <tr><td colspan="9"></td></tr>
    @endforeach
</table>