<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Penjualan</title>

    <style>
        /* =========================
           PAGE & BASE SETUP (WAJIB)
        ==========================*/
        @page {
            size: 58mm auto;
            margin: 0;
        }

        html,
        body {
            width: 58mm;
            height: auto !important;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 10pt;
            color: #000;
            padding: 3mm 1mm;
            box-sizing: border-box;
            background: #fff;
        }

        /* CEGAH PAGE BREAK ANDROID */
        * {
            page-break-before: avoid !important;
            page-break-after: avoid !important;
            page-break-inside: avoid !important;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        /* =========================
           HEADER
        ==========================*/
        .header {
            text-align: center;
            margin-bottom: 6px;
        }

        .logo {
            max-width: 45mm;
            height: auto;
            margin-bottom: 4px;
        }

        .title {
            font-size: 12pt;
            font-weight: bold;
            margin: 0;
        }

        /* =========================
           DIVIDER
        ==========================*/
        .divider {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        /* =========================
           INFO TABLE
        ==========================*/
        .info-table {
            width: 100%;
            font-size: 9pt;
        }

        .info-table td {
            vertical-align: top;
            padding: 1px 0;
        }

        .label {
            width: 35%;
        }

        .colon {
            width: 5%;
            text-align: center;
        }

        .value {
            width: 60%;
        }

        /* =========================
           ITEM
        ==========================*/
        .item-container {
            margin-bottom: 6px;
        }

        .item-name {
            font-weight: bold;
            font-size: 10pt;
            margin-bottom: 2px;
        }

        .details-table {
            width: 100%;
            font-size: 9pt;
        }

        .details-table td {
            padding: 1px 0;
        }

        .lbl {
            width: 35%;
        }

        .val {
            width: 65%;
            text-align: right;
        }

        /* =========================
           TOTAL
        ==========================*/
        .total-table {
            width: 100%;
            font-size: 9pt;
        }

        .total-table td {
            padding: 1px 0;
        }

        .grand-total {
            font-weight: bold;
            font-size: 11pt;
            border-top: 1px dashed #000;
            padding-top: 4px;
        }

        /* =========================
           FOOTER
        ==========================*/
        .footer {
            text-align: center;
            font-size: 9pt;
            margin-top: 10px;
        }

        /* =========================
           PRINT ONLY
        ==========================*/
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>

<div id="receipt">

    @php
        $dateCode = $penjualan->created_at->format('Ymd');
        $noUrut = str_pad($penjualan->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $nomorInvoice = "INV-{$penjualan->user_id}-{$dateCode}-{$noUrut}";
    @endphp

    <!-- HEADER -->
    <div class="header">
        @if(file_exists(public_path('assets/images/logo.png')))
        <img src="{{ asset('assets/images/logo.png') }}" class="logo">
        @endif
        <div class="title">INVOICE PENJUALAN</div>
    </div>

    <!-- INFO -->
    <table class="info-table">
        <tr><td class="label">Nomor</td><td class="colon">:</td><td class="value">{{ $nomorInvoice }}</td></tr>
        <tr><td class="label">Tanggal</td><td class="colon">:</td>
            <td class="value">{{ $penjualan->tgl_transaksi->format('d/m/Y') }} | {{ $penjualan->created_at->format('H:i') }}</td>
        </tr>
        <tr><td class="label">Jatuh Tempo</td><td class="colon">:</td>
            <td class="value">{{ $penjualan->tgl_jatuh_tempo ? $penjualan->tgl_jatuh_tempo->format('d/m/Y') : '-' }}</td>
        </tr>
        <tr><td class="label">Pembayaran</td><td class="colon">:</td><td class="value">{{ $penjualan->metode_pembayaran ?? 'Net 7' }}</td></tr>
        <tr><td class="label">Pelanggan</td><td class="colon">:</td><td class="value">{{ $penjualan->pelanggan ?? '-' }}</td></tr>
        <tr><td class="label">Sales</td><td class="colon">:</td><td class="value">{{ $penjualan->user->name ?? '-' }}</td></tr>
        <tr><td class="label">Gudang</td><td class="colon">:</td><td class="value">{{ $penjualan->gudang->nama_gudang ?? '-' }}</td></tr>
        <tr><td class="label">Status</td><td class="colon">:</td><td class="value">{{ $penjualan->status }}</td></tr>
        @if($penjualan->approver_id && $penjualan->approver)
        <tr><td class="label">Disetujui</td><td class="colon">:</td><td class="value">{{ $penjualan->approver->name }}</td></tr>
        @endif
    </table>

    <div class="divider"></div>

    <!-- ITEMS -->
    @foreach($penjualan->items as $item)
        <div class="item-container">
            <div class="item-name">
                {{ $item->produk->nama_produk }} ({{ $item->produk->item_code ?? '-' }})
            </div>

            <table class="details-table">
                <tr><td class="lbl">Qty</td><td class="val">{{ $item->kuantitas }} Pcs</td></tr>
                <tr><td class="lbl">Harga</td><td class="val">Rp {{ number_format($item->harga_satuan,0,',','.') }}</td></tr>
                @if($item->diskon_per_item > 0)
                <tr><td class="lbl">Diskon</td><td class="val">- Rp {{ number_format($item->diskon_per_item,0,',','.') }}</td></tr>
                @endif
                <tr><td class="lbl" style="font-weight:bold">Jumlah</td>
                    <td class="val" style="font-weight:bold">Rp {{ number_format($item->jumlah_baris,0,',','.') }}</td>
                </tr>
            </table>
        </div>
    @endforeach

    <div class="divider"></div>

    <!-- TOTAL -->
    <table class="total-table">
        <tr><td class="lbl">Subtotal</td><td class="val">Rp {{ number_format($penjualan->items->sum('jumlah_baris'),0,',','.') }}</td></tr>
        @if($penjualan->diskon_akhir > 0)
            <tr><td class="lbl">Diskon</td><td class="val">- Rp {{ number_format($penjualan->diskon_akhir,0,',','.') }}</td></tr>
        @endif
        @if($penjualan->tax_percentage > 0)
        @php
            $kenaPajak = max(0, $penjualan->items->sum('jumlah_baris') - $penjualan->diskon_akhir);
            $pajakNominal = $kenaPajak * ($penjualan->tax_percentage / 100);
        @endphp
            <tr><td class="lbl">Pajak ({{ $penjualan->tax_percentage }}%)</td><td class="val">Rp {{ number_format($pajakNominal,0,',','.') }}</td></tr>
        @endif
        <tr>
            <td class="lbl grand-total">GRAND TOTAL</td>
            <td class="val grand-total">Rp {{ number_format($penjualan->grand_total,0,',','.') }}</td>
        </tr>
    </table>

    <!-- FOOTER -->
    <div class="footer">
        <div>marketing@hibiscusefsya.com</div>
        <div>-- Terima Kasih --</div>
  div>

<!-- html2canvas for client-side image rendering -->
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script>
async function renderImage() {
    const receipt = document.getElementById('receipt');
    
    const canvas = await html2canvas(receipt, {
        scale: 2,
        backgroundColor: '#fff',
        useCORS: true,
        width: 384,
        windowWidth: 384
    });
    
    const img = canvas.toDataURL('image/png');
    
    // Replace body dengan image untuk iWare Image Mode
    document.body.innerHTML = `
        <div style="text-align:center;background:#fff;padding:10px;">
            <img src="${img}" style="width:100%;max-width:384px;display:block;margin:0 auto;">
            <p style="margin-top:10px;font-size:12px;color:#666;">
                Tap & hold gambar → Share → Pilih iWare → Print
            </p>
        </div>
    `;
}

// Auto-render setelah halaman load
window.addEventListener('load', () => {
    setTimeout(renderImage, 500);
});
</script>

</  </div>

</body>
</html>
