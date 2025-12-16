<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Pengeluaran {{ $biaya->penerima }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; color: #333; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #f5576c; padding-bottom: 20px; }
        .header h1 { color: #f5576c; font-size: 24px; margin-bottom: 5px; }
        .header p { color: #666; }
        .invoice-number { font-size: 18px; font-weight: bold; margin-top: 10px; }
        .info-section { display: table; width: 100%; margin-bottom: 30px; }
        .info-box { display: table-cell; width: 50%; vertical-align: top; }
        .info-box h3 { background: #f5576c; color: #fff; padding: 8px 12px; font-size: 12px; margin-bottom: 10px; }
        .info-row { padding: 5px 12px; }
        .info-row .label { display: inline-block; width: 100px; color: #666; }
        .info-row .value { font-weight: 500; }
        table.items { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.items th { background: #f5576c; color: #fff; padding: 10px 8px; text-align: left; font-size: 11px; }
        table.items td { padding: 10px 8px; border-bottom: 1px solid #ddd; }
        table.items .text-right { text-align: right; }
        table.items .text-center { text-align: center; }
        .totals { width: 300px; margin-left: auto; }
        .totals .row { display: table; width: 100%; padding: 8px 0; }
        .totals .row .label { display: table-cell; }
        .totals .row .value { display: table-cell; text-align: right; }
        .totals .grand { background: #f5576c; color: #fff; padding: 12px; font-size: 14px; font-weight: bold; }
        .footer { text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd; color: #666; }
        .status { display: inline-block; padding: 3px 10px; border-radius: 10px; font-size: 10px; font-weight: bold; }
        .status-approved { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-canceled { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    @php
        $dateCode = $biaya->created_at->format('Ymd');
        $noUrut = str_pad($biaya->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $nomorInvoice = "EXP-{$biaya->user_id}-{$dateCode}-{$noUrut}";
        
        $subtotal = $biaya->items->sum('jumlah');
        $pajakNominal = $subtotal * (($biaya->tax_percentage ?? 0) / 100);
        
        $statusClass = 'pending';
        if ($biaya->status == 'Approved') {
            $statusClass = 'approved';
        } elseif ($biaya->status == 'Canceled') {
            $statusClass = 'canceled';
        }
    @endphp

    <div class="header">
        <h1>HIBISCUS EFSYA</h1>
        <p>BUKTI PENGELUARAN</p>
        <div class="invoice-number">{{ $nomorInvoice }}</div>
    </div>

    <div class="info-section">
        <div class="info-box">
            <h3>INFORMASI DOKUMEN</h3>
            <div class="info-row"><span class="label">Tanggal</span><span class="value">{{ $biaya->tgl_transaksi->format('d F Y') }}</span></div>
            <div class="info-row"><span class="label">Waktu</span><span class="value">{{ $biaya->created_at->format('H:i') }} WIB</span></div>
            <div class="info-row"><span class="label">Pembayaran</span><span class="value">{{ $biaya->cara_pembayaran ?? '-' }}</span></div>
            <div class="info-row"><span class="label">Bayar Dari</span><span class="value">{{ $biaya->bayar_dari ?? '-' }}</span></div>
            <div class="info-row"><span class="label">Status</span><span class="value"><span class="status status-{{ $statusClass }}">{{ $biaya->status }}</span></span></div>
        </div>
        <div class="info-box">
            <h3>INFORMASI PENERIMA</h3>
            <div class="info-row"><span class="label">Penerima</span><span class="value">{{ $biaya->penerima ?? '-' }}</span></div>
            <div class="info-row"><span class="label">Pembuat</span><span class="value">{{ $biaya->user->name }}</span></div>
            <div class="info-row"><span class="label">Disetujui</span><span class="value">{{ $biaya->status != 'Pending' && $biaya->approver ? $biaya->approver->name : '-' }}</span></div>
            <div class="info-row"><span class="label">Tag</span><span class="value">{{ $biaya->tag ?? '-' }}</span></div>
        </div>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Kategori</th>
                <th>Deskripsi</th>
                <th class="text-right">Jumlah</th>
            </tr>
        </thead>
        <tbody>
            @foreach($biaya->items as $item)
            <tr>
                <td>{{ $item->kategori }}</td>
                <td>{{ $item->deskripsi ?? '-' }}</td>
                <td class="text-right">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="row"><span class="label">Subtotal</span><span class="value">Rp {{ number_format($subtotal, 0, ',', '.') }}</span></div>
        @if(($biaya->tax_percentage ?? 0) > 0)
        <div class="row"><span class="label">Pajak ({{ $biaya->tax_percentage }}%)</span><span class="value">Rp {{ number_format($pajakNominal, 0, ',', '.') }}</span></div>
        @endif
        <div class="row grand"><span class="label">GRAND TOTAL</span><span class="value">Rp {{ number_format($biaya->grand_total, 0, ',', '.') }}</span></div>
    </div>

    <div class="footer">
        <p><strong>HIBISCUS EFSYA</strong></p>
        <p>marketing@hibiscusefsya.com</p>
    </div>
</body>
</html>
