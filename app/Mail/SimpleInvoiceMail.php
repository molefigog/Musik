<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SimpleInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $invoice) {}

    public function build(): self
    {
        return $this
            ->subject('Invoice - ' . ($this->invoice['txn_id'] ?? 'Payment'))
            ->view('emails.simple-invoice');
    }
}
