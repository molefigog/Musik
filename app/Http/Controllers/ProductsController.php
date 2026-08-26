<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductAdjustment;
use App\Models\Category;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use App\Models\Qoute;
use App\Models\Transaction;
use App\Models\SoldItemsCollection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class ProductsController extends Controller
{
    // protected function authorizeAdmin()
    // {
    //     if (auth()->user()->role !== 3) {
    //         abort(403, 'Unauthorized.');
    //     }
    // }
    public function categories(Request $request)
    {
        $categories = Category::withCount(['products as product_count'])->get();
        return response()->json(compact('categories'));
    }
    public function items(Request $request)
    {
        $search = $request->query('search');

        $query = Product::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "$search%")
                    ->orWhere('barcode', 'like', "$search%");
            });
        } else {

            $query->limit(20);
        }

        $products = $query->orderBy('name', 'asc')->get();

        return response()->json([
            'products' => $products,
        ]);
    }

    public function index(Request $request)
    {
        $search = $request->query('search');
        $categoryId = $request->query('category_id');
        $stockOrder = $request->query('stock_order');

        $minCost = $request->query('min_cost');
        $maxCost = $request->query('max_cost');
        $minPrice = $request->query('min_price');
        $maxPrice = $request->query('max_price');
        $minStock = $request->query('min_stock');
        $maxStock = $request->query('max_stock');

        $query = Product::join('categories as c', 'c.id', '=', 'products.category_id')
            ->select('products.*', 'c.name as category');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('products.name', 'like', "%$search%")
                    ->orWhere('products.barcode', 'like', "%$search%")
                    ->orWhere('c.name', 'like', "%$search%");
            });
        }

        if ($categoryId) {
            $query->where('c.id', $categoryId);
        }

        if ($minCost !== null) {
            $query->where('products.cost', '>=', $minCost);
        }

        if ($maxCost !== null) {
            $query->where('products.cost', '<=', $maxCost);
        }

        if ($minPrice !== null) {
            $query->where('products.price', '>=', $minPrice);
        }

        if ($maxPrice !== null) {
            $query->where('products.price', '<=', $maxPrice);
        }

        if ($minStock !== null) {
            $query->where('products.stock', '>=', $minStock);
        }

        if ($maxStock !== null) {
            $query->where('products.stock', '<=', $maxStock);
        }

        if ($stockOrder && in_array(strtolower($stockOrder), ['asc', 'desc'])) {
            $query->orderBy('products.stock', $stockOrder);
        } else {
            $query->orderBy('products.name', 'asc');
        }

        $products = $query->get();
        $categories = Category::withCount(['products as product_count'])->get();

        return response()->json(compact('products', 'categories'));
    }

    public function updateStock(Request $request)
    {
  
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'adjustmentType' => 'required|in:increment,decrement',
            'adjustmentValue' => 'required|integer|min:1',
            'reason' => 'required|in:new stock,damage,stolen,returned',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        if ($validated['adjustmentType'] === 'increment') {
            $product->stock += $validated['adjustmentValue'];
        } else {
            $product->stock -= $validated['adjustmentValue'];
        }
        $product->save();

        ProductAdjustment::create([
            'product_id' => $product->id,
            'type' => $validated['adjustmentType'],
            'quantity' => $validated['adjustmentValue'],
            'reason' => $validated['reason'],
            'adjusted_at' => now(),
        ]);

        return response()->json(['message' => 'Stock updated successfully', 'product' => $product]);
    }

    public function getStockHistory(Request $request)
    {
        $productId = $request->input('product_id');
        $search = $request->input('search');
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $query = ProductAdjustment::where('product_id', $productId);

        if ($search) {
            $query->where('reason', 'like', '%' . $search . '%');
        }

        $stockHistory = $query->paginate($perPage, ['*'], 'page', $page);
        return response()->json([
            'history' => $stockHistory->items(),
            'pagination' => [
                'page' => $stockHistory->currentPage(),
                'rowsPerPage' => $stockHistory->perPage(),
                'total' => $stockHistory->total(),
            ]
        ]);
    }
    public function restock(Request $request, Product $product)
    {
        // Validate the quantity to restock
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        // Increment product stock
        $product->increment('stock', $validated['quantity']);

        return response()->json([
            'message' => 'Product restocked successfully.',
            'product' => $product,
        ]);
    }
    public function store(Request $request)
    {
        // $this->authorizeAdmin();
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'cost' => 'required|numeric',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'barcode' => 'required|string|unique:products,barcode',
            'alerts' => 'required|integer',
            'category_id' => 'required|exists:categories,id',
        ]);

        $product = Product::create([
            'name' => $validatedData['name'],
            'barcode' => $validatedData['barcode'],
            'price' => $validatedData['price'],
            'stock' => $validatedData['stock'],
            'alerts' => $validatedData['alerts'],
            'cost' => $validatedData['cost'],
            'category_id' => $validatedData['category_id'],
        ]);

        return response()->json(['message' => 'Product added successfully', 'product' => $product], 201);
    }

    public function update(Request $request, $id)
    {
        // $this->authorizeAdmin();
        Log::info('Received Update Request:', $request->all());
        $product = Product::findOrFail($id);
        $product->update($request->all());

        return response()->json(['message' => 'Product updated successfully', 'product' => $product], 200);
    }

    public function delete($id)
    {
        // $this->authorizeAdmin();
        try {

            $product = Product::findOrFail($id);
            $product->delete();
            return response()->json(['message' => 'Product deleted successfully']);
        } catch (\Exception $e) {
            Log::error('Error deleting product: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting product', 'error' => $e->getMessage()], 500);
        }
    }
    public function invoice(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'total' => 'required|numeric',
            'change' => 'required|numeric',
            'customer' => 'required|string',
            'cash_paid' => 'required|numeric',
            'payment_methods' => 'required|array',
        ]);

        $items = $validated['items'];

        foreach ($items as $item) {
            $product = Product::find($item['id'] ?? null);
            if ($product && isset($item['quantity'])) {
                $newQuantity = max($product->stock - $item['quantity'], 0);
                $product->stock = $newQuantity;
                $product->save();
            }
        }

        $invoiceNumber = $this->generateInvoiceNumber();

        $transaction = Transaction::create([
            'items' => $items,
            'invoice_number' => $invoiceNumber,
            'total' => $validated['total'],
            'change' => $validated['change'],
            'customer' => $validated['customer'],
            'cash_paid' => $validated['cash_paid'],
            'payment_methods' => $validated['payment_methods'],
        ]);

        return response()->json([
            'message' => 'Transaction stored successfully',
            'data' => $transaction
        ]);
    }

    public function SoldItemsColletion(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'total' => 'required|numeric',
            // 'invoice_number' => 'required|string|regex:/^\d+_\d+$/',
            'invoice_number' => 'required|numeric',
            'original_receipt_date' => 'required|string',
            'customer' => 'required|string',
            'change' => 'required|numeric',
            'cash_paid' => 'required|numeric',
            'payment_methods' => 'required|array',
        ]);

        $items = $validated['items'];

        foreach ($items as $item) {
            $product = Product::find($item['id'] ?? null);
            if ($product && isset($item['quantity'])) {
                $newQuantity = max($product->stock - $item['quantity'], 0);
                $product->stock = $newQuantity;
                $product->save();
            }
        }



        $transaction = SoldItemsCollection::create([
            'items' => $items,
            'invoice_number' =>  $validated['invoice_number'],
            'total' => $validated['total'],
            'change' => $validated['change'],
            'customer' => $validated['customer'],
            'original_receipt_date' => $validated['original_receipt_date'],
            'cash_paid' => $validated['cash_paid'],
            'payment_methods' => $validated['payment_methods'],
        ]);

        return response()->json([
            'message' => 'Transaction stored successfully',
            'data' => $transaction
        ]);
    }
    private function generateInvoiceNumber(): string
    {
        $date = now()->format('dmy');
        $sequence = DB::table('receipt_sequences')->lockForUpdate()->first();

        if (!$sequence) {
            DB::table('receipt_sequences')->insert(['last_number' => 1]);
            $next = 1;
        } else {
            $next = $sequence->last_number + 1;
            DB::table('receipt_sequences')->update(['last_number' => $next]);
        }

        $sequencePadded = str_pad($next, 3, '0', STR_PAD_LEFT);

        return "INV-{$date}-{$sequencePadded}";
    }
    public function latest()
    {
        $transaction = Transaction::latest()->first();
        $company = Company::first();

        if (!$transaction || !$company) {
            return response()->json(['error' => 'Missing transaction or company data'], 404);
        }

        $total = floatval($transaction->total);
        $vatExclusive = $total / 1.15;
        $vatAmount = $total - $vatExclusive;

        return response()->json([
            ...$transaction->toArray(),
            'company' => [
                'name' => $company->name,
                'address' => $company->address,
                'vat' => $company->vat,
                'contact' => $company->phone,
                'tel' => $company->tel,
                'email' => $company->email,
                'logo' => $company->logo,
                'vat' => [
                    'exclusive' => number_format($vatExclusive, 2),
                    'amount' => number_format($vatAmount, 2),
                ],
            ],
        ]);
    }

    public function latestQ()
    {
        $transaction = Qoute::latest('updated_at')->first();

        if (!$transaction) {
            return response()->json(['message' => 'No quote found'], 404);
        }
        $transaction->time = $transaction->updated_at->format('H:i'); // or 'h:i A' for AM/PM
        return response()->json($transaction);
    }

    public function latestCollection()
    {
        $transaction = SoldItemsCollection::latest()->first();
        $company = Company::first();

        if (!$transaction || !$company) {
            return response()->json(['error' => 'Missing transaction or company data'], 404);
        }

        $total = floatval($transaction->total);
        $vatExclusive = $total / 1.15;
        $vatAmount = $total - $vatExclusive;

        return response()->json([
            ...$transaction->toArray(),
            'company' => [
                'name' => $company->name,
                'address' => $company->address,
                'vat_number' => $company->vat,
                'contact' => $company->phone,
                'email' => $company->email,
                'acc' => $company->acc,
                'bank' => $company->bank,
                'branch_code' => $company->branch_code,
                'logo' => asset('img/' . $company->logo),
                'vat' => [
                    'exclusive' => number_format($vatExclusive, 2),
                    'amount' => number_format($vatAmount, 2),
                ],
            ],
        ]);
    }



    public function deductStock(Request $request)
    {
        Log::info('Received Update Request:', $request->all());

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'adjustmentType' => 'required|in:decrement',
            'reason' => 'required|in:damage,stolen,taken',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['items'] as $item) {
                $product = Product::lockForUpdate()->find($item['id']);
                $qty = $item['quantity'];

                if ($product->stock < $qty) {
                    Log::warning("Insufficient stock for {$product->name}", [
                        'requested' => $qty,
                        'available' => $product->stock
                    ]);
                    throw new \Exception(
                        "Insufficient stock for {$product->name}. Available: {$product->stock}"
                    );
                }

                // Decrement stock
                $product->decrement('stock', $qty);

                // Log adjustment
                ProductAdjustment::create([
                    'product_id' => $product->id,
                    'type' => 'decrement',
                    'quantity' => $qty,
                    'reason' => $validated['reason'],
                    'adjusted_at' => now(),
                ]);

                Log::info("Stock decremented for product {$product->name}", [
                    'decremented' => $qty,
                    'remaining' => $product->stock
                ]);
            }
        });

        Log::info('Stock deduction completed successfully');

        return response()->json([
            'message' => 'Stock deducted successfully',
        ]);
    }

    // public function refund(Request $request)
    // {
    //     $validated = $request->validate([
    //         'transaction_id' => 'required|exists:transactions,id',
    //         'invoice_number' => 'required|string',
    //         'amount' => 'required|numeric',
    //         'reason' => 'required|string',
    //         'items_returned' => 'required|array',
    //         'items_returned.*' => 'string',
    //     ]);

    //     try {
    //         $transaction = Transaction::findOrFail($validated['transaction_id']);
    //         $refund = $transaction->refunds()->create([
    //             'invoice_number' => $validated['invoice_number'],
    //             'amount' => $validated['amount'],
    //             'reason' => $validated['reason'],
    //             'items_returned' => $validated['items_returned'],
    //         ]);
    //         $items = collect($transaction->items);

    //         foreach ($validated['items_returned'] as $itemName) {
    //             $itemIndex = $items->search(fn($i) => $i['details'] === $itemName);
    //             if ($itemIndex !== false) {
    //                 $item = $items[$itemIndex];
    //                 $product = Product::where('name', $item['details'])->first();
    //                 if ($product) {
    //                     $quantity = $item['qty'] ?? 1;
    //                     $product->increment('stock', $quantity);
    //                     $item['qty'] = max(0, $item['qty'] - $quantity);
    //                     $items[$itemIndex] = $item;
    //                 } else {
    //                     return response()->json([
    //                         'message' => 'Product not found for item: ' . $itemName,
    //                     ], 404);
    //                 }
    //             } else {
    //                 return response()->json([
    //                     'message' => 'Item not found in the transaction: ' . $itemName,
    //                 ], 404);
    //             }
    //         }
    //         $transaction->items = $items->toArray();
    //         $transaction->save();
    //         return response()->json([
    //             'message' => 'Refund processed successfully.',
    //             'refund' => $refund,
    //         ], 201);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'An error occurred while processing the refund.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }
    public function refund(Request $request)
    {
        $validated = $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'invoice_number' => 'required|string',
            'amount' => 'required|numeric',
            'reason' => 'required|string',
            'items_returned' => 'required|array',
            'items_returned.*' => 'string',
        ]);

        try {
            // Fetch the transaction
            $transaction = Transaction::findOrFail($validated['transaction_id']);

            // Create refund record
            $refund = $transaction->refunds()->create([
                'invoice_number' => $validated['invoice_number'],
                'amount' => $validated['amount'],
                'reason' => $validated['reason'],
                'items_returned' => $validated['items_returned'],
            ]);

            // Convert items to collection
            $items = collect($transaction->items);

            foreach ($validated['items_returned'] as $itemName) {
                $item = $items->firstWhere('details', $itemName);

                if (!$item) {
                    return response()->json([
                        'message' => 'Item not found in the transaction: ' . $itemName,
                    ], 404);
                }

                $product = Product::where('name', $item['details'])->first();
                if (!$product) {
                    return response()->json([
                        'message' => 'Product not found for item: ' . $itemName,
                    ], 404);
                }

                $quantity = $item['qty'] ?? 1;
                $product->increment('stock', $quantity);
            }

            // Remove returned items from transaction
            $transaction->items = $items->reject(fn($i) => in_array($i['details'], $validated['items_returned']))->values()->all();
            $transaction->save();

            return response()->json([
                'message' => 'Refund processed successfully.',
                'refund' => $refund,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while processing the refund.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
