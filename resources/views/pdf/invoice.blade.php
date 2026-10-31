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
        .bank-box { margin-top: 14px; background: #1e3a8a; border-radius: 10px; overflow: hidden; color: #fff; }
        .bank-box table { width: 100%; border-collapse: collapse; }
        .bank-box td { padding: 12px 16px; vertical-align: middle; }
        .bank-box .bank-left { width: 38%; border-right: 1px solid rgba(255,255,255,.25); }
        .bank-box .bank-label { font-size: 9px; letter-spacing: 2px; opacity: .8; margin-bottom: 6px; }
        .bank-box .bank-bank { display: inline-block; background: #fff; color: #1e3a8a; font-size: 12px; font-weight: bold; letter-spacing: 2px; padding: 4px 16px; border-radius: 20px; }
        .bank-box .bank-no { font-family: Courier, monospace; font-size: 22px; font-weight: bold; letter-spacing: 4px; }
        .bank-box .bank-an { font-size: 11px; opacity: .85; margin-top: 3px; }
        .sign { width: 100%; margin-top: 36px; font-size: 11px; }
        .sign td { text-align: center; }
        .footer { text-align: center; font-size: 9px; color: #9ca3af; margin-top: 24px; border-top: 1px solid #e5e7eb; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="co">{{ $co['name'] ?? config('app.name') }}</div>
        <div class="co-sub">{{ $co['alamat'] ?? '' }}{{ isset($co['telepon']) && $co['telepon'] ? ' · Telp: '.$co['telepon'] : '' }}{{ isset($co['email']) && $co['email'] ? ' · '.$co['email'] : '' }}</div>
        <h1>INVOICE</h1>
    </div>
    <div class="doc-title">
        <h2>{{ $invoice->invoice_nomor }}</h2>
    </div>
    <div class="body">
        <table class="info">
            <tr>
                <td style="width:55%">
                    <strong>Kepada Yth:</strong><br />
                    <strong>{{ $invoice->invoice_pelanggan }}</strong><br />
                    {{ $invoice->invoice_alamat ?: $customer?->customer_alamat }}<br />
                    {{ $invoice->invoice_telepon ?: $customer?->customer_telepon }}
                </td>
                <td style="width:45%">
                    <table>
                        <tr><td class="lbl">Tanggal</td><td>: {{ formatDate($invoice->invoice_tanggal) }}</td></tr>
                        <tr><td class="lbl">Jatuh tempo</td><td>: {{ $invoice->invoice_jatuh_tempo ? formatDate($invoice->invoice_jatuh_tempo) : '-' }}</td></tr>
                        <tr><td class="lbl">Termin</td><td>: {{ $termin ?? '-' }}</td></tr>
                        <tr><td class="lbl">Status</td><td>: {{ $invoice->invoice_status }}</td></tr>
                    </table>
                </td>
            </tr>
        </table>
        <table class="items">
            <thead>
                <tr><th style="width:24px">No</th><th>Deskripsi</th><th class="num">Qty</th><th class="num">Harga</th><th class="num">Diskon</th><th class="num">Subtotal</th></tr>
            </thead>
            <tbody>
                @foreach($invoice->hasItems as $i => $it)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $it->item_deskripsi }}</td>
                    <td class="num">{{ rtrim(rtrim(number_format($it->item_qty, 2), '0'), '.') }}</td>
                    <td class="num">{{ number_format($it->item_harga_satuan, 0, ',', '.') }}</td>
                    <td class="num">{{ number_format($it->item_diskon, 0, ',', '.') }}</td>
                    <td class="num">{{ number_format($it->item_subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <table class="totals">
            <tr><td>Bruto</td><td class="num">{{ number_format($t['bruto'], 0, ',', '.') }}</td></tr>
            <tr><td>Diskon per barang</td><td class="num">({{ number_format($t['diskon_item'], 0, ',', '.') }})</td></tr>
            <tr><td>Diskon global</td><td class="num">({{ number_format($t['diskon_global'], 0, ',', '.') }})</td></tr>
            <tr><td>DPP</td><td class="num">{{ number_format($t['dpp'], 0, ',', '.') }}</td></tr>
            <tr><td>Pajak ({{ $invoice->invoice_pajak_persen }}%)</td><td class="num">{{ number_format($t['pajak'], 0, ',', '.') }}</td></tr>
            <tr><td>Ongkir</td><td class="num">{{ number_format($t['ongkir'], 0, ',', '.') }}</td></tr>
            <tr><td>Biaya lain</td><td class="num">{{ number_format($t['biaya_lain'], 0, ',', '.') }}</td></tr>
            <tr class="grand"><td>TOTAL</td><td class="num">Rp {{ number_format($t['total'], 0, ',', '.') }}</td></tr>
        </table>
        @if($invoice->invoice_catatan)
        <div class="note-box">
            <strong>Catatan:</strong>
            <div>{!! $invoice->invoice_catatan !!}</div>
        </div>
        @endif
        @if(($bank['tampil'] ?? false) && ($bank['nomor'] ?? ''))
        <div class="bank-box">
            <table>
                <tr>
                    <td class="bank-left">
                        <div class="bank-label">PEMBAYARAN VIA<br />TRANSFER BANK</div>
                        <div class="bank-bank">{{ strtoupper($bank['nama_bank']) }}</div>
                    </td>
                    <td>
                        <div class="bank-no">{{ trim(chunk_split(preg_replace('/\s+/', '', $bank['nomor']), 4, ' ')) }}</div>
                        <div class="bank-an">a.n. {{ $bank['atas_nama'] }}</div>
                    </td>
                </tr>
            </table>
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
