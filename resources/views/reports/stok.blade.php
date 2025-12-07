<table>
    <thead>
        <tr>
            <th colspan="4"
                style="background-color: #4472C4; color: white; font-weight: bold; text-align: left; padding: 10px;">
                LAPORAN STOK BARANG - {{ strtoupper($gudang->nama_gudang) }}
            </th>
        </tr>
        <tr>
            <th colspan="4" style="background-color: #E7E6E6; font-weight: bold; text-align: left; padding: 5px;">
                Tanggal Export: {{ date('d-m-Y H:i:s') }}
            </th>
        </tr>
        <tr>
            <th style="background-color: #5B9BD5; color: white; font-weight: bold; text-align: center;">No</th>
            <th style="background-color: #5B9BD5; color: white; font-weight: bold; text-align: center;">Produk</th>
            <th style="background-color: #5B9BD5; color: white; font-weight: bold; text-align: center;">Item Code</th>
            <th style="background-color: #5B9BD5; color: white; font-weight: bold; text-align: center;">Jumlah Stok</th>
        </tr>
    </thead>
    <tbody>
        @php $no = 1;
        $totalStok = 0; @endphp
        @foreach($stokData as $item)
            @if($item->produk)
                <tr style="background-color: {{ $no % 2 == 1 ? '#FFFFFF' : '#F2F2F2' }};">
                    <td style="text-align: center;">{{ $no++ }}</td>
                    <td>{{ $item->produk->nama_produk }}</td>
                    <td>{{ $item->produk->item_code }}</td>
                    <td style="text-align: right;">{{ number_format($item->stok, 0, ',', ',') }}</td>
                </tr>
                @php $totalStok += $item->stok; @endphp
            @endif
        @endforeach
        <tr style="background-color: #F4B084; font-weight: bold;">
            <td colspan="3" style="text-align: left;">TOTAL</td>
            <td style="text-align: right;">{{ number_format($totalStok, 0, ',', ',') }}</td>
        </tr>
    </tbody>
</table>