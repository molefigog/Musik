<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TransactionsPdfMail extends Mailable
{
    use Queueable, SerializesModels;
    public $pdfContent;

    public function __construct($pdfContent)
    {
        $this->pdfContent = $pdfContent;
    }

    public function build()
    {
        return $this->subject('Your Transactions Report')
            ->view('emails.transaction-summary') // optional email body
            ->attachData($this->pdfContent, 'transactions.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
