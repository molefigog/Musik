<?php

namespace App\Exports;
use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
class TransactionsViewExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
	protected $query;

	protected $rec_id;

    public function __construct($query, $rec_id)
    {
        $this->query = $query->select(Transaction::exportViewFields());
        $this->rec_id = $rec_id;
    }


    public function query()
    {
        return $this->query->where("id", $this->rec_id);
    }


	public function headings(): array
    {
        return [
			'Id',
			'Items',
			'Invoice Number',
			'Total',
			'Change',
			'Payment Methods',
			'Created At',
			'Updated At',
			'Cash Paid',
			'Customer'
        ];
    }


    public function map($record): array
    {
        return [
			$record->id,
			$record->items,
			$record->invoice_number,
			$record->total,
			$record->change,
			$record->payment_methods,
			$record->created_at,
			$record->updated_at,
			$record->cash_paid,
			$record->customer
        ];
    }
}
