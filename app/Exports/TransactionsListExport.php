<?php

namespace App\Exports;
use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
class TransactionsListExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{

	protected $query;

    public function __construct($query)
    {
        $this->query = $query->select(Transaction::exportListFields());
    }

    public function query()
    {
        return $this->query;
    }

	public function headings(): array
    {
        return [
			'Id',
			'Invoice Number',
			'Total',
			'Change',
			'Cash Paid',
			'Customer'
        ];
    }

    public function map($record): array
    {
        return [
			$record->id,
			$record->invoice_number,
			$record->total,
			$record->change,
			$record->cash_paid,
			$record->customer
        ];
    }
}
