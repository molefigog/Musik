@extends('layouts.report')
@section('content')
 {{-- @php
        $records = App\Models\Transaction::all();
    @endphp --}}
<table  class="main-table" style="margin-top: 20px; width: 100%; border-collapse: collapse;">
    <thead>
        <tr>
            <th style="width: 20%; text-align: left;">Id</th>
            <th style="width: 20%; text-align: left;">Invoice Number</th>
            <th style="width: 20%; text-align: left;">Total</th>
            <th style="width: 20%; text-align: left;">Change</th>
            <th style="width: 20%; text-align: right;">Cash Paid</th>
            <th style="width: 20%; text-align: right;">Customer</th>
        </tr>
    </thead>
    <tbody>
        @foreach($records as $record)
        <tr>
            <td style="text-align: left;">{{ $record->id }}</td>
            <td style="text-align: left;">{{ $record->invoice_number }}</td>
            <td style="text-align: left;">{{ $record->total }}</td>
            <td style="text-align: left;">{{ $record->change }}</td>
            <td style="text-align: right;">{{ $record->cash_paid }}</td>
            <td style="text-align: right;">{{ $record->customer }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
