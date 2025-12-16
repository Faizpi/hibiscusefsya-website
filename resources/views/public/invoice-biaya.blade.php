<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bukti Pengeluaran - {{ $biaya->penerima }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            min-height: 100vh;
            padding: 20px 0;
        }

        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .invoice-header {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
            color: #fff;
            padding: 30px;
            text-align: center;
        }

        .invoice-header img {
            max-width: 150px;
            margin-bottom: 15px;
        }

        .invoice-header h1 {
            font-size: 24px;
            margin: 0;
            font-weight: 600;
        }

        .invoice-body {
            padding: 30px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        @media (max-width: 576px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
        }

        .info-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
        }

        .info-box h5 {
            color: #f5576c;
            font-size: 14px;
            text-transform: uppercase;
            margin-bottom: 15px;
            font-weight: 600;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #dee2e6;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-row .label {
            color: #6c757d;
            font-size: 14px;
        }

        .info-row .value {
            font-weight: 500;
            text-align: right;
        }

        .items-table {
            margin-bottom: 30px;
        }

        .items-table th {
            background: #f5576c;
            color: #fff;
            font-weight: 500;
            font-size: 14px;
            border: none !important;
        }

        .items-table td {
            vertical-align: middle;
        }

        .totals-box {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 16px;
        }

        .total-row.grand {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: #fff;
            padding: 15px 20px;
            border-radius: 10px;
            margin: 15px -20px -20px -20px;
            font-size: 20px;
            font-weight: 600;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-canceled {
            background: #f8d7da;
            color: #721c24;
        }

        .download-section {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }

        .btn-download {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border: none;
            color: #fff;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .btn-download:hover {
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(245, 87, 108, 0.4);
            text-decoration: none;
        }

        .footer-text {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-size: 14px;
        }
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

    <div class="invoice-container">
        <div class="invoice-header">
            <img src="{{ asset('assets/img/logoHE1.png') }}" alt="Hibiscus Efsya" onerror="this.style.display='none'">
            <h1>BUKTI PENGELUARAN</h1>
            <p class="mb-0 mt-2" style="font-size: 18px;">{{ $nomorInvoice }}</p>
        </div>

        <div class="invoice-body">
            <div class="info-grid">
                <div class="info-box">
                    <h5><i class="fas fa-info-circle mr-2"></i>Informasi Dokumen</h5>
                    <div class="info-row">
                        <span class="label">Tanggal</span>
                        <span class="value">{{ $biaya->tgl_transaksi->format('d F Y') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Waktu</span>
                        <span class="value">{{ $biaya->created_at->format('H:i') }} WIB</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Pembayaran</span>
                        <span class="value">{{ $biaya->cara_pembayaran ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Bayar Dari</span>
                        <span class="value">{{ $biaya->bayar_dari ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Status</span>
                        <span class="value">
                            <span class="status-badge status-{{ $statusClass }}">{{ $biaya->status }}</span>
                        </span>
                    </div>
                </div>

                <div class="info-box">
                    <h5><i class="fas fa-user mr-2"></i>Informasi Penerima</h5>
                    <div class="info-row">
                        <span class="label">Penerima</span>
                        <span class="value">{{ $biaya->penerima ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Pembuat</span>
                        <span class="value">{{ $biaya->user->name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Disetujui</span>
                        <span
                            class="value">{{ $biaya->status != 'Pending' && $biaya->approver ? $biaya->approver->name : '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Tag</span>
                        <span class="value">{{ $biaya->tag ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <div class="items-table">
                <table class="table table-bordered">
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
                                <td><strong>{{ $item->kategori }}</strong></td>
                                <td>{{ $item->deskripsi ?? '-' }}</td>
                                <td class="text-right">Rp {{ number_format($item->jumlah, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="totals-box">
                <div class="total-row">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                </div>
                @if(($biaya->tax_percentage ?? 0) > 0)
                    <div class="total-row">
                        <span>Pajak ({{ $biaya->tax_percentage }}%)</span>
                        <span>Rp {{ number_format($pajakNominal, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="total-row grand">
                    <span>GRAND TOTAL</span>
                    <span>Rp {{ number_format($biaya->grand_total, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <div class="download-section">
            <a href="{{ route('public.invoice.biaya.download', $biaya->id) }}" class="btn-download">
                <i class="fas fa-download mr-2"></i>Download PDF
            </a>
        </div>

        <div class="footer-text">
            <p class="mb-1"><strong>HIBISCUS EFSYA</strong></p>
            <p class="mb-0">marketing@hibiscusefsya.com</p>
        </div>
    </div>

</body>

</html>