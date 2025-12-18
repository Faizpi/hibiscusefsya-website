<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TransaksiInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $transaksi;
    public $type; // penjualan, pembelian, biaya
    public $pdfContent;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($transaksi, $type, $pdfContent)
    {
        $this->transaksi = $transaksi;
        $this->type = $type;
        $this->pdfContent = $pdfContent;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $typeLabels = [
            'penjualan' => 'Penjualan',
            'pembelian' => 'Pembelian',
            'biaya' => 'Biaya'
        ];
        
        $label = $typeLabels[$this->type] ?? 'Transaksi';
        $nomor = $this->transaksi->nomor ?? $this->transaksi->custom_number ?? $this->transaksi->id;
        
        return $this->subject("Invoice {$label} #{$nomor} - Hibiscus Efsya")
            ->view('emails.transaksi-invoice')
            ->attachData($this->pdfContent, "invoice-{$this->type}-{$nomor}.pdf", [
                'mime' => 'application/pdf',
            ]);
    }
}
