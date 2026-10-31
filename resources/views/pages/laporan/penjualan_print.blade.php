<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: Arial, Helvetica, sans-serif; font-size: 11px; color: #000; margin: 20px; }
        .header { width: 100%; border: 0; margin-bottom: 10px; }
        .header h3 { margin: 2px 0; white-space: nowrap; }
        .header td { white-space: nowrap; }
        table.data { width: 100%; border-collapse: collapse; border-spacing: 0; }
        table.data th, table.data td { border: 1px solid #000; padding: 4px 6px; }
        table.data thead th { background: #eee; text-align: left; }
        table.data td.num, table.data th.num { text-align: right; }
        .footer { width: 100%; margin-top: 20px; border: 0; }
        .print-date, .print-person { text-align: right; }
        .no-print { margin-bottom: 12px; }
        @media print { .no-print { display: none !important; } }
    </style>
</head>
<body>

<div class="no-print" style="position:fixed;bottom:16px;right:16px;z-index:9999;display:flex;gap:8px;">
    <button onclick="window.print()" style="padding:8px 20px;font-size:14px;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,.2);">Cetak / Simpan PDF</button>
    <a href="{{ route('laporan.getPenjualanExcel', $query) }}" style="padding:8px 20px;font-size:14px;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,.2);background:#16a34a;color:#fff;text-decoration:none;font-family:Arial,Helvetica,sans-serif;">Export Excel</a>
</div>

@php($logoUrl = \App\Models\WebsiteSetting::fileUrl(config('website.logo')))
<table class="header">
    <tr>
        <td style="vertical-align:middle;">
            <h3><b>{{ $title }}</b></h3>
            <h3>{{ config('website.name') }}</h3>
            <h3>Periode : Tahun {{ $tahun }}{{ $bulan ? ' Bulan '.$bulan : '' }}{{ $pelanggan ? ' · Pelanggan : '.$pelanggan : '' }}</h3>
        </td>
        <td style="width:100px;min-width:100px;text-align:right;vertical-align:middle;">
            @if($logoUrl)
            <img src="{{ $logoUrl }}" alt="Logo" style="display:block;margin-left:auto;width:90px;max-width:90px;height:auto;max-height:60px;object-fit:contain;">
            @endif
        </td>
    </tr>
</table>

<div class="table-responsive" id="table_data">
    <table class="data">
        <thead>
            <tr>
                <th width="1">No.</th>
                <th>Tanggal</th>
                <th>Nomor</th>
                <th>Pelanggan</th>
                <th class="num">Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $r)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ formatDate($r->invoice_tanggal) }}</td>
                <td>{{ $r->invoice_nomor }}</td>
                <td>{{ $r->invoice_pelanggan }}</td>
                <td class="num">{{ formatAngka((int) round($r->total)) }}</td>
                <td>{{ $r->invoice_status }}</td>
            </tr>
            @empty
            <tr><td colspan="6" style="text-align:center;">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" style="text-align:right;">Total:</th>
                <th class="num">{{ formatAngka((int) round($total)) }}</th>
                <th style="text-align:left;">{{ $rows->count() }} baris</th>
            </tr>
        </tfoot>
    </table>
</div>

<table class="footer">
    <tr>
        <td colspan="2" class="print-date">{{ config('app.location') }}, {{ date('d F Y') }}</td>
    </tr>
    <tr>
        <td colspan="2" class="print-person">{{ auth()->user()->name ?? '' }}</td>
    </tr>
</table>

</body>
</html>
