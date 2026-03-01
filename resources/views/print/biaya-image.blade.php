<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Struk Biaya</title>

    <style>
        * {
            box-sizing: border-box;
            font-family: "Courier New", monospace;
            color: #000;
        }

        body {
            margin: 0;
            padding: 12px;
            width: 384px;
            /* 58mm thermal printer */
            background: #fff;
            font-size: 14px;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .logo {
            max-width: 280px;
            margin-bottom: 5px;
        }

        .title {
            font-weight: bold;
            font-size: 16px;
        }

        .divider {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        td {
            padding: 2px 0;
            vertical-align: top;
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

        .item-name {
            font-weight: bold;
            margin-top: 6px;
        }

        .lbl {
            width: 40%;
        }

        .val {
            width: 60%;
            text-align: right;
        }

        .grand-total {
            font-weight: bold;
            font-size: 16px;
            border-top: 1px dashed #000;
            padding-top: 6px;
        }

        .footer {
            text-align: center;
            margin-top: 12px;
            font-size: 13px;
        }
    </style>
</head>

<body>

    @php
        $dateCode = $biaya->created_at->format('Ymd');
        $noUrut = str_pad($biaya->no_urut_harian, 3, '0', STR_PAD_LEFT);
        $nomorBiaya = "EXP-{$biaya->user_id}-{$dateCode}-{$noUrut}";
    @endphp

    <div class="header">
        @if(file_exists(public_path('assets/img/logoHE1.png')))
            <img src="{{ public_path('assets/img/logoHE1.png') }}" class="logo">
        @endif
        <div class="title">BUKTI PENGELUARAN</div>
    </div>

    <table>
        <tr>
            <td class="label">Nomor</td>
            <td class="colon">:</td>
            <td class="value">{{ $nomorBiaya }}</td>
        </tr>
        <tr>
            <td class="label">Tanggal</td>
            <td class="colon">:</td>
            <td class="value">{{ $biaya->tgl_transaksi->format('d/m/Y') }} {{ $biaya->created_at->format('H:i') }}</td>
        </tr>
        <tr>
            <td class="label">Jatuh Tempo</td>
            <td class="colon">:</td>
            <td class="value">{{ $biaya->tgl_jatuh_tempo ? $biaya->tgl_jatuh_tempo->format('d/m/Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Pembayaran</td>
            <td class="colon">:</td>
            <td class="value">{{ $biaya->metode_pembayaran ?? 'Cash' }}</td>
        </tr>
        <tr>
            <td class="label">Kontak</td>
            <td class="colon">:</td>
            <td class="value">{{ $biaya->nama_pemasok ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Diinput</td>
            <td class="colon">:</td>
            <td class="value">{{ $biaya->user->name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Gudang</td>
            <td class="colon">:</td>
            <td class="value">{{ optional($biaya->gudang)->nama_gudang ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Status</td>
            <td class="colon">:</td>
            <td class="value">{{ $biaya->status }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    @foreach($biaya->items as $item)
        <div class="item-name">
            {{ $item->kategori ?? 'Kategori' }}
        </div>

        <table>
            <tr>
                <td class="lbl">Deskripsi</td>
                <td class="val">{{ $item->deskripsi ?? '-' }}</td>
            </tr>
            <tr>
                <td class="lbl"><b>Jumlah</b></td>
                <td class="val"><b>Rp {{ number_format($item->jumlah, 0, ',', '.') }}</b></td>
            </tr>
        </table>
    @endforeach

    <div class="divider"></div>

    <table>
        <tr>
            <td class="lbl grand-total">TOTAL BIAYA</td>
            <td class="val grand-total">Rp {{ number_format($biaya->items->sum('jumlah'), 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="footer">
        accounting@hibiscusefsya.com<br>
        -- Dokumen Internal --
    </div>

</body>

</html>