<?php

namespace App\Http\Controllers;

use App\Models\Qoute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class QouteController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'items' => 'required|array',
            'total' => 'required|numeric',

            'customer' => 'required|string',
        ]);
        $qNumber = $this->generateInvoiceNumber();
        $quote = new Qoute();
        $quote->items = $data['items'];
        $quote->total = $data['total'];
        $quote->customer = $data['customer'];
        $quote->qoutation_number =  $qNumber;
        $quote->save();
        return response()->json([
            'message' => 'Quote created successfully',
            'quote' => $quote,
        ]);
    }

    public function update(Request $request, $id)
    {
        $quote = Qoute::findOrFail($id);
        $data = $request->only(['items', 'total']);

        if (isset($data['items'])) {
            $quote->items = $data['items'];
        }
        if (isset($data['total'])) {
            $quote->total = $data['total'];
        }
        $quote->save();
        return response()->json($quote, 200);
    }

    public function search(Request $request)
    {
        $search = $request->query('search');

        if (!$search) {
            return response()->json([]);
        }
        $quotes = Qoute::where('customer', 'like', "$search%")
            ->orWhere('qoutation_number', 'like', "$search%")
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
        return response()->json($quotes);
    }

    private function generateInvoiceNumber(): string
    {
        $date = now()->format('dmy');
        $sequence = DB::table('quote_sequences')->lockForUpdate()->first();
        if (!$sequence) {
            DB::table('quote_sequences')->insert(['last_number' => 1]);
            $next = 1;
        } else {
            $next = $sequence->last_number + 1;
            DB::table('quote_sequences')->update(['last_number' => $next]);
        }
        $sequencePadded = str_pad($next, 3, '0', STR_PAD_LEFT);
        return "Q-{$date}-{$sequencePadded}";
    }

    public function index(Request $request)
    {
        $query = Qoute::select('id', 'qoutation_number', 'customer', 'created_at', 'items');
        if ($request->filled('filter')) {
            $term = $request->filter;
            $query->where(function ($q) use ($term) {
                $q->where('qoutation_number', 'like', "%$term%")
                    ->orWhere('customer', 'like', "%$term%");
            });
        }
        $sortBy = $request->get('sortBy', 'id');
        $descending = $request->get('descending', 'true');
        $direction = ($descending === 'true' || $descending === true) ? 'desc' : 'asc';
        $query->orderBy($sortBy, $direction);
        $perPage = (int) $request->get('perPage', 10);
        $page = (int) $request->get('page', 1);
        return $query->paginate($perPage, ['*'], 'page', $page);
    }


    public function show(Qoute $qoute)
    {
        return $qoute;
    }
}
