<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Transaction;
use PDF;
use Illuminate\Support\Facades\Log;
use App\Models\FcmToken;

class FrontendController extends Controller
{
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'vat' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'tel' => 'required|string|max:255',
            'logo' => 'required|file|image|max:2048',
            'email' => 'required|string|max:255',
            'acc' => 'required|string|max:255',
            'acc_name' => 'required|string|max:255',
            'branch_code' => 'required|string|max:255',
            'branch_name' => 'required|string|max:255',
            'bank' => 'required|string|max:255',
        ]);
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = $file->getClientOriginalName();
            $destinationPath = public_path('img');
            $file->move($destinationPath, $filename);

            $validatedData['logo'] = 'img/' . $filename;
        }
        $company = Company::create([
            'name' => $validatedData['name'],
            'address' => $validatedData['address'],
            'vat' => $validatedData['vat'],
            'phone' => $validatedData['phone'],
            'logo' => $validatedData['logo'],
            'email' => $validatedData['email'],
            'acc' => $validatedData['acc'],
            'branch_code' => $validatedData['branch_code'],
            'bank' =>  $validatedData['bank'],
        ]);
        return response()->json(['message' => 'Company added successfully', 'added' => $company], 201);
    }

    public function update(Request $request, $id)
    {
        Log::info('Received Update Request:', $request->all());
        $company = Company::findOrFail($id);
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'vat' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'tel' => 'required|string|max:255',
            'email' => 'required|string|max:255',
            'acc' => 'required|string|max:255',
            'acc_name' => 'required|string|max:255',
            'branch_code' => 'required|string|max:255',
            'branch_name' => 'required|string|max:255',
            'bank' => 'required|string|max:255',
            'logo' => 'nullable|file|max:2048',
            'merchants' => 'required|array',
             'merchants.M-pesa' => 'nullable|string|max:255',
             'merchants.Ecocash' => 'nullable|string|max:255',

        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = $file->getClientOriginalName();
            $destinationPath = public_path('img');
            $file->move($destinationPath, $filename);
            $validatedData['logo'] = 'img/' . $filename;
        }
        $company->update($validatedData);
        return response()->json([
            'message' => 'Company updated successfully',
            'product' => $company
        ], 200);
    }

    public function index()
    {
        $companies = Company::all();
        return response()->json($companies);
    }

    public function downloadInvoice($id)
    {
        $transaction = Transaction::findOrFail($id);
        $company = Company::first();
        $totalAmount = $transaction->total;
        $exclusiveVat = $totalAmount / 1.15;
        $vatAmount = $totalAmount - $exclusiveVat;
        $createdAt = $transaction->created_at;
        $formattedDate = $createdAt->format('h:i A d M Y');
        $pdf = PDF::loadView('invoices.invoice', compact(
            'transaction',
            'company',
            'exclusiveVat',
            'vatAmount',
            'formattedDate'
        ));
        return $pdf->download("Invoice-{$transaction->invoice_number}.pdf");
    }

    public function fcmToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string|unique:fcm_tokens,token',
            'device_name' => 'required|string',
        ]);
        FcmToken::create([
            'user_id' => auth()->id(),
            'token' => $request->token,
        ]);

        return response()->json(['message' => 'FCM token saved successfully.']);
    }
}
