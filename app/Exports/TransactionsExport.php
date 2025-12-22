<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TransactionsExport implements FromView, WithTitle, ShouldAutoSize, WithStyles
{
    protected $transactions;
    protected $exportType;

    public function __construct($transactions, $exportType = 'all')
    {
        $this->transactions = $transactions;
        $this->exportType = $exportType;
    }

    public function view(): View
    {
        $viewName = 'reports.transactions';

        // Gunakan view berbeda berdasarkan tipe
        if ($this->exportType == 'penjualan') {
            $viewName = 'reports.penjualan';
        } elseif ($this->exportType == 'pembelian') {
            $viewName = 'reports.pembelian';
        } elseif ($this->exportType == 'biaya') {
            $viewName = 'reports.biaya';
        } elseif ($this->exportType == 'kunjungan') {
            $viewName = 'reports.kunjungan';
        }

        return view($viewName, [
            'transactions' => $this->transactions,
            'exportType' => $this->exportType
        ]);
    }

    public function title(): string
    {
        $titles = [
            'all' => 'Semua Transaksi',
            'penjualan' => 'Laporan Penjualan',
            'pembelian' => 'Laporan Pembelian',
            'biaya' => 'Laporan Biaya',
            'kunjungan' => 'Laporan Kunjungan'
        ];

        return $titles[$this->exportType] ?? 'Laporan Transaksi';
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header row bold
            1 => ['font' => ['bold' => true]],
        ];
    }
}