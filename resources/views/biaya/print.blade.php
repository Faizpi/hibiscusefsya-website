<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk Biaya</title>
    <style>
        /* ... CSS SAMA SEPERTI DI ATAS ... */
        @page { size: 58mm; margin: 0; }
        @media screen {
            html { background-color: #E0E0E0; }
            body { margin: 2rem auto !important; box-shadow: 0 0 6px rgba(0,0,0,0.3); background: #fff; }
        }
        body { width: 54mm; font-family: 'Consolas', 'Courier New', monospace; font-size: 9pt; color: #000; margin: 0 auto; padding: 4mm 2mm; }
        .header { text-align: center; margin-bottom: 10px; }
        .logo { max-width: 45mm; height: auto; margin-bottom: 5px; }
        .title { font-size: 9pt; margin: 0; font-weight: bold; }
        .divider { border-top: 1px dashed #000; margin: 6px 0; }
        .info-table { width: 100%; font-size: 8pt; }
        .info-table td { vertical-align: top; padding-bottom: 2px; }
        .info-table .label { width: 35%; }
        .info-table .colon { width: 5%; text-align: center; }
        .info-table .value { width: 60%; }
        .item-container { margin-bottom: 8px; }
        .item-name { font-weight: bold; font-size: 9pt; margin-bottom: 2px; }
        .details-table { width: 100%; font-size: 8.5pt; }
        .details-table .lbl { width: 35%; text-align: left; }
        .details-table .val { width: 65%; text-align: right; }
        .total-table { width: 100%; font-size: 9pt; margin-top: 5px; }
        .total-table .val { text-align: right; }
        .grand-total { font-weight: bold; font-size: 11pt; padding-top: 5px; border-top: 1px dashed #000; }
        .footer { text-align: center; margin-top: 15px; font-size: 8pt; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body onload="window.print()">

    @php
        $dateCode = $biaya->created_at->format('Ymd');
        $noUrut = str_pad($biaya->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $nomorInvoice = "EXP-{$biaya->user_id}-{$dateCode}-{$noUrut}";
    @endphp

    <div class="header">
        <img src="{{ asset('assets/img/logoHE1.png') }}" alt="HIBISCUS EFSYA" class="logo">
        <div class="title">BUKTI PENGELUARAN</div>
    </div>

    <table class="info-table">
        <tr><td class="label">Nomor</td><td class="colon">:</td><td class="value">{{ $nomorInvoice }}</td></tr>
        <tr><td class="label">Tanggal</td><td class="colon">:</td><td class="value">{{ $biaya->tgl_transaksi->format('d/m/Y') }}</td></tr>
        <tr><td class="label">Pembayaran</td><td class="colon">:</td><td class="value">{{ $biaya->cara_pembayaran ?? '-' }}</td></tr>
        <tr><td class="label">Bayar Dari</td><td class="colon">:</td><td class="value">{{ $biaya->bayar_dari ?? '-' }}</td></tr>
        <tr><td class="label">Penerima</td><td class="colon">:</td><td class="value">{{ $biaya->penerima }}</td></tr>
        <tr><td class="label">Dibuat</td><td class="colon">:</td><td class="value">{{ $biaya->user->name }}</td></tr>
        <tr><td class="label">Disetujui</td><td class="colon">:</td><td class="value">{{ $biaya->approver->name ?? '-' }}</td></tr>
        <tr><td class="label">Status</td><td class="colon">:</td><td class="value">{{ $biaya->status }}</td></tr>
    </table>

    <div class="divider"></div>

    @foreach($biaya->items as $item)
    <div class="item-container">
        <div class="item-name">{{ $item->kategori }}</div>
        
        <table class="details-table">
            @if($item->deskripsi)
            <tr>
                <td class="lbl">Ket</td>
                <td class="val">{{ $item->deskripsi }}</td>
            </tr>
            @endif
            <tr>
                <td class="lbl" style="font-weight: bold;">Jumlah</td>
                <td class="val" style="font-weight: bold;">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>
    @endforeach

    <div class="divider"></div>

    <table class="total-table">
        <tr>
            <td class="lbl">Subtotal</td>
            <td class="val">Rp {{ number_format($biaya->items->sum('jumlah'), 0, ',', '.') }}</td>
        </tr>

        @if($biaya->tax_percentage > 0)
        @php
            $subtotal = $biaya->items->sum('jumlah');
            $pajakNominal = $subtotal * ($biaya->tax_percentage / 100);
        @endphp
        <tr>
            <td class="lbl">Pajak ({{ $biaya->tax_percentage }}%)</td>
            <td class="val">Rp {{ number_format($pajakNominal, 0, ',', '.') }}</td>
        </tr>
        @endif

        <tr>
            <td class="lbl grand-total">GRAND TOTAL</td>
            <td class="val grand-total">Rp {{ number_format($biaya->grand_total, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="footer">
        <p>marketing@hibiscusefsya.com</p>
        <p>-- Terima Kasih --</p>
        <button type="button" class="no-print" onclick="window.print()" style="margin-top:10px; padding:5px 10px;">Print Ulang</button>
    </div>

</body>
</html>