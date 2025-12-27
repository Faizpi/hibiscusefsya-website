<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TransaksiNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $transaksi;
    public $type; // penjualan, pembelian, biaya
    public $notificationType; // created, needs_approval, approved
    public $pdfContent;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($transaksi, $type, $notificationType, $pdfContent = null)
    {
        $this->transaksi = $transaksi;
        $this->type = $type;
        $this->notificationType = $notificationType;
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
            'biaya' => 'Biaya',
            'kunjungan' => 'Kunjungan'
        ];

        $notificationLabels = [
            'created' => 'Transaksi Baru Dibuat',
            'needs_approval' => 'Menunggu Persetujuan',
            'approved' => 'Telah Disetujui'
        ];

        $label = $typeLabels[$this->type] ?? 'Transaksi';
        $notifLabel = $notificationLabels[$this->notificationType] ?? 'Notifikasi';
        $nomor = $this->transaksi->nomor ?? $this->transaksi->custom_number ?? $this->transaksi->id;

        // Use type-specific email template (styled like public invoice)
        $viewName = "emails.invoice-{$this->type}";

        // Fallback to generic template if specific one doesn't exist
        if (!view()->exists($viewName)) {
            $viewName = 'emails.transaksi-notification';
        }

        $mail = $this->subject("[{$notifLabel}] {$label} #{$nomor} - Hibiscus Efsya")
            ->view($viewName);

        // Attach PDF jika ada
        if ($this->pdfContent) {
            $mail->attachData($this->pdfContent, "invoice-{$this->type}-{$nomor}.pdf", [
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }
}
