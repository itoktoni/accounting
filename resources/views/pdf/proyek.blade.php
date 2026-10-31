<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1f2937; margin: 0; padding: 0; }
        .header { background: #1e3a8a; color: #fff; padding: 24px 32px; }
        .header .co { font-size: 13px; font-weight: bold; }
        .header .co-sub { font-size: 10px; opacity: .85; }
        .header h1 { margin: 6px 0 0; font-size: 20px; letter-spacing: 1px; }
        .doc-title { background: #eff6ff; border-bottom: 3px solid #1e3a8a; padding: 12px 32px; }
        .doc-title h2 { margin: 0; font-size: 16px; color: #1e3a8a; }
        .body { padding: 16px 32px; }
        table.info { width: 100%; margin-bottom: 10px; font-size: 11px; }
        table.info td { padding: 2px 4px; vertical-align: top; }
        .lbl { color: #6b7280; width: 150px; }
        h3.sec { font-size: 13px; color: #1e3a8a; border-bottom: 2px solid #1e3a8a; padding-bottom: 4px; margin: 16px 0 6px; }
        table.items { width: 100%; border-collapse: collapse; font-size: 11px; }
        table.items th { background: #1e3a8a; color: #fff; padding: 7px 6px; text-align: left; }
        table.items td { padding: 6px; border-bottom: 1px solid #e5e7eb; }
        table.items tr:nth-child(even) td { background: #f8fafc; }
        .num { text-align: right; white-space: nowrap; }
        .totals { width: 55%; margin-left: auto; margin-top: 8px; font-size: 11px; }
        .totals td { padding: 4px 6px; }
        .totals tr.grand td { background: #1e3a8a; color: #fff; font-weight: bold; font-size: 13px; }
        .totals tr.profit td { background: #ecfdf5; font-weight: bold; font-size: 12px; }
        .sign { width: 100%; margin-top: 36px; font-size: 11px; }
        .sign td { text-align: center; }
        .footer { text-align: center; font-size: 9px; color: #9ca3af; margin-top: 24px; border-top: 1px solid #e5e7eb; padding-top: 8px; }
    </style>
</head>
<body>
    @php
        $totBiaya = $estimasis->sum('estimasi_subtotal');
        $totMasuk = $estimasiMasuks->sum('estimasi_masuk_subtotal');
        $laba = $totMasuk - $totBiaya;
        $realBiaya = $jurnals->sum('total_debit');
    @endphp
    <div class="header">
        <div class="co">{{ $co['name'] ?? config('app.name') }}</div>
        <div class="co-sub">{{ $co['alamat'] ?? '' }}</div>
        <h1>LAPORAN PROYEK</h1>
    </div>
    <div class="doc-title"><h2>{{ $proyek->proyek_kode }} - {{ $proyek->proyek_nama }}</h2></div>
    <div class="body">
        <table class="info">
            <tr><td class="lbl">Pelanggan</td><td>: {{ $proyek->proyek_pelanggan ?: '-' }}</td><td class="lbl">Nilai Kontrak</td><td>: Rp {{ number_format($proyek->proyek_nilai_kontrak, 0, ',', '.') }}</td></tr>
            <tr><td class="lbl">Periode</td><td>: {{ $proyek->proyek_tanggal_mulai ? formatDate($proyek->proyek_tanggal_mulai) : '-' }} s/d {{ $proyek->proyek_target_selesai ? formatDate($proyek->proyek_target_selesai) : '-' }}</td><td class="lbl">Anggaran (Plafon)</td><td>: Rp {{ number_format($proyek->proyek_anggaran_biaya, 0, ',', '.') }}</td></tr>
            <tr><td class="lbl">Status</td><td>: {{ $proyek->proyek_status }}</td><td class="lbl">Realisasi Biaya</td><td>: Rp {{ number_format($realBiaya, 0, ',', '.') }}</td></tr>
        </table>

        <h3 class="sec">Estimasi Pendapatan</h3>
        <table class="items">
            <thead><tr><th>No</th><th>Deskripsi</th><th class="num">Qty</th><th>Satuan</th><th class="num">Harga</th><th class="num">Subtotal</th></tr></thead>
            <tbody>
                @forelse($estimasiMasuks as $i => $e)
                <tr><td>{{ $i + 1 }}</td><td>{{ $e->estimasi_masuk_deskripsi }}</td><td class="num">{{ rtrim(rtrim(number_format($e->estimasi_masuk_qty, 2), '0'), '.') }}</td><td>{{ $e->estimasi_masuk_satuan }}</td><td class="num">{{ number_format($e->estimasi_masuk_harga, 0, ',', '.') }}</td><td class="num">{{ number_format($e->estimasi_masuk_subtotal, 0, ',', '.') }}</td></tr>
                @empty
                <tr><td colspan="6" style="text-align:center">Belum ada estimasi pendapatan.</td></tr>
                @endforelse
            </tbody>
        </table>

        <h3 class="sec">Estimasi Pengeluaran</h3>
        <table class="items">
            <thead><tr><th>No</th><th>Deskripsi</th><th class="num">Qty</th><th>Satuan</th><th class="num">Harga</th><th class="num">Subtotal</th></tr></thead>
            <tbody>
                @forelse($estimasis as $i => $e)
                <tr><td>{{ $i + 1 }}</td><td>{{ $e->estimasi_deskripsi }}</td><td class="num">{{ rtrim(rtrim(number_format($e->estimasi_qty, 2), '0'), '.') }}</td><td>{{ $e->estimasi_satuan }}</td><td class="num">{{ number_format($e->estimasi_harga, 0, ',', '.') }}</td><td class="num">{{ number_format($e->estimasi_subtotal, 0, ',', '.') }}</td></tr>
                @empty
                <tr><td colspan="6" style="text-align:center">Belum ada estimasi pengeluaran.</td></tr>
                @endforelse
            </tbody>
        </table>
        <table class="totals">
            <tr><td>Total Estimasi Pendapatan</td><td class="num">{{ number_format($totMasuk, 0, ',', '.') }}</td></tr>
            <tr><td>Total Estimasi Pengeluaran</td><td class="num">({{ number_format($totBiaya, 0, ',', '.') }})</td></tr>
            <tr class="profit"><td>Estimasi Laba (Untung)</td><td class="num">Rp {{ number_format($laba, 0, ',', '.') }}</td></tr>
            <tr class="grand"><td>Margin</td><td class="num">{{ $totMasuk > 0 ? round($laba / $totMasuk * 100, 1).'%' : '-' }}</td></tr>
        </table>

        <h3 class="sec">Rincian Biaya per Akun (Realisasi)</h3>
        <table class="items">
            <thead><tr><th>Akun</th><th class="num">Debit</th><th class="num">Kredit</th></tr></thead>
            <tbody>
                @forelse($rincian as $r)
                <tr><td>{{ $r->akun_kode }} - {{ $r->akun_nama }}</td><td class="num">{{ number_format($r->debit, 0, ',', '.') }}</td><td class="num">{{ number_format($r->kredit, 0, ',', '.') }}</td></tr>
                @empty
                <tr><td colspan="3" style="text-align:center">Belum ada transaksi proyek.</td></tr>
                @endforelse
            </tbody>
        </table>

        <h3 class="sec">Transaksi Proyek</h3>
        <table class="items">
            <thead><tr><th>Tanggal</th><th>Nomor</th><th>Keterangan</th><th class="num">Debit</th><th class="num">Kredit</th></tr></thead>
            <tbody>
                @forelse($jurnals as $j)
                <tr><td>{{ formatDate($j->jurnal_tanggal) }}</td><td>{{ $j->jurnal_nomor_tx }}</td><td>{{ $j->jurnal_keterangan }}</td><td class="num">{{ number_format($j->total_debit, 0, ',', '.') }}</td><td class="num">{{ number_format($j->total_kredit, 0, ',', '.') }}</td></tr>
                @empty
                <tr><td colspan="5" style="text-align:center">Belum ada transaksi proyek.</td></tr>
                @endforelse
            </tbody>
        </table>

        <table class="sign">
            <tr>
                <td>Hormat kami,<br /><br /><br /><br /><br />( ______________________ )</td>
                <td>Menyetujui,<br /><br /><br /><br /><br />( ______________________ )</td>
            </tr>
        </table>
        <div class="footer">Dicetak pada {{ date('d/m/Y H:i') }}</div>
    </div>
</body>
</html>
