<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\VclController;
use Illuminate\Support\Facades\Log;
use App\Models\BeatsTransactionLogs;
use SendGrid\Mail\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TransactionsListExport;
use App\Exports\TransactionsViewExport;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\TransactionsPdfMail;

class TransactionController extends Controller
{
    private $options;

    public function __construct()
    {
        $this->options = [
            'api_key' => config('laravel-pesa.api_key'),
            'public_key' => config('laravel-pesa.public_key'),
            'service_provider_code' => config('laravel-pesa.short_code'),
            'country' => 'LES',
            'currency' => 'LSL',
            'persistent_session' => false,
            'env' => config('laravel-pesa.env')
        ];
    }

    public function charge(Request $request)
    {
        Log::info("Charge route accessed");

        $request->validate([
            'input_Amount' => 'required|numeric|min:1',
            'input_CustomerMSISDN' => 'required',
            'input_PurchasedItemsDesc' => 'required|string',
            'beat_id' => 'required|exists:beats,id'
        ]);
        $number = $request->input('input_CustomerMSISDN');
        $mssid = '266' . ltrim($number, '0');
        $beatId = $request->input('beat_id');
        $existing = BeatsTransactionLogs::where([
            'user_id' => auth()->id(),
            'beat_id' => $beatId,
            'status' => 'pending'
        ])->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Payment already in progress'
            ], 409);
        }
        $conversationId = 'NID' . substr(Str::uuid(), 0, 20);

        $vclController = new VclController($this->options);

        $response = $vclController->c2b([
            'input_Amount' => $request->input('input_Amount'),
            'input_Country' => 'LES',
            'input_Currency' => 'LSL',
            'input_CustomerMSISDN' => $mssid,
            'input_ServiceProviderCode' => config('laravel-pesa.short_code'),
            'input_ThirdPartyConversationID' => $conversationId,
            'input_TransactionReference' => 'nidptyltd',
            'input_PurchasedItemsDesc' => $request->input('input_PurchasedItemsDesc')
        ]);

        Log::info("C2B response", $response);

        $statusCode = $response['statusCode'] ?? null;
        $responseCode = $response['output_ResponseCode'] ?? null;
        if ($statusCode == 201 && $responseCode === 'INS-0') {

            $transaction = BeatsTransactionLogs::create([
                'user_id' => auth()->id(),
                'beat_id' => $beatId,
                'status' => 'pending',
                'payment_method' => 'mpesa',
                'third_party_conversation_id' => $conversationId,
                'msisdn' => $mssid
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment request sent to phone',
                'transaction_ref' => $conversationId
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => $response['output_ResponseDesc'] ?? 'Payment failed',
            'code' => $responseCode
        ], 400);
    }

    public function status(Request $request)
    {
        $request->validate([
            'transaction_ref' => 'required'
        ]);

        $transaction = BeatsTransactionLogs::where(
            'third_party_conversation_id',
            $request->transaction_ref
        )->first();

        if (!$transaction) {
            return response()->json([
                'message' => 'Transaction not found'
            ], 404);
        }

        $vclController = new VclController($this->options);

        $response = $vclController->queryTransactionStatus([
            'input_QueryReference' => $transaction->msisdn,
            'input_Country' => 'LES',
            'input_ServiceProviderCode' => config('laravel-pesa.short_code'),
            'input_ThirdPartyConversationID' => $transaction->third_party_conversation_id
        ]);

        Log::info("QTS response", $response);

        $code = $response['output_ResponseCode'] ?? null;

        // ✅ SUCCESS
        if ($code === 'INS-0') {
            $transaction->update(['status' => 'success']);

            return response()->json([
                'status' => 'success'
            ]);
        }

        // ❌ FAILED
        if (in_array($code, ['INS-6', 'INS-2006', 'INS-2051'])) {
            $transaction->update(['status' => 'failed']);

            return response()->json([
                'status' => 'failed'
            ]);
        }

        // ⏳ STILL PENDING
        return response()->json([
            'status' => 'pending'
        ]);
    }

    public function b2c(Request $request)
    {
        Log::info("B2C route accessed.");
        Log::info("Options for VclController: ", $this->options);

        $number = $request->input('input_CustomerMSISDN');
        $mssid = '266' . $number;

        $vclController = new VclController($this->options);
        $response = $vclController->b2c([
            'input_Amount' => $request->input('input_Amount'),
            'input_Country' => 'LES',
            'input_Currency' => 'LSL',
            'input_CustomerMSISDN' => $mssid,
            'input_ServiceProviderCode' => config('laravel-pesa.short_code'),
            'input_ThirdPartyConversationID' => 'NID' . rand(),
            'input_TransactionReference' => 'nidptyltd',
            'input_PaymentItemsDesc' => $request->input('input_PaymentItemsDesc')
        ]);

        Log::info("Charge response: " . print_r($response, true));
        return response()->json($response);
    }

    public function b2b(Request $request)
    {
        Log::info("B2B route accessed.");
        Log::info("Options for VclController: ", $this->options);

        $number = $request->input('input_ReceiverPartyCode');
        $mssid =  $number;

        $vclController = new VclController($this->options);
        $response = $vclController->b2b([
            'input_Amount' => $request->input('input_Amount'),
            'input_Country' => 'LES',
            'input_Currency' => 'LSL',
            'input_PrimaryPartyCode' => config('laravel-pesa.short_code'),
            'input_ReceiverPartyCode' => $mssid,
            'input_ThirdPartyConversationID' => 'NID' . rand(),
            'input_TransactionReference' => 'nidptyltd',
            'input_PurchasedItemsDesc' => $request->input('input_PurchasedItemsDesc')
        ]);

        Log::info("Charge response: " . print_r($response, true));
        return response()->json($response);
    }

    public function reverse(Request $request)
    {
        Log::info("Reverse route accessed.");
        Log::info("Options for VclController: ", $this->options);

        $vclController = new VclController($this->options);

        $response = $vclController->reverse([
            'input_ReversalAmount' => $request->input('input_Amount'),
            'input_Country' => 'LES',
            'input_ServiceProviderCode' => config('laravel-pesa.short_code'),
            'input_ThirdPartyConversationID' => 'NID' . rand(),
            'input_TransactionReference' => 'nidptyltd',
            'input_TransactionID' => $request->input('input_TransactionID')
        ]);

        Log::info("Reverse response: " . print_r($response, true));
        return response()->json($response);
    }



    public function export(Request $request)
    {
        $query = Transaction::query();

        return $this->ExportList($query, $request);
    }

    private function ExportList($query, $request)
    {
        ob_end_clean();
        $filename = "ListTransactionsReport-" . now()->format('Y-m-d_H-i-s');
        $format = $request->query('format');
        if ($format == "print") {
            $records = $query->get(Transaction::exportListFields());
            return view("reports.transactions-list", ["records" => $records]);
        } elseif ($format == "pdf") {
            $records = $query->get(Transaction::exportListFields());
            $pdf = PDF::loadView("reports.transactions-list", ["records" => $records]);
            return $pdf->download("$filename.pdf");
        } elseif ($format == "csv") {
            return Excel::download(new TransactionsListExport($query), "$filename.csv");
        } elseif ($format == "excel") {
            return Excel::download(new TransactionsListExport($query), "$filename.xlsx");
        } else {
            return response()->json(['error' => 'Unsupported export format'], 400);
        }
    }
    // public function exportAndSendEmail()
    // {
    //     $query = Transaction::query(); // now you have a query to work with

    //     $records = $query->get(Transaction::exportListFields());

    //     $pdf = PDF::loadView("reports.transactions-list", ["records" => $records]);

    //     Mail::to('elliotgog@gmail.com')->send(new \App\Mail\TransactionsPdfMail($pdf->output()));

    //     return 'PDF emailed successfully!';
    // }
    public function exportAndSendEmail()
    {
        // 1. Fetch transaction records with selected fields
        $records = Transaction::query()->get(Transaction::exportListFields());

        // 2. Generate PDF from Blade view
        $pdf = PDF::loadView('reports.transactions-list', ['records' => $records]);

        // 3. Base64 encode the PDF content (SendGrid requires base64 encoded attachments)
        $pdfContent = base64_encode($pdf->output());

        // 4. Create SendGrid email object
        $email = new Mail();
        $email->setFrom('you@example.com', 'Your Name'); // use your verified sender email
        $email->setSubject('Transactions PDF Report');
        $email->addTo('elliotgog@gmail.com', 'Elliot');
        $email->addContent('text/plain', 'Please find the attached PDF report of transactions.');
        $email->addContent('text/html', '<p>Please find the attached PDF report of transactions.</p>');

        // 5. Attach the PDF
        $email->addAttachment(
            $pdfContent,
            'application/pdf',
            'transactions.pdf',
            'attachment'
        );

        // 6. Send the email via SendGrid
        $sendgrid = new \SendGrid(env('SENDGRID_API_KEY'));

        try {
            $response = $sendgrid->send($email);

            Log::info('SendGrid Response:', [
                'status_code' => $response->statusCode(),
                'headers' => $response->headers(),
                'body' => $response->body()
            ]);

            return 'PDF emailed successfully!';
        } catch (\Exception $e) {
            Log::error('SendGrid Error:', ['error' => $e->getMessage()]);
            return 'Caught exception: ' . $e->getMessage();
        }
    }
}
