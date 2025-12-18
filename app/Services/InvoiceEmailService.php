<?php

namespace App\Services;

use App\Mail\TransaksiInvoiceMail;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceEmailService
{
    /**
     * Kirim email invoice penjualan
     *
     * @param \App\Penjualan $penjualan
     * @param string|null $toEmail - Email tujuan, jika null akan pakai email user creator
     * @return bool
     */
    public static function sendPenjualanInvoice($penjualan, $toEmail = null)
    {
        try {
            // Load relasi yang dibutuhkan
            $penjualan->load(['items.produk', 'user', 'gudang', 'approver']);
            
            // Generate PDF
            $pdf = Pdf::loadView('pdf.invoice-penjualan', ['penjualan' => $penjualan]);
            $pdfContent = $pdf->output();
            
            // Tentukan email tujuan
            $email = $toEmail ?? $penjualan->user->email ?? null;
            
            if (!$email) {
                \Log::warning("Invoice penjualan #{$penjualan->id}: Email tidak tersedia");
                return false;
            }
            
            // Kirim email
            Mail::to($email)->send(new TransaksiInvoiceMail($penjualan, 'penjualan', $pdfContent));
            
            \Log::info("Invoice penjualan #{$penjualan->id} berhasil dikirim ke {$email}");
            return true;
            
        } catch (\Exception $e) {
            \Log::error("Gagal mengirim invoice penjualan #{$penjualan->id}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Kirim email invoice pembelian
     *
     * @param \App\Pembelian $pembelian
     * @param string|null $toEmail - Email tujuan, jika null akan pakai email user creator
     * @return bool
     */
    public static function sendPembelianInvoice($pembelian, $toEmail = null)
    {
        try {
            // Load relasi yang dibutuhkan
            $pembelian->load(['items.produk', 'user', 'gudang', 'approver']);
            
            // Generate PDF
            $pdf = Pdf::loadView('pdf.invoice-pembelian', ['pembelian' => $pembelian]);
            $pdfContent = $pdf->output();
            
            // Tentukan email tujuan
            $email = $toEmail ?? $pembelian->user->email ?? null;
            
            if (!$email) {
                \Log::warning("Invoice pembelian #{$pembelian->id}: Email tidak tersedia");
                return false;
            }
            
            // Kirim email
            Mail::to($email)->send(new TransaksiInvoiceMail($pembelian, 'pembelian', $pdfContent));
            
            \Log::info("Invoice pembelian #{$pembelian->id} berhasil dikirim ke {$email}");
            return true;
            
        } catch (\Exception $e) {
            \Log::error("Gagal mengirim invoice pembelian #{$pembelian->id}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Kirim email invoice biaya
     *
     * @param \App\Biaya $biaya
     * @param string|null $toEmail - Email tujuan, jika null akan pakai email user creator
     * @return bool
     */
    public static function sendBiayaInvoice($biaya, $toEmail = null)
    {
        try {
            // Load relasi yang dibutuhkan
            $biaya->load(['items', 'user', 'approver']);
            
            // Generate PDF
            $pdf = Pdf::loadView('pdf.invoice-biaya', ['biaya' => $biaya]);
            $pdfContent = $pdf->output();
            
            // Tentukan email tujuan
            $email = $toEmail ?? $biaya->user->email ?? null;
            
            if (!$email) {
                \Log::warning("Invoice biaya #{$biaya->id}: Email tidak tersedia");
                return false;
            }
            
            // Kirim email
            Mail::to($email)->send(new TransaksiInvoiceMail($biaya, 'biaya', $pdfContent));
            
            \Log::info("Invoice biaya #{$biaya->id} berhasil dikirim ke {$email}");
            return true;
            
        } catch (\Exception $e) {
            \Log::error("Gagal mengirim invoice biaya #{$biaya->id}: " . $e->getMessage());
            return false;
        }
    }
}
