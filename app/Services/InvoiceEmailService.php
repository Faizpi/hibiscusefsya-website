<?php

namespace App\Services;

use App\Mail\TransaksiInvoiceMail;
use App\Mail\TransaksiNotificationMail;
use App\User;
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

    /**
     * Kirim email invoice kunjungan
     *
     * @param \App\Kunjungan $kunjungan
     * @param string|null $toEmail - Email tujuan, jika null akan pakai email user creator
     * @return bool
     */
    public static function sendKunjunganInvoice($kunjungan, $toEmail = null)
    {
        try {
            // Load relasi yang dibutuhkan
            $kunjungan->load(['items.produk', 'user', 'gudang', 'approver', 'kontak']);

            // Generate PDF
            $pdf = Pdf::loadView('pdf.invoice-kunjungan', ['kunjungan' => $kunjungan]);
            $pdfContent = $pdf->output();

            // Tentukan email tujuan
            $email = $toEmail ?? $kunjungan->user->email ?? null;

            if (!$email) {
                \Log::warning("Invoice kunjungan #{$kunjungan->id}: Email tidak tersedia");
                return false;
            }

            // Kirim email
            Mail::to($email)->send(new TransaksiInvoiceMail($kunjungan, 'kunjungan', $pdfContent));

            \Log::info("Invoice kunjungan #{$kunjungan->id} berhasil dikirim ke {$email}");
            return true;

        } catch (\Exception $e) {
            \Log::error("Gagal mengirim invoice kunjungan #{$kunjungan->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Dapatkan semua email admin gudang tertentu + super_admin
     *
     * @param int|null $gudangId
     * @return array
     */
    public static function getApproverEmails($gudangId = null)
    {
        $emails = [];

        // Super admin selalu dapat notifikasi
        $superAdmins = User::where('role', 'super_admin')->pluck('email')->toArray();
        $emails = array_merge($emails, $superAdmins);

        // Admin yang pegang gudang tersebut
        if ($gudangId) {
            $gudangAdmins = User::where('role', 'admin')
                ->where('gudang_id', $gudangId)
                ->pluck('email')
                ->toArray();
            $emails = array_merge($emails, $gudangAdmins);
        }

        return array_unique(array_filter($emails));
    }

    /**
     * Kirim notifikasi saat transaksi DIBUAT (ke pembuat + approvers)
     * Menggunakan dispatch afterResponse agar tidak blocking request
     *
     * @param mixed $transaksi
     * @param string $type - penjualan, pembelian, biaya, kunjungan
     * @return void
     */
    public static function sendCreatedNotification($transaksi, $type)
    {
        // Clone data yang dibutuhkan untuk menghindari issues setelah response
        $transaksiId = $transaksi->id;
        $transaksiClass = get_class($transaksi);
        
        dispatch(function () use ($transaksiId, $transaksiClass, $type) {
            try {
                // Reload transaksi dari database
                $transaksi = $transaksiClass::find($transaksiId);
                if (!$transaksi) return;
                
                // Load relasi yang dibutuhkan
                if (in_array($type, ['penjualan', 'pembelian'])) {
                    $transaksi->load(['items.produk', 'user', 'gudang']);
                } elseif ($type == 'kunjungan') {
                    $transaksi->load(['items.produk', 'user', 'gudang', 'kontak']);
                } else {
                    $transaksi->load(['items', 'user']);
                }

                // Generate PDF
                $pdf = Pdf::loadView("pdf.invoice-{$type}", [$type => $transaksi]);
                $pdfContent = $pdf->output();

                $nomor = $transaksi->nomor ?? $transaksi->custom_number ?? $transaksi->id;
                $gudangId = $transaksi->gudang_id ?? null;

                // 1. Kirim ke pembuat transaksi
                $creatorEmail = $transaksi->user->email ?? null;
                if ($creatorEmail) {
                    Mail::to($creatorEmail)->send(new TransaksiNotificationMail($transaksi, $type, 'created', $pdfContent));
                    \Log::info("Notifikasi created {$type} #{$nomor} dikirim ke pembuat: {$creatorEmail}");
                }

                // 2. Kirim ke semua approvers (admin gudang + super_admin) - needs_approval
                $approverEmails = self::getApproverEmails($gudangId);
                foreach ($approverEmails as $email) {
                    if ($email !== $creatorEmail) { // Jangan kirim double ke pembuat
                        Mail::to($email)->send(new TransaksiNotificationMail($transaksi, $type, 'needs_approval', $pdfContent));
                        \Log::info("Notifikasi needs_approval {$type} #{$nomor} dikirim ke approver: {$email}");
                    }
                }

            } catch (\Exception $e) {
                \Log::error("Gagal mengirim notifikasi created {$type}: " . $e->getMessage());
            }
        })->afterResponse();
    }

    /**
     * Kirim notifikasi saat transaksi DI-APPROVE (ke pembuat)
     * Menggunakan dispatch afterResponse agar tidak blocking request
     *
     * @param mixed $transaksi
     * @param string $type - penjualan, pembelian, biaya, kunjungan
     * @return void
     */
    public static function sendApprovedNotification($transaksi, $type)
    {
        // Clone data yang dibutuhkan untuk menghindari issues setelah response
        $transaksiId = $transaksi->id;
        $transaksiClass = get_class($transaksi);
        
        dispatch(function () use ($transaksiId, $transaksiClass, $type) {
            try {
                // Reload transaksi dari database
                $transaksi = $transaksiClass::find($transaksiId);
                if (!$transaksi) return;
                
                // Load relasi yang dibutuhkan
                if (in_array($type, ['penjualan', 'pembelian'])) {
                    $transaksi->load(['items.produk', 'user', 'gudang', 'approver']);
                } elseif ($type == 'kunjungan') {
                    $transaksi->load(['items.produk', 'user', 'gudang', 'kontak', 'approver']);
                } else {
                    $transaksi->load(['items', 'user', 'approver']);
                }

                // Generate PDF invoice final
                $pdf = Pdf::loadView("pdf.invoice-{$type}", [$type => $transaksi]);
                $pdfContent = $pdf->output();

                $nomor = $transaksi->nomor ?? $transaksi->custom_number ?? $transaksi->id;

                // Kirim ke pembuat transaksi
                $creatorEmail = $transaksi->user->email ?? null;
                if ($creatorEmail) {
                    Mail::to($creatorEmail)->send(new TransaksiNotificationMail($transaksi, $type, 'approved', $pdfContent));
                    \Log::info("Notifikasi approved {$type} #{$nomor} dikirim ke pembuat: {$creatorEmail}");
                }

            } catch (\Exception $e) {
                \Log::error("Gagal mengirim notifikasi approved {$type}: " . $e->getMessage());
            }
        })->afterResponse();
    }
}
