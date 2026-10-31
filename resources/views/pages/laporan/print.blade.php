<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $judul }}</title>
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
    <a href="{{ $excelUrl }}" style="padding:8px 20px;font-size:14px;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,.2);background:#16a34a;color:#fff;text-decoration:none;font-family:Arial,Helvetica,sans-serif;">Export Excel</a>
</div>

@php($logoUrl = \App\Models\WebsiteSetting::fileUrl(config('website.logo')))
<table class="header">
    <tr>
        <td style="vertical-align:middle;">
            <h3><b>{{ strtoupper($judul) }}</b></h3>
            <h3>{{ config('website.name') }}</h3>
            <h3>Periode : {{ $periode }}</h3>
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
                @foreach($headers as $h)
                <th>{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $r)
            @php($cells = is_array($r) ? array_values($r) : array_values((array) $r))
            <tr>
                <td>{{ $loop->iteration }}</td>
                @foreach($cells as $c)
                @php($s = (string) $c)
                @php($isNum = $s !== '' && preg_match('/^[\d\s\.\,\-]+$/', $s) && preg_match('/\d/', $s))
                <td @if($isNum) class="num" @endif>{{ $c }}</td>
                @endforeach
            </tr>
            @empty
            <tr><td colspan="{{ count($headers) + 1 }}" style="text-align:center;">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
        @if(!empty($foot))
        <tfoot>
            <tr>
                <th></th>
                @foreach($foot as $i => $f)
                @php($s = (string) $f)
                @php($isNum = $s !== '' && preg_match('/^[\d\s\.\,\-]+$/', $s) && preg_match('/\d/', $s))
                <th @if($isNum) class="num" @endif @if($i === 0) style="text-align:right;" @endif>{{ $f }}</th>
                @endforeach
            </tr>
        </tfoot>
        @endif
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
