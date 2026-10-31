<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <style>
        * { box-sizing: border-box; }
        body { font-family: sans-serif; font-size: 12px; color: #1f2937; margin: 0; padding: 0; }
        .header { background: #1e3a8a; color: #fff; padding: 24px 32px; }
        .header h1 { margin: 0; font-size: 22px; letter-spacing: 1px; }
        .header .co { font-size: 13px; font-weight: bold; margin-bottom: 2px; }
        .header .co-sub { font-size: 10px; opacity: .85; }
        .doc-title { background: #eff6ff; border-bottom: 3px solid #1e3a8a; padding: 12px 32px; }
        .doc-title h2 { margin: 0; font-size: 17px; color: #1e3a8a; }
        .doc-title .st { display: inline-block; background: #1e3a8a; color: #fff; font-size: 10px; padding: 2px 10px; border-radius: 10px; margin-left: 8px; vertical-align: middle; }
        .body { padding: 16px 32px; }
        .info { width: 100%; margin-bottom: 12px; }
        .info td { vertical-align: top; padding: 2px 4px; font-size: 11px; }
        .info .lbl { color: #6b7280; width: 110px; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 6px; font-size: 11px; }
        table.items th { background: #1e3a8a; color: #fff; padding: 8px 6px; text-align: left; }
        table.items td { padding: 7px 6px; border-bottom: 1px solid #e5e7eb; }
        table.items tr:nth-child(even) td { background: #f8fafc; }
        .num { text-align: right; white-space: nowrap; }
        .totals { width: 45%; margin-left: auto; margin-top: 10px; font-size: 11px; }
        .totals td { padding: 4px 6px; }
        .totals tr.grand td { background: #1e3a8a; color: #fff; font-weight: bold; font-size: 13px; }
        .note-box { background: #f8fafc; border-left: 4px solid #1e3a8a; padding: 8px 12px; margin-top: 12px; font-size: 11px; }
        .sign { width: 100%; margin-top: 36px; font-size: 11px; }
        .sign td { text-align: center; }
        .footer { text-align: center; font-size: 9px; color: #9ca3af; margin-top: 24px; border-top: 1px solid #e5e7eb; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="co">{{ $co['name'] ?? config('app.name') }}</div>
        <div class="co-sub">{{ $co['alamat'] ?? '' }}{{ isset($co['telepon']) && $co['telepon'] ? ' · Telp: '.$co['telepon'] : '' }}{{ isset($co['email']) && $co['email'] ? ' · '.$co['email'] : '' }}</div>
        <h1>PURCHASE ORDER</h1>
    </div>
    <div class="doc-title">
        <h2>{{ $po->po_nomor }}<span class="st">{{ $po->po_status }}</span></h2>
    </div>
    <div class="body">
        <table class="info">
            <tr>
                <td style="width:55%">
                    <strong>Kepada Yth (Vendor):</strong><br />
                    <strong>{{ $po->po_vendor }}</strong><br />
                    {{ $po->po_alamat ?: $vendor?->vendor_alamat }}<br />
                    {{ $po->po_telepon ?: $vendor?->vendor_telepon }}
                </td>
                <td style="width:45%">
                    <table>
                        <tr><td class="lbl">Tanggal PO</td><td>: {{ formatDate($po->po_tanggal) }}</td></tr>
                        <tr><td class="lbl">Harap diterima</td><td>: {{ $po->po_expected_date ? formatDate($po->po_expected_date) : '-' }}</td></tr>
                        <tr><td class="lbl">Status</td><td>: {{ $po->po_status }}</td></tr>
                    </table>
                </td>
            </tr>
        </table>
        <table class="items">
            <thead>
                <tr><th style="width:24px">No</th><th>Deskripsi</th><th class="num">Qty</th><th class="num">Harga</th><th class="num">Diskon</th><th class="num">Subtotal</th></tr>
            </thead>
            <tbody>
                @foreach($po->hasItems as $i => $it)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $it->poi_deskripsi }}</td>
                    <td class="num">{{ rtrim(rtrim(number_format($it->poi_qty, 2), '0'), '.') }}</td>
                    <td class="num">{{ number_format($it->poi_harga_satuan, 0, ',', '.') }}</td>
                    <td class="num">{{ number_format($it->poi_diskon, 0, ',', '.') }}</td>
                    <td class="num">{{ number_format($it->poi_subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <table class="totals">
            <tr><td>Bruto</td><td class="num">{{ number_format($t['bruto'], 0, ',', '.') }}</td></tr>
            <tr><td>Diskon per barang</td><td class="num">({{ number_format($t['diskon_item'], 0, ',', '.') }})</td></tr>
            <tr><td>Diskon global</td><td class="num">({{ number_format($t['diskon_global'], 0, ',', '.') }})</td></tr>
            <tr><td>Ongkir</td><td class="num">{{ number_format($t['ongkir'], 0, ',', '.') }}</td></tr>
            <tr><td>Biaya lain</td><td class="num">{{ number_format($t['biaya_lain'], 0, ',', '.') }}</td></tr>
            <tr class="grand"><td>TOTAL</td><td class="num">Rp {{ number_format($t['total'], 0, ',', '.') }}</td></tr>
        </table>
        @if($po->po_catatan)
        <div class="note-box">
            <strong>Catatan:</strong>
            <div>{!! $po->po_catatan !!}</div>
        </div>
        @endif
        <table class="sign">
            <tr>
                <td>Hormat kami,<br /><br /><br /><br /><br />( ______________________ )</td>
                <td>Menyetujui,<br /><br /><br /><br /><br />( ______________________ )</td>
            </tr>
        </table>
        <div class="footer">Dokumen ini dicetak dari sistem pada {{ date('d/m/Y H:i') }}</div>
    </div>
</body>
</html>
