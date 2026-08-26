@extends('layouts.report')
@section('content')
<div id="report-title"><h1>Transaction Details</h1></div>
<table class="table table-sm table-striped">
    <tbody>
        <tr>
            <th>Id</th>
            <td>{{ $record->id }}</td>
        </tr>
        <tr>
            <th>Items</th>
            <td>{{ $record->items }}</td>
        </tr>
        <tr>
            <th>Invoice Number</th>
            <td>{{ $record->invoice_number }}</td>
        </tr>
        <tr>
            <th>Total</th>
            <td>{{ $record->total }}</td>
        </tr>
        <tr>
            <th>Change</th>
            <td>{{ $record->change }}</td>
        </tr>
        <tr>
            <th>Payment Methods</th>
            <td>{{ $record->payment_methods }}</td>
        </tr>
        <tr>
            <th>Created At</th>
            <td>{{ $record->created_at }}</td>
        </tr>
        <tr>
            <th>Updated At</th>
            <td>{{ $record->updated_at }}</td>
        </tr>
        <tr>
            <th>Cash Paid</th>
            <td>{{ $record->cash_paid }}</td>
        </tr>
        <tr>
            <th>Customer</th>
            <td>{{ $record->customer }}</td>
        </tr>
    </tbody>
</table>
@endsection