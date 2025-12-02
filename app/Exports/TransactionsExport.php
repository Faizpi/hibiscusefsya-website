<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;

class TransactionsExport implements FromView, WithTitle
{
    protected $transactions;

    public function __construct($transactions)
    {
        $this->transactions = $transactions;
    }

    public function view(): View
    {
        return view('reports.transactions', [
            'transactions' => $this->transactions
        ]);
    }

    public function title(): string
    {
        return 'Laporan Transaksi';
    }
}