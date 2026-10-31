<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Http\Controllers\Controller;
use App\Models\Akun;
use App\Models\Jurnal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// shortcut: pakai Request (bukan GeneralRequest) agar halaman laporan read-only
// tidak terblokir authorize() policy; upgrade ke policy khusus bila perlu batasi peran.
class LaporanController extends Controller
{
    use ControllerTrait;

    public function __construct(Jurnal $model)
    {
        $this->model = $model::getModel();
    }

    private function periode(Request $request): array
    {
        return [
            'tahun' => (int) $request->input('tahun', date('Y')),
            'bulan' => $request->input('bulan') ? (int) $request->input('bulan') : null,
        ];
    }

    private function saldoAkun(int $tahun, ?int $bulan)
    {
        $q = DB::table('detail_jurnal as dj')
            ->join('jurnal as j', 'j.jurnal_id', '=', 'dj.detail_jurnal_id')
            ->join('akun as a', 'a.akun_id', '=', 'dj.detail_akun_id')
            ->whereYear('j.jurnal_tanggal', $tahun);

        if ($bulan) {
            $q->whereMonth('j.jurnal_tanggal', $bulan);
        }

        return $q->select('a.akun_id', 'a.akun_kode', 'a.akun_nama', 'a.akun_tipe', 'a.akun_subtipe', 'a.akun_saldo_normal',
                DB::raw('SUM(dj.detail_debit) as total_debit'), DB::raw('SUM(dj.detail_kredit) as total_kredit'))
            ->groupBy('a.akun_id', 'a.akun_kode', 'a.akun_nama', 'a.akun_tipe', 'a.akun_subtipe', 'a.akun_saldo_normal')
            ->orderBy('a.akun_kode')
            ->get()
            ->map(function ($r) {
                $d = (float) $r->total_debit;
                $k = (float) $r->total_kredit;
                $r->saldo = $r->akun_saldo_normal === 'DEBIT' ? $d - $k : $k - $d;

                return $r;
            });
    }

    private function labaBerjalan(int $tahun, ?int $bulan): float
    {
        $rows = $this->saldoAkun($tahun, $bulan);
        $pendapatan = (float) $rows->where('akun_tipe', 'PENDAPATAN')->sum('saldo');
        $beban = (float) $rows->where('akun_tipe', 'BEBAN')->sum('saldo');

        return round($pendapatan - $beban, 2);
    }

    // ---- Helper lama (pola Kledo) dipertahankan bila perlu, saat ini tak dipakai ----

    private function period($q, Request $r, string $col)
    {
        $q->whereYear($col, (int) $r->input('tahun', date('Y')));
        if ($r->input('bulan')) {
            $q->whereMonth($col, (int) $r->input('bulan'));
        }

        return $q;
    }

    private function rp($n): string
    {
        return formatAngka((int) round((float) $n));
    }

    // ---- Shared BAC helpers: filter → print viewer (tab baru) + excel ----
    protected function filterView(string $judul, string $print, string $excel, array $dropdowns = [])
    {
        return $this->views('pages.laporan.filter', [
            'model' => $this->model, 'judul' => $judul,
            'printRoute' => $print, 'excelRoute' => $excel, 'dropdowns' => $dropdowns,
        ]);
    }

    protected function periodeLabel(Request $r): string
    {
        $label = 'Tahun '.(int) $r->input('tahun', date('Y'));
        if ($r->input('bulan')) {
            $label .= ' Bulan '.$r->input('bulan');
        }
        foreach ($r->except(['tahun', 'bulan', 'page']) as $key => $val) {
            if ($val === null || $val === '') {
                continue;
            }
            if ($key === 'akun_id' && is_numeric($val)) {
                $akun = Akun::find($val);
                $val = $akun ? $akun->akun_kode.' - '.$akun->akun_nama : $val;
                $key = 'akun';
            }
            $label .= ' · '.ucwords(str_replace('_', ' ', $key)).' : '.$val;
        }

        return $label;
    }

    protected function printResponse(string $judul, string $periode, array $headers, $rows, array $foot, string $excelUrl)
    {
        return response()->view('pages.laporan.print', [
            'judul' => $judul, 'periode' => $periode, 'headers' => $headers,
            'rows' => $rows, 'foot' => $foot, 'excelUrl' => $excelUrl,
        ]);
    }

    protected function excelResponse(string $filename, string $judul, string $periode, array $headers, $rows, array $foot = [])
    {
        return response()->streamDownload(function () use ($judul, $periode, $headers, $rows, $foot) {
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
            echo '<head><meta charset="UTF-8"></head><body>';
            echo '<table><tr><td colspan="'.count($headers).'"><b>'.e(strtoupper($judul)).'</b><br><b>Periode : '.e($periode).'</b></td></tr></table><br>';
            echo '<table border="1"><thead><tr><th>No.</th>';
            foreach ($headers as $h) {
                echo '<th>'.e($h).'</th>';
            }
            echo '</tr></thead><tbody>';
            $no = 0;
            $emit = function ($r) use (&$no) {
                $no++;
                $cells = is_array($r) ? array_values($r) : array_values((array) $r);
                echo '<tr><td>'.$no.'</td>';
                foreach ($cells as $c) {
                    echo '<td>'.e((string) $c).'</td>';
                }
                echo '</tr>';
            };
            if ($rows instanceof \Illuminate\Database\Query\Builder || $rows instanceof \Illuminate\Database\Eloquent\Builder) {
                $rows->chunk(1000, function ($chunk) use ($emit) {
                    foreach ($chunk as $r) {
                        $emit($r);
                    }
                    flush();
                });
            } else {
                foreach ($rows as $r) {
                    $emit($r);
                }
            }
            echo '</tbody>';
            if (! empty($foot)) {
                echo '<tfoot><tr><th></th>';
                foreach ($foot as $f) {
                    echo '<th>'.e((string) $f).'</th>';
                }
                echo '</tr></tfoot>';
            }
            echo '</table></body></html>';
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    private function sisaPiutang(): \Illuminate\Support\Collection
    {
        return DB::table('piutang')
            ->select('piutang_pelanggan', DB::raw('SUM(piutang_jumlah) as total'), DB::raw('SUM(piutang_terbayar) as bayar'))
            ->groupBy('piutang_pelanggan')->get()
            ->map(fn ($r) => (object) ['kontak' => $r->piutang_pelanggan, 'sisa' => (float) $r->total - (float) $r->bayar]);
    }

    private function sisaHutang(): \Illuminate\Support\Collection
    {
        return DB::table('hutang')
            ->select('hutang_pemasok', DB::raw('SUM(hutang_jumlah) as total'), DB::raw('SUM(hutang_terbayar) as bayar'))
            ->groupBy('hutang_pemasok')->get()
            ->map(fn ($r) => (object) ['kontak' => $r->hutang_pemasok, 'sisa' => (float) $r->total - (float) $r->bayar]);
    }

    // ---- Data builders (dipakai Print + Excel) ----
    private function neracaData(int $tahun, ?int $bulan): array
    {
        $rows = $this->saldoAkun($tahun, $bulan);
        $grup = $rows->groupBy('akun_tipe');
        $total = fn ($tipe) => round((float) ($grup->get($tipe, collect())->sum('saldo')), 2);
        $totalAset = $total('ASET');
        $laba = $this->labaBerjalan($tahun, $bulan);
        $totalPasiva = round($total('LIABILITAS') + $total('EKUITAS') + $laba, 2);
        $out = [];
        foreach (['ASET', 'LIABILITAS', 'EKUITAS'] as $tipe) {
            $out[] = [$tipe, ''];
            foreach ($grup->get($tipe, collect()) as $r) {
                $out[] = [$r->akun_kode.' - '.$r->akun_nama, $this->rp($r->saldo)];
            }
            $out[] = ['Subtotal '.$tipe, $this->rp($total($tipe))];
        }
        $out[] = ['Laba berjalan', $this->rp($laba)];
        $out[] = ['Total Aset', $this->rp($totalAset)];
        $foot = ['Total Pasiva', $this->rp($totalPasiva)];

        return [['Akun', 'Saldo'], $out, $foot];
    }

    private function labaRugiData(int $tahun, ?int $bulan): array
    {
        $rows = $this->saldoAkun($tahun, $bulan);
        $pendapatan = $rows->where('akun_tipe', 'PENDAPATAN')->groupBy('akun_subtipe');
        $beban = $rows->where('akun_tipe', 'BEBAN')->groupBy('akun_subtipe');
        $totalPendapatan = round((float) $rows->where('akun_tipe', 'PENDAPATAN')->sum('saldo'), 2);
        $totalBeban = round((float) $rows->where('akun_tipe', 'BEBAN')->sum('saldo'), 2);
        $out = [];
        $out[] = ['PENDAPATAN', ''];
        foreach ($pendapatan as $sub => $list) {
            $out[] = ['-- '.($sub ?: 'Lainnya'), ''];
            foreach ($list as $r) {
                $out[] = [$r->akun_kode.' - '.$r->akun_nama, $this->rp($r->saldo)];
            }
        }
        $out[] = ['Total Pendapatan', $this->rp($totalPendapatan)];
        $out[] = ['BEBAN', ''];
        foreach ($beban as $sub => $list) {
            $out[] = ['-- '.($sub ?: 'Lainnya'), ''];
            foreach ($list as $r) {
                $out[] = [$r->akun_kode.' - '.$r->akun_nama, $this->rp($r->saldo)];
            }
        }
        $out[] = ['Total Beban', $this->rp($totalBeban)];
        $foot = ['Laba / (Rugi)', $this->rp($totalPendapatan - $totalBeban)];

        return [['Akun', 'Nominal'], $out, $foot];
    }

    private function arusKasData(int $tahun, ?int $bulan): array
    {
        $rows = DB::table('detail_jurnal as dj')
            ->join('jurnal as j', 'j.jurnal_id', '=', 'dj.detail_jurnal_id')
            ->join('akun as a', 'a.akun_id', '=', 'dj.detail_akun_id')
            ->whereYear('j.jurnal_tanggal', $tahun)
            ->when($bulan, fn ($q) => $q->whereMonth('j.jurnal_tanggal', $bulan))
            ->where(function ($q) {
                $q->where('a.akun_is_rekening', true)->orWhereIn('a.akun_kode', ['1100', '1110']);
            })
            ->select('j.jurnal_kategori', DB::raw('SUM(dj.detail_debit) as masuk'), DB::raw('SUM(dj.detail_kredit) as keluar'))
            ->groupBy('j.jurnal_kategori')
            ->orderBy('j.jurnal_kategori')
            ->get();
        $totalMasuk = round((float) $rows->sum('masuk'), 2);
        $totalKeluar = round((float) $rows->sum('keluar'), 2);
        $out = $rows->map(fn ($r) => [($r->jurnal_kategori ?: '-'), $this->rp($r->masuk), $this->rp($r->keluar)])->toArray();
        $out[] = ['Selisih', $this->rp($totalMasuk - $totalKeluar), ''];
        $foot = ['Total', $this->rp($totalMasuk), $this->rp($totalKeluar)];

        return [['Kategori', 'Masuk', 'Keluar'], $out, $foot];
    }

    private function bukuBesarData(int $tahun, ?int $bulan, ?int $akunId): array
    {
        $mutasi = DB::table('detail_jurnal as dj')
            ->join('jurnal as j', 'j.jurnal_id', '=', 'dj.detail_jurnal_id')
            ->join('akun as a', 'a.akun_id', '=', 'dj.detail_akun_id')
            ->whereYear('j.jurnal_tanggal', $tahun)
            ->when($bulan, fn ($q) => $q->whereMonth('j.jurnal_tanggal', $bulan))
            ->when($akunId, fn ($q) => $q->where('a.akun_id', $akunId))
            ->select('a.akun_id', 'a.akun_kode', 'a.akun_nama', 'a.akun_saldo_normal', 'j.jurnal_tanggal', 'j.jurnal_keterangan', 'j.jurnal_nomor_tx', 'dj.detail_debit', 'dj.detail_kredit')
            ->orderBy('a.akun_kode')->orderBy('j.jurnal_tanggal')
            ->get()
            ->groupBy('akun_id');
        $out = [];
        foreach ($mutasi as $list) {
            $first = $list->first();
            $out[] = [$first->akun_kode.' - '.$first->akun_nama, '', '', '', '', ''];
            foreach ($list as $m) {
                $out[] = ['', $m->jurnal_tanggal, $m->jurnal_nomor_tx, $m->jurnal_keterangan, $this->rp($m->detail_debit), $this->rp($m->detail_kredit)];
            }
        }

        return [['Akun', 'Tanggal', 'Nomor', 'Keterangan', 'Debit', 'Kredit'], $out, []];
    }

    private function akunOptions(): array
    {
        return Akun::orderBy('akun_kode')->get()->mapWithKeys(fn ($a) => [$a->akun_id => $a->akun_kode.' - '.$a->akun_nama])->toArray();
    }

    private function jurnalQuery(Request $request)
    {
        return $this->period(DB::table('detail_jurnal as dj')
            ->join('jurnal as j', 'j.jurnal_id', '=', 'dj.detail_jurnal_id')
            ->join('akun as a', 'a.akun_id', '=', 'dj.detail_akun_id'), $request, 'j.jurnal_tanggal')
            ->select('j.jurnal_tanggal', 'j.jurnal_nomor_tx', 'j.jurnal_keterangan', 'a.akun_kode', 'dj.detail_debit', 'dj.detail_kredit')
            ->orderBy('j.jurnal_tanggal')->orderBy('j.jurnal_id');
    }

    private function jurnalRows($rows): array
    {
        return collect($rows)->map(fn ($r) => [$r->jurnal_tanggal, $r->jurnal_nomor_tx, $r->jurnal_keterangan, $r->akun_kode, $this->rp($r->detail_debit), $this->rp($r->detail_kredit)])->toArray();
    }

    private function proyeksiArusKasData(): array
    {
        $kas = (float) DB::table('detail_jurnal as dj')
            ->join('akun as a', 'a.akun_id', '=', 'dj.detail_akun_id')
            ->whereIn('a.akun_kode', ['1100', '1110'])
            ->selectRaw('SUM(dj.detail_debit) - SUM(dj.detail_kredit) as s')->value('s');
        $masuk = (float) DB::table('piutang')->selectRaw('SUM(piutang_jumlah - piutang_terbayar) as s')->value('s');
        $keluar = (float) DB::table('hutang')->selectRaw('SUM(hutang_jumlah - hutang_terbayar) as s')->value('s');
        $rows = [
            ['Kas saat ini', $this->rp($kas)],
            ['Piutang jatuh tempo (arus masuk)', $this->rp($masuk)],
            ['Hutang jatuh tempo (arus keluar)', $this->rp($keluar)],
        ];
        $foot = ['Proyeksi kas akhir', $this->rp($kas + $masuk - $keluar)];

        return [['Uraian', 'Nominal'], $rows, $foot];
    }

    private function perubahanModalData(int $tahun, ?int $bulan): array
    {
        $rows = $this->saldoAkun($tahun, $bulan)->where('akun_tipe', 'EKUITAS');
        $laba = $this->labaBerjalan($tahun, $bulan);
        $out = $rows->map(fn ($r) => [$r->akun_kode.' - '.$r->akun_nama, $this->rp($r->saldo)])->values()->toArray();
        $out[] = ['Laba berjalan', $this->rp($laba)];
        $foot = ['Total Ekuitas', $this->rp($rows->sum('saldo') + $laba)];

        return [['Akun', 'Saldo'], $out, $foot];
    }

    private function ringkasanEksekutifData(int $tahun, ?int $bulan): array
    {
        $rows = $this->saldoAkun($tahun, $bulan);
        $total = fn ($t) => (float) $rows->where('akun_tipe', $t)->sum('saldo');
        $data = [
            ['Total Aset', $this->rp($total('ASET'))],
            ['Total Liabilitas', $this->rp($total('LIABILITAS'))],
            ['Total Ekuitas', $this->rp($total('EKUITAS'))],
            ['Pendapatan', $this->rp($total('PENDAPATAN'))],
            ['Beban', $this->rp($total('BEBAN'))],
            ['Laba / (Rugi)', $this->rp($total('PENDAPATAN') - $total('BEBAN'))],
            ['Piutang outstanding', $this->rp($this->sisaPiutang()->sum('sisa'))],
            ['Hutang outstanding', $this->rp($this->sisaHutang()->sum('sisa'))],
        ];

        return [['Indikator', 'Nilai'], $data, []];
    }

    private function hutangPiutangKontakData(): array
    {
        $rows = $this->sisaPiutang()->map(fn ($r) => (object) ['kontak' => $r->kontak, 'tipe' => 'Piutang', 'sisa' => $r->sisa])
            ->merge($this->sisaHutang()->map(fn ($r) => (object) ['kontak' => $r->kontak, 'tipe' => 'Hutang', 'sisa' => $r->sisa]))
            ->sortBy('kontak')->values();
        $out = $rows->map(fn ($r) => [$r->kontak, $r->tipe, $this->rp($r->sisa)])->toArray();

        return [['Kontak', 'Tipe', 'Sisa'], $out, []];
    }

    private function ringkasanBankData(): array
    {
        $rows = DB::table('detail_jurnal as dj')
            ->join('akun as a', 'a.akun_id', '=', 'dj.detail_akun_id')
            ->where(fn ($q) => $q->where('a.akun_is_rekening', true)->orWhereIn('a.akun_kode', ['1100', '1110']))
            ->select('a.akun_kode', 'a.akun_nama', DB::raw('SUM(dj.detail_debit) - SUM(dj.detail_kredit) as saldo'))
            ->groupBy('a.akun_kode', 'a.akun_nama')->orderBy('a.akun_kode')->get();
        $out = $rows->map(fn ($r) => [$r->akun_kode.' - '.$r->akun_nama, $this->rp($r->saldo)])->toArray();
        $foot = ['Total Kas & Bank', $this->rp($rows->sum('saldo'))];

        return [['Rekening', 'Saldo'], $out, $foot];
    }

    private function bukuBesarPembantuData(): array
    {
        $rows = $this->sisaPiutang()->map(fn ($r) => [$r->kontak, 'Piutang', $this->rp($r->sisa)])
            ->merge($this->sisaHutang()->map(fn ($r) => [$r->kontak, 'Hutang', $this->rp($r->sisa)]))
            ->sortBy(fn ($r) => $r[0].$r[1])->values()->toArray();

        return [['Kontak', 'Buku', 'Sisa'], $rows, []];
    }

    private function trialBalanceData(int $tahun, ?int $bulan): array
    {
        $rows = $this->saldoAkun($tahun, $bulan);
        $out = [];
        $td = $tk = 0;
        foreach ($rows as $r) {
            $d = (float) $r->total_debit;
            $k = (float) $r->total_kredit;
            $td += $d;
            $tk += $k;
            $out[] = [$r->akun_kode.' - '.$r->akun_nama, $this->rp($d), $this->rp($k)];
        }
        $foot = ['Total', $this->rp($td), $this->rp($tk)];

        return [['Akun', 'Debit', 'Kredit'], $out, $foot];
    }

    // ---- Keuangan (BAC: filter → print viewer + excel) ----
    public function getNeraca(Request $request)
    {
        return $this->filterView('Neraca', 'laporan.getNeracaPrint', 'laporan.getNeracaExcel');
    }

    public function getNeracaPrint(Request $request)
    {
        ['tahun' => $tahun, 'bulan' => $bulan] = $this->periode($request);
        [$headers, $rows, $foot] = $this->neracaData($tahun, $bulan);

        return $this->printResponse('Neraca', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getNeracaExcel', $request->query()));
    }

    public function getNeracaExcel(Request $request)
    {
        ['tahun' => $tahun, 'bulan' => $bulan] = $this->periode($request);
        [$headers, $rows, $foot] = $this->neracaData($tahun, $bulan);

        return $this->excelResponse('neraca-'.now()->format('Ymd-His').'.xls', 'Neraca', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    public function getLabaRugi(Request $request)
    {
        return $this->filterView('Laba Rugi', 'laporan.getLabaRugiPrint', 'laporan.getLabaRugiExcel');
    }

    public function getLabaRugiPrint(Request $request)
    {
        ['tahun' => $tahun, 'bulan' => $bulan] = $this->periode($request);
        [$headers, $rows, $foot] = $this->labaRugiData($tahun, $bulan);

        return $this->printResponse('Laba Rugi', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getLabaRugiExcel', $request->query()));
    }

    public function getLabaRugiExcel(Request $request)
    {
        ['tahun' => $tahun, 'bulan' => $bulan] = $this->periode($request);
        [$headers, $rows, $foot] = $this->labaRugiData($tahun, $bulan);

        return $this->excelResponse('laba-rugi-'.now()->format('Ymd-His').'.xls', 'Laba Rugi', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    public function getBukuBesar(Request $request)
    {
        return $this->filterView('Buku Besar', 'laporan.getBukuBesarPrint', 'laporan.getBukuBesarExcel', [
            ['name' => 'akun_id', 'label' => 'Akun (opsional)', 'options' => $this->akunOptions()],
        ]);
    }

    public function getBukuBesarPrint(Request $request)
    {
        ['tahun' => $tahun, 'bulan' => $bulan] = $this->periode($request);
        $akunId = $request->input('akun_id') ? (int) $request->input('akun_id') : null;
        [$headers, $rows, $foot] = $this->bukuBesarData($tahun, $bulan, $akunId);

        return $this->printResponse('Buku Besar', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getBukuBesarExcel', $request->query()));
    }

    public function getBukuBesarExcel(Request $request)
    {
        ['tahun' => $tahun, 'bulan' => $bulan] = $this->periode($request);
        $akunId = $request->input('akun_id') ? (int) $request->input('akun_id') : null;
        [$headers, $rows, $foot] = $this->bukuBesarData($tahun, $bulan, $akunId);

        return $this->excelResponse('buku-besar-'.now()->format('Ymd-His').'.xls', 'Buku Besar', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    public function getArusKas(Request $request)
    {
        return $this->filterView('Arus Kas', 'laporan.getArusKasPrint', 'laporan.getArusKasExcel');
    }

    public function getArusKasPrint(Request $request)
    {
        ['tahun' => $tahun, 'bulan' => $bulan] = $this->periode($request);
        [$headers, $rows, $foot] = $this->arusKasData($tahun, $bulan);

        return $this->printResponse('Arus Kas', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getArusKasExcel', $request->query()));
    }

    public function getArusKasExcel(Request $request)
    {
        ['tahun' => $tahun, 'bulan' => $bulan] = $this->periode($request);
        [$headers, $rows, $foot] = $this->arusKasData($tahun, $bulan);

        return $this->excelResponse('arus-kas-'.now()->format('Ymd-His').'.xls', 'Arus Kas', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    public function getProyeksiArusKas(Request $request)
    {
        return $this->filterView('Proyeksi Arus Kas', 'laporan.getProyeksiArusKasPrint', 'laporan.getProyeksiArusKasExcel');
    }

    public function getProyeksiArusKasPrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->proyeksiArusKasData();

        return $this->printResponse('Proyeksi Arus Kas', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getProyeksiArusKasExcel', $request->query()));
    }

    public function getProyeksiArusKasExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->proyeksiArusKasData();

        return $this->excelResponse('proyeksi-arus-kas-'.now()->format('Ymd-His').'.xls', 'Proyeksi Arus Kas', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    public function getPerubahanModal(Request $request)
    {
        return $this->filterView('Perubahan Modal', 'laporan.getPerubahanModalPrint', 'laporan.getPerubahanModalExcel');
    }

    public function getPerubahanModalPrint(Request $request)
    {
        ['tahun' => $tahun, 'bulan' => $bulan] = $this->periode($request);
        [$headers, $rows, $foot] = $this->perubahanModalData($tahun, $bulan);

        return $this->printResponse('Perubahan Modal', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getPerubahanModalExcel', $request->query()));
    }

    public function getPerubahanModalExcel(Request $request)
    {
        ['tahun' => $tahun, 'bulan' => $bulan] = $this->periode($request);
        [$headers, $rows, $foot] = $this->perubahanModalData($tahun, $bulan);

        return $this->excelResponse('perubahan-modal-'.now()->format('Ymd-His').'.xls', 'Perubahan Modal', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    public function getRingkasanEksekutif(Request $request)
    {
        return $this->filterView('Ringkasan Eksekutif', 'laporan.getRingkasanEksekutifPrint', 'laporan.getRingkasanEksekutifExcel');
    }

    public function getRingkasanEksekutifPrint(Request $request)
    {
        ['tahun' => $tahun, 'bulan' => $bulan] = $this->periode($request);
        [$headers, $rows, $foot] = $this->ringkasanEksekutifData($tahun, $bulan);

        return $this->printResponse('Ringkasan Eksekutif', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getRingkasanEksekutifExcel', $request->query()));
    }

    public function getRingkasanEksekutifExcel(Request $request)
    {
        ['tahun' => $tahun, 'bulan' => $bulan] = $this->periode($request);
        [$headers, $rows, $foot] = $this->ringkasanEksekutifData($tahun, $bulan);

        return $this->excelResponse('ringkasan-eksekutif-'.now()->format('Ymd-His').'.xls', 'Ringkasan Eksekutif', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    public function getHutangPiutangKontak(Request $request)
    {
        return $this->filterView('Hutang & Piutang per Kontak', 'laporan.getHutangPiutangKontakPrint', 'laporan.getHutangPiutangKontakExcel');
    }

    public function getHutangPiutangKontakPrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->hutangPiutangKontakData();

        return $this->printResponse('Hutang & Piutang per Kontak', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getHutangPiutangKontakExcel', $request->query()));
    }

    public function getHutangPiutangKontakExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->hutangPiutangKontakData();

        return $this->excelResponse('hutang-piutang-kontak-'.now()->format('Ymd-His').'.xls', 'Hutang & Piutang per Kontak', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    public function getRingkasanBank(Request $request)
    {
        return $this->filterView('Ringkasan Bank', 'laporan.getRingkasanBankPrint', 'laporan.getRingkasanBankExcel');
    }

    public function getRingkasanBankPrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->ringkasanBankData();

        return $this->printResponse('Ringkasan Bank', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getRingkasanBankExcel', $request->query()));
    }

    public function getRingkasanBankExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->ringkasanBankData();

        return $this->excelResponse('ringkasan-bank-'.now()->format('Ymd-His').'.xls', 'Ringkasan Bank', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    public function getBukuBesarPembantu(Request $request)
    {
        return $this->filterView('Buku Besar Pembantu', 'laporan.getBukuBesarPembantuPrint', 'laporan.getBukuBesarPembantuExcel');
    }

    public function getBukuBesarPembantuPrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->bukuBesarPembantuData();

        return $this->printResponse('Buku Besar Pembantu', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getBukuBesarPembantuExcel', $request->query()));
    }

    public function getBukuBesarPembantuExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->bukuBesarPembantuData();

        return $this->excelResponse('buku-besar-pembantu-'.now()->format('Ymd-His').'.xls', 'Buku Besar Pembantu', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    public function getJurnal(Request $request)
    {
        return $this->filterView('Jurnal', 'laporan.getJurnalPrint', 'laporan.getJurnalExcel');
    }

    public function getJurnalPrint(Request $request)
    {
        $headers = ['Tanggal', 'Nomor', 'Keterangan', 'Akun', 'Debit', 'Kredit'];
        $rows = $this->jurnalRows($this->jurnalQuery($request)->limit(5000)->get());

        return $this->printResponse('Jurnal', $this->periodeLabel($request), $headers, $rows, [], route('laporan.getJurnalExcel', $request->query()));
    }

    public function getJurnalExcel(Request $request)
    {
        $headers = ['Tanggal', 'Nomor', 'Keterangan', 'Akun', 'Debit', 'Kredit'];

        return $this->excelResponse('jurnal-'.now()->format('Ymd-His').'.xls', 'Jurnal', $this->periodeLabel($request), $headers, $this->jurnalQuery($request));
    }

    public function getTrialBalance(Request $request)
    {
        return $this->filterView('Trial Balance', 'laporan.getTrialBalancePrint', 'laporan.getTrialBalanceExcel');
    }

    public function getTrialBalancePrint(Request $request)
    {
        ['tahun' => $tahun, 'bulan' => $bulan] = $this->periode($request);
        [$headers, $rows, $foot] = $this->trialBalanceData($tahun, $bulan);

        return $this->printResponse('Trial Balance', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getTrialBalanceExcel', $request->query()));
    }

    public function getTrialBalanceExcel(Request $request)
    {
        ['tahun' => $tahun, 'bulan' => $bulan] = $this->periode($request);
        [$headers, $rows, $foot] = $this->trialBalanceData($tahun, $bulan);

        return $this->excelResponse('trial-balance-'.now()->format('Ymd-His').'.xls', 'Trial Balance', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    // ---- Penjualan ----
    public function getPenjualan(Request $request)
    {
        // Form filter saja — hasil dibuka di getPenjualanPrint (tab baru).
        return $this->views('pages.laporan.penjualan_filter', [
            'model' => $this->model,
            'pelangganOptions' => \App\Models\Customer::orderBy('customer_nama')->pluck('customer_nama', 'customer_nama')->toArray(),
        ]);
    }

    private function penjualanQuery(Request $request)
    {
        return $this->period(DB::table('invoice as i')
            ->leftJoin('invoice_item as ii', 'ii.item_invoice_id', '=', 'i.invoice_id'), $request, 'i.invoice_tanggal')
            ->when($request->input('pelanggan'), fn ($q) => $q->where('i.invoice_pelanggan', $request->input('pelanggan')))
            ->select('i.invoice_nomor', 'i.invoice_tanggal', 'i.invoice_pelanggan', 'i.invoice_status', DB::raw('SUM(ii.item_subtotal) as total'))
            ->groupBy('i.invoice_nomor', 'i.invoice_tanggal', 'i.invoice_pelanggan', 'i.invoice_status')
            ->orderBy('i.invoice_tanggal');
    }

    private function pelangganOptions(): array
    {
        return \App\Models\Customer::orderBy('customer_nama')->pluck('customer_nama', 'customer_nama')->toArray();
    }

    public function getPenjualanPrint(Request $request)
    {
        $rows = $this->penjualanQuery($request)->limit(5000)->get();
        $total = (float) $rows->sum('total');

        return response()->view('pages.laporan.penjualan_print', [
            'title' => 'LAPORAN PENJUALAN',
            'rows' => $rows,
            'total' => $total,
            'tahun' => (int) $request->input('tahun', date('Y')),
            'bulan' => $request->input('bulan') ? (int) $request->input('bulan') : null,
            'pelanggan' => $request->input('pelanggan'),
            'query' => $request->query(),
        ]);
    }

    public function getPenjualanExcel(Request $request)
    {
        $filename = 'penjualan-'.now()->format('Ymd-His').'.xls';
        $query = $this->penjualanQuery($request);

        return response()->streamDownload(function () use ($query, $request) {
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
            echo '<head><meta charset="UTF-8"></head><body>';
            echo '<table><tr><td colspan="5"><b>LAPORAN PENJUALAN</b><br><b>Tahun : '.e($request->input('tahun', date('Y'))).($request->input('bulan') ? ' Bulan : '.e($request->input('bulan')) : '').($request->input('pelanggan') ? '<br><b>Pelanggan : '.e($request->input('pelanggan')) : '').'</b></td></tr></table><br>';
            echo '<table border="1"><thead><tr><th>No.</th><th>Tanggal</th><th>Nomor</th><th>Pelanggan</th><th>Total</th><th>Status</th></tr></thead><tbody>';
            $no = 0;
            $total = 0;
            $query->orderBy('i.invoice_tanggal')->chunk(1000, function ($rows) use (&$no, &$total) {
                foreach ($rows as $r) {
                    $no++;
                    $total += (float) $r->total;
                    echo '<tr><td>'.$no.'</td><td>'.e($r->invoice_tanggal).'</td><td>'.e($r->invoice_nomor).'</td><td>'.e($r->invoice_pelanggan).'</td><td>'.$r->total.'</td><td>'.e($r->invoice_status).'</td></tr>';
                }
                flush();
            });
            echo '</tbody><tfoot><tr><th colspan="4">Total</th><th>'.$total.'</th><th>'.$no.' baris</th></tr></tfoot></table></body></html>';
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function getDetailPenjualan(Request $request)
    {
        return $this->filterView('Detail Penjualan', 'laporan.getDetailPenjualanPrint', 'laporan.getDetailPenjualanExcel', [
            ['name' => 'pelanggan', 'label' => 'Pelanggan', 'options' => $this->pelangganOptions()],
        ]);
    }

    public function getDetailPenjualanPrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->detailPenjualanData($request);

        return $this->printResponse('Detail Penjualan', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getDetailPenjualanExcel', $request->query()));
    }

    public function getDetailPenjualanExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->detailPenjualanData($request);

        return $this->excelResponse('detail-penjualan-'.now()->format('Ymd-His').'.xls', 'Detail Penjualan', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    private function detailPenjualanData(Request $request): array
    {
        $rows = $this->period(DB::table('invoice_item as ii')
            ->join('invoice as i', 'i.invoice_id', '=', 'ii.item_invoice_id'), $request, 'i.invoice_tanggal')
            ->when($request->input('pelanggan'), fn ($q) => $q->where('i.invoice_pelanggan', $request->input('pelanggan')))
            ->select('i.invoice_nomor', 'i.invoice_pelanggan', 'ii.item_deskripsi', 'ii.item_qty', 'ii.item_harga_satuan', 'ii.item_subtotal')
            ->orderBy('i.invoice_tanggal')->limit(500)->get()
            ->map(fn ($r) => [$r->invoice_nomor, $r->invoice_pelanggan, $r->item_deskripsi, $r->item_qty, $this->rp($r->item_harga_satuan), $this->rp($r->item_subtotal)])->toArray();

        return [['Invoice', 'Pelanggan', 'Item', 'Qty', 'Harga', 'Subtotal'], $rows, []];
    }

    public function getUmurPiutang(Request $request)
    {
        return $this->filterView('Umur Piutang', 'laporan.getUmurPiutangPrint', 'laporan.getUmurPiutangExcel', [
            ['name' => 'pelanggan', 'label' => 'Pelanggan', 'options' => $this->pelangganOptions()],
        ]);
    }

    public function getUmurPiutangPrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->umurPiutangData($request);

        return $this->printResponse('Umur Piutang', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getUmurPiutangExcel', $request->query()));
    }

    public function getUmurPiutangExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->umurPiutangData($request);

        return $this->excelResponse('umur-piutang-'.now()->format('Ymd-His').'.xls', 'Umur Piutang', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    private function umurPiutangData(Request $request): array
    {
        $rows = DB::table('piutang')
            ->when($request->input('pelanggan'), fn ($q) => $q->where('piutang_pelanggan', $request->input('pelanggan')))
            ->get()->map(function ($r) {
                $sisa = (float) $r->piutang_jumlah - (float) $r->piutang_terbayar;
                if ($sisa <= 0) {
                    return null;
                }
                $jt = $r->piutang_jatuh_tempo ?: $r->piutang_tanggal;
                $hari = (int) floor((strtotime(date('Y-m-d')) - strtotime($jt)) / 86400);
                $bucket = $hari <= 0 ? 'Belum jatuh tempo' : ($hari <= 30 ? '1-30 hari' : ($hari <= 60 ? '31-60 hari' : ($hari <= 90 ? '61-90 hari' : '> 90 hari')));

                return [$r->piutang_pelanggan, $r->piutang_tanggal, $jt, $bucket, $this->rp($sisa)];
            })->filter()->values()->toArray();

        return [['Pelanggan', 'Tanggal', 'Jatuh Tempo', 'Umur', 'Sisa'], $rows, []];
    }

    public function getPiutangPelanggan(Request $request)
    {
        return $this->filterView('Laporan Piutang Pelanggan', 'laporan.getPiutangPelangganPrint', 'laporan.getPiutangPelangganExcel', [
            ['name' => 'pelanggan', 'label' => 'Pelanggan', 'options' => $this->pelangganOptions()],
        ]);
    }

    public function getPiutangPelangganPrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->piutangPelangganData($request);

        return $this->printResponse('Laporan Piutang Pelanggan', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getPiutangPelangganExcel', $request->query()));
    }

    public function getPiutangPelangganExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->piutangPelangganData($request);

        return $this->excelResponse('piutang-pelanggan-'.now()->format('Ymd-His').'.xls', 'Laporan Piutang Pelanggan', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    private function piutangPelangganData(Request $request): array
    {
        $all = $this->sisaPiutang();
        if ($request->input('pelanggan')) {
            $all = $all->where('kontak', $request->input('pelanggan'))->values();
        }
        $out = $all->map(fn ($r) => [$r->kontak, $this->rp($r->sisa)])->values()->toArray();
        $foot = ['Total Piutang', $this->rp($all->sum('sisa'))];

        return [['Pelanggan', 'Sisa Piutang'], $out, $foot];
    }

    public function getSaldoPiutangTermin(Request $request)
    {
        return $this->filterView('Saldo Piutang Termin', 'laporan.getSaldoPiutangTerminPrint', 'laporan.getSaldoPiutangTerminExcel');
    }

    public function getSaldoPiutangTerminPrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->saldoPiutangTerminData();

        return $this->printResponse('Saldo Piutang Termin', $this->periodeLabel($request).' · Dikelompokkan per tanggal jatuh tempo (termin).', $headers, $rows, $foot, route('laporan.getSaldoPiutangTerminExcel', $request->query()));
    }

    public function getSaldoPiutangTerminExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->saldoPiutangTerminData();

        return $this->excelResponse('saldo-piutang-termin-'.now()->format('Ymd-His').'.xls', 'Saldo Piutang Termin', $this->periodeLabel($request).' · Dikelompokkan per tanggal jatuh tempo (termin).', $headers, $rows, $foot);
    }

    private function saldoPiutangTerminData(): array
    {
        $rows = DB::table('piutang')->select('piutang_jatuh_tempo', DB::raw('SUM(piutang_jumlah - piutang_terbayar) as sisa'))
            ->groupBy('piutang_jatuh_tempo')->orderBy('piutang_jatuh_tempo')->get()
            ->map(fn ($r) => [$r->piutang_jatuh_tempo ?: '-', $this->rp($r->sisa)])->toArray();

        return [['Jatuh Tempo', 'Sisa'], $rows, []];
    }

    public function getCreditMemo(Request $request)
    {
        return $this->filterView('Credit Memo', 'laporan.getCreditMemoPrint', 'laporan.getCreditMemoExcel');
    }

    public function getCreditMemoPrint(Request $request)
    {
        return $this->printResponse('Credit Memo', $this->periodeLabel($request).' · Belum ada modul credit memo — retur penjualan dicatat lewat jurnal tipe RETUR.', ['Nomor', 'Pelanggan', 'Tanggal', 'Nominal'], [], [], route('laporan.getCreditMemoExcel', $request->query()));
    }

    public function getCreditMemoExcel(Request $request)
    {
        return $this->excelResponse('credit-memo-'.now()->format('Ymd-His').'.xls', 'Credit Memo', $this->periodeLabel($request).' · Belum ada modul credit memo — retur penjualan dicatat lewat jurnal tipe RETUR.', ['Nomor', 'Pelanggan', 'Tanggal', 'Nominal'], [], []);
    }

    public function getTagihanPelanggan(Request $request)
    {
        return $this->filterView('Tagihan Pelanggan', 'laporan.getTagihanPelangganPrint', 'laporan.getTagihanPelangganExcel', [
            ['name' => 'pelanggan', 'label' => 'Pelanggan', 'options' => $this->pelangganOptions()],
        ]);
    }

    public function getTagihanPelangganPrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->tagihanPelangganData($request);

        return $this->printResponse('Tagihan Pelanggan', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getTagihanPelangganExcel', $request->query()));
    }

    public function getTagihanPelangganExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->tagihanPelangganData($request);

        return $this->excelResponse('tagihan-pelanggan-'.now()->format('Ymd-His').'.xls', 'Tagihan Pelanggan', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    private function tagihanPelangganData(Request $request): array
    {
        $rows = $this->period(DB::table('invoice'), $request, 'invoice_tanggal')
            ->when($request->input('pelanggan'), fn ($q) => $q->where('invoice_pelanggan', $request->input('pelanggan')))
            ->select('invoice_nomor', 'invoice_tanggal', 'invoice_jatuh_tempo', 'invoice_pelanggan', 'invoice_status')->orderBy('invoice_tanggal')->limit(500)->get();
        $out = [];
        foreach ($rows as $r) {
            $inv = DB::table('invoice')->where('invoice_nomor', $r->invoice_nomor)->first();
            $total = (float) DB::table('invoice_item')->where('item_invoice_id', $inv->invoice_id)->sum('item_subtotal');
            $terbayar = (float) DB::table('piutang')->where('piutang_invoice_id', $inv->invoice_id)->sum('piutang_terbayar');
            $out[] = [$r->invoice_nomor, $r->invoice_tanggal, $r->invoice_jatuh_tempo, $r->invoice_pelanggan, $this->rp($total), $this->rp($terbayar), $this->rp($total - $terbayar), $r->invoice_status];
        }

        return [['Nomor', 'Tanggal', 'Jatuh Tempo', 'Pelanggan', 'Total', 'Terbayar', 'Sisa', 'Status'], $out, []];
    }

    public function getProfitabilitasProduk(Request $request)
    {
        return $this->filterView('Profitabilitas Produk', 'laporan.getProfitabilitasProdukPrint', 'laporan.getProfitabilitasProdukExcel');
    }

    public function getProfitabilitasProdukPrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->profitabilitasProdukData();

        return $this->printResponse('Profitabilitas Produk', $this->periodeLabel($request).' · HPP diestimasi dari harga beli master produk.', $headers, $rows, $foot, route('laporan.getProfitabilitasProdukExcel', $request->query()));
    }

    public function getProfitabilitasProdukExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->profitabilitasProdukData();

        return $this->excelResponse('profitabilitas-produk-'.now()->format('Ymd-His').'.xls', 'Profitabilitas Produk', $this->periodeLabel($request).' · HPP diestimasi dari harga beli master produk.', $headers, $rows, $foot);
    }

    private function profitabilitasProdukData(): array
    {
        $jual = DB::table('invoice_item as ii')->join('invoice as i', 'i.invoice_id', '=', 'ii.item_invoice_id')
            ->select('ii.item_deskripsi', DB::raw('SUM(ii.item_qty) as qty'), DB::raw('SUM(ii.item_subtotal) as omzet'))
            ->groupBy('ii.item_deskripsi')->get();
        $out = [];
        foreach ($jual as $r) {
            $beli = (float) DB::table('produk')->where('produk_nama', $r->item_deskripsi)->value('produk_harga_beli');
            if (! $beli) {
                $beli = (float) DB::table('produk')->where('produk_nama', 'like', '%'.explode(' ', $r->item_deskripsi)[0].'%')->value('produk_harga_beli');
            }
            $hpp = $beli * (float) $r->qty;
            $out[] = [$r->item_deskripsi, $r->qty, $this->rp($r->omzet), $this->rp($hpp), $this->rp($r->omzet - $hpp)];
        }

        return [['Produk', 'Qty Terjual', 'Omzet', 'Estimasi HPP', 'Laba Kotor'], $out, []];
    }

    public function getProfitabilitasTagihan(Request $request)
    {
        return $this->filterView('Profitabilitas per Tagihan', 'laporan.getProfitabilitasTagihanPrint', 'laporan.getProfitabilitasTagihanExcel', [
            ['name' => 'pelanggan', 'label' => 'Pelanggan', 'options' => $this->pelangganOptions()],
        ]);
    }

    public function getProfitabilitasTagihanPrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->profitabilitasTagihanData($request);

        return $this->printResponse('Profitabilitas per Tagihan', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getProfitabilitasTagihanExcel', $request->query()));
    }

    public function getProfitabilitasTagihanExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->profitabilitasTagihanData($request);

        return $this->excelResponse('profitabilitas-tagihan-'.now()->format('Ymd-His').'.xls', 'Profitabilitas per Tagihan', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    private function profitabilitasTagihanData(Request $request): array
    {
        $rows = $this->period(DB::table('invoice'), $request, 'invoice_tanggal')
            ->when($request->input('pelanggan'), fn ($q) => $q->where('invoice_pelanggan', $request->input('pelanggan')))
            ->orderBy('invoice_tanggal')->limit(200)->get();
        $out = [];
        foreach ($rows as $inv) {
            $items = DB::table('invoice_item')->where('item_invoice_id', $inv->invoice_id)->get();
            $omzet = (float) $items->sum('item_subtotal');
            $hpp = 0;
            foreach ($items as $it) {
                $beli = (float) DB::table('produk')->where('produk_nama', $it->item_deskripsi)->value('produk_harga_beli');
                $hpp += $beli * (float) $it->item_qty;
            }
            $out[] = [$inv->invoice_nomor, $inv->invoice_pelanggan, $this->rp($omzet), $this->rp($hpp), $this->rp($omzet - $hpp)];
        }

        return [['Invoice', 'Pelanggan', 'Omzet', 'Estimasi HPP', 'Laba Kotor'], $out, []];
    }

    public function getPendapatanPelanggan(Request $request)
    {
        return $this->filterView('Pendapatan per Pelanggan', 'laporan.getPendapatanPelangganPrint', 'laporan.getPendapatanPelangganExcel', [
            ['name' => 'pelanggan', 'label' => 'Pelanggan', 'options' => $this->pelangganOptions()],
        ]);
    }

    public function getPendapatanPelangganPrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->pendapatanPelangganData($request);

        return $this->printResponse('Pendapatan per Pelanggan', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getPendapatanPelangganExcel', $request->query()));
    }

    public function getPendapatanPelangganExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->pendapatanPelangganData($request);

        return $this->excelResponse('pendapatan-pelanggan-'.now()->format('Ymd-His').'.xls', 'Pendapatan per Pelanggan', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    private function pendapatanPelangganData(Request $request): array
    {
        $rows = $this->period(DB::table('invoice_item as ii')
            ->join('invoice as i', 'i.invoice_id', '=', 'ii.item_invoice_id'), $request, 'i.invoice_tanggal')
            ->when($request->input('pelanggan'), fn ($q) => $q->where('i.invoice_pelanggan', $request->input('pelanggan')))
            ->select('i.invoice_pelanggan', DB::raw('SUM(ii.item_subtotal) as total'))
            ->groupBy('i.invoice_pelanggan')->orderByDesc('total')->get()
            ->map(fn ($r) => [$r->invoice_pelanggan, $this->rp($r->total)])->toArray();

        return [['Pelanggan', 'Total'], $rows, []];
    }

    public function getPenjualanProduk(Request $request)
    {
        return $this->filterView('Penjualan per Produk', 'laporan.getPenjualanProdukPrint', 'laporan.getPenjualanProdukExcel', [
            ['name' => 'pelanggan', 'label' => 'Pelanggan', 'options' => $this->pelangganOptions()],
        ]);
    }

    public function getPenjualanProdukPrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->penjualanProdukData($request);

        return $this->printResponse('Penjualan per Produk', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getPenjualanProdukExcel', $request->query()));
    }

    public function getPenjualanProdukExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->penjualanProdukData($request);

        return $this->excelResponse('penjualan-produk-'.now()->format('Ymd-His').'.xls', 'Penjualan per Produk', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    private function penjualanProdukData(Request $request): array
    {
        $rows = $this->period(DB::table('invoice_item as ii')
            ->join('invoice as i', 'i.invoice_id', '=', 'ii.item_invoice_id'), $request, 'i.invoice_tanggal')
            ->when($request->input('pelanggan'), fn ($q) => $q->where('i.invoice_pelanggan', $request->input('pelanggan')))
            ->select('ii.item_deskripsi', DB::raw('SUM(ii.item_qty) as qty'), DB::raw('SUM(ii.item_subtotal) as total'))
            ->groupBy('ii.item_deskripsi')->orderByDesc('total')->get()
            ->map(fn ($r) => [$r->item_deskripsi, $r->qty, $this->rp($r->total)])->toArray();

        return [['Produk', 'Qty', 'Total'], $rows, []];
    }

    public function getPemesananProduk(Request $request)
    {
        return $this->filterView('Pemesanan per Produk', 'laporan.getPemesananProdukPrint', 'laporan.getPemesananProdukExcel', [
            ['name' => 'pelanggan', 'label' => 'Pelanggan', 'options' => $this->pelangganOptions()],
        ]);
    }

    public function getPemesananProdukPrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->pemesananProdukData($request);

        return $this->printResponse('Pemesanan per Produk', $this->periodeLabel($request).' · Belum ada modul sales order — memakai qty item invoice sebagai proksi pesanan.', $headers, $rows, $foot, route('laporan.getPemesananProdukExcel', $request->query()));
    }

    public function getPemesananProdukExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->pemesananProdukData($request);

        return $this->excelResponse('pemesanan-produk-'.now()->format('Ymd-His').'.xls', 'Pemesanan per Produk', $this->periodeLabel($request).' · Belum ada modul sales order — memakai qty item invoice sebagai proksi pesanan.', $headers, $rows, $foot);
    }

    private function pemesananProdukData(Request $request): array
    {
        $rows = $this->period(DB::table('invoice_item as ii')
            ->join('invoice as i', 'i.invoice_id', '=', 'ii.item_invoice_id'), $request, 'i.invoice_tanggal')
            ->when($request->input('pelanggan'), fn ($q) => $q->where('i.invoice_pelanggan', $request->input('pelanggan')))
            ->select('ii.item_deskripsi', DB::raw('SUM(ii.item_qty) as qty'))
            ->groupBy('ii.item_deskripsi')->orderByDesc('qty')->get()
            ->map(fn ($r) => [$r->item_deskripsi, $r->qty])->toArray();

        return [['Produk', 'Qty Dipesan'], $rows, []];
    }

    public function getPenjualanSales(Request $request)
    {
        return $this->filterView('Penjualan per Sales Person', 'laporan.getPenjualanSalesPrint', 'laporan.getPenjualanSalesExcel');
    }

    public function getPenjualanSalesPrint(Request $request)
    {
        return $this->printResponse('Penjualan per Sales Person', $this->periodeLabel($request).' · Belum ada field sales person di invoice — tambahkan kolom bila perlu.', ['Sales', 'Transaksi', 'Total'], [], [], route('laporan.getPenjualanSalesExcel', $request->query()));
    }

    public function getPenjualanSalesExcel(Request $request)
    {
        return $this->excelResponse('penjualan-sales-'.now()->format('Ymd-His').'.xls', 'Penjualan per Sales Person', $this->periodeLabel($request).' · Belum ada field sales person di invoice — tambahkan kolom bila perlu.', ['Sales', 'Transaksi', 'Total'], [], []);
    }

    public function getPengirimanPenjualan(Request $request)
    {
        return $this->filterView('Pengiriman Penjualan', 'laporan.getPengirimanPenjualanPrint', 'laporan.getPengirimanPenjualanExcel');
    }

    public function getPengirimanPenjualanPrint(Request $request)
    {
        return $this->printResponse('Pengiriman Penjualan', $this->periodeLabel($request).' · Belum ada modul surat jalan — pengiriman dicatat manual.', ['No. Surat Jalan', 'Invoice', 'Tanggal', 'Status'], [], [], route('laporan.getPengirimanPenjualanExcel', $request->query()));
    }

    public function getPengirimanPenjualanExcel(Request $request)
    {
        return $this->excelResponse('pengiriman-penjualan-'.now()->format('Ymd-His').'.xls', 'Pengiriman Penjualan', $this->periodeLabel($request).' · Belum ada modul surat jalan — pengiriman dicatat manual.', ['No. Surat Jalan', 'Invoice', 'Tanggal', 'Status'], [], []);
    }

    public function getOngkosKirimEkspedisi(Request $request)
    {
        return $this->filterView('Ongkos Kirim per Ekspedisi', 'laporan.getOngkosKirimEkspedisiPrint', 'laporan.getOngkosKirimEkspedisiExcel', [
            ['name' => 'pelanggan', 'label' => 'Pelanggan', 'options' => $this->pelangganOptions()],
        ]);
    }

    public function getOngkosKirimEkspedisiPrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->ongkosKirimEkspedisiData($request);

        return $this->printResponse('Ongkos Kirim per Ekspedisi', $this->periodeLabel($request).' · Kolom ekspedisi belum ada — ditampilkan per invoice, isi manual bila perlu.', $headers, $rows, $foot, route('laporan.getOngkosKirimEkspedisiExcel', $request->query()));
    }

    public function getOngkosKirimEkspedisiExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->ongkosKirimEkspedisiData($request);

        return $this->excelResponse('ongkos-kirim-ekspedisi-'.now()->format('Ymd-His').'.xls', 'Ongkos Kirim per Ekspedisi', $this->periodeLabel($request).' · Kolom ekspedisi belum ada — ditampilkan per invoice, isi manual bila perlu.', $headers, $rows, $foot);
    }

    private function ongkosKirimEkspedisiData(Request $request): array
    {
        $rows = $this->period(DB::table('invoice'), $request, 'invoice_tanggal')
            ->where('invoice_ongkir', '>', 0)
            ->when($request->input('pelanggan'), fn ($q) => $q->where('invoice_pelanggan', $request->input('pelanggan')))
            ->select('invoice_nomor', 'invoice_pelanggan', 'invoice_ongkir')->orderBy('invoice_tanggal')->limit(200)->get()
            ->map(fn ($r) => [$r->invoice_nomor, $r->invoice_pelanggan, '-', $this->rp($r->invoice_ongkir)])->toArray();

        return [['Invoice', 'Pelanggan', 'Ekspedisi', 'Ongkir'], $rows, []];
    }

    public function getPelunasanTagihan(Request $request)
    {
        return $this->filterView('Pelunasan Pembayaran Tagihan', 'laporan.getPelunasanTagihanPrint', 'laporan.getPelunasanTagihanExcel', [
            ['name' => 'pelanggan', 'label' => 'Pelanggan', 'options' => $this->pelangganOptions()],
        ]);
    }

    public function getPelunasanTagihanPrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->pelunasanTagihanData($request);

        return $this->printResponse('Pelunasan Pembayaran Tagihan', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getPelunasanTagihanExcel', $request->query()));
    }

    public function getPelunasanTagihanExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->pelunasanTagihanData($request);

        return $this->excelResponse('pelunasan-tagihan-'.now()->format('Ymd-His').'.xls', 'Pelunasan Pembayaran Tagihan', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    private function pelunasanTagihanData(Request $request): array
    {
        $rows = $this->period(DB::table('bayar_piutang as b')
            ->leftJoin('piutang as p', 'p.piutang_id', '=', 'b.bayar_piutang_id'), $request, 'b.bayar_tanggal')
            ->when($request->input('pelanggan'), fn ($q) => $q->where('p.piutang_pelanggan', $request->input('pelanggan')))
            ->select('b.bayar_tanggal', 'p.piutang_pelanggan', 'b.bayar_jumlah', 'b.bayar_catatan')
            ->orderBy('b.bayar_tanggal')->limit(500)->get()
            ->map(fn ($r) => [$r->bayar_tanggal, $r->piutang_pelanggan, $this->rp($r->bayar_jumlah), $r->bayar_catatan])->toArray();

        return [['Tanggal', 'Pelanggan', 'Jumlah', 'Catatan'], $rows, []];
    }

    public function getRekapPembayaranPelanggan(Request $request)
    {
        return $this->filterView('Rekap Pembayaran Pelanggan', 'laporan.getRekapPembayaranPelangganPrint', 'laporan.getRekapPembayaranPelangganExcel', [
            ['name' => 'pelanggan', 'label' => 'Pelanggan', 'options' => $this->pelangganOptions()],
        ]);
    }

    public function getRekapPembayaranPelangganPrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->rekapPembayaranPelangganData($request);

        return $this->printResponse('Rekap Pembayaran Pelanggan', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getRekapPembayaranPelangganExcel', $request->query()));
    }

    public function getRekapPembayaranPelangganExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->rekapPembayaranPelangganData($request);

        return $this->excelResponse('rekap-pembayaran-pelanggan-'.now()->format('Ymd-His').'.xls', 'Rekap Pembayaran Pelanggan', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    private function rekapPembayaranPelangganData(Request $request): array
    {
        $rows = $this->period(DB::table('bayar_piutang as b')
            ->leftJoin('piutang as p', 'p.piutang_id', '=', 'b.bayar_piutang_id'), $request, 'b.bayar_tanggal')
            ->when($request->input('pelanggan'), fn ($q) => $q->where('p.piutang_pelanggan', $request->input('pelanggan')))
            ->select('p.piutang_pelanggan', DB::raw('SUM(b.bayar_jumlah) as total'), DB::raw('COUNT(*) as n'))
            ->groupBy('p.piutang_pelanggan')->get()
            ->map(fn ($r) => [$r->piutang_pelanggan, $r->n, $this->rp($r->total)])->toArray();

        return [['Pelanggan', 'Kali Bayar', 'Total'], $rows, []];
    }

    public function getPenjualanKategori(Request $request)
    {
        return $this->filterView('Penjualan per Kategori Produk', 'laporan.getPenjualanKategoriPrint', 'laporan.getPenjualanKategoriExcel', [
            ['name' => 'pelanggan', 'label' => 'Pelanggan', 'options' => $this->pelangganOptions()],
        ]);
    }

    public function getPenjualanKategoriPrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->penjualanKategoriData($request);

        return $this->printResponse('Penjualan per Kategori Produk', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getPenjualanKategoriExcel', $request->query()));
    }

    public function getPenjualanKategoriExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->penjualanKategoriData($request);

        return $this->excelResponse('penjualan-kategori-'.now()->format('Ymd-His').'.xls', 'Penjualan per Kategori Produk', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    private function penjualanKategoriData(Request $request): array
    {
        $rows = $this->period(DB::table('invoice_item as ii')
            ->join('invoice as i', 'i.invoice_id', '=', 'ii.item_invoice_id')
            ->leftJoin('produk as p', 'p.produk_nama', '=', 'ii.item_deskripsi'), $request, 'i.invoice_tanggal')
            ->when($request->input('pelanggan'), fn ($q) => $q->where('i.invoice_pelanggan', $request->input('pelanggan')))
            ->select(DB::raw("COALESCE(p.produk_kategori, 'Lainnya') as kat"), DB::raw('SUM(ii.item_subtotal) as total'))
            ->groupBy('kat')->orderByDesc('total')->get()
            ->map(fn ($r) => [$r->kat, $this->rp($r->total)])->toArray();

        return [['Kategori', 'Total'], $rows, []];
    }

    public function getPenjualanProdukPelanggan(Request $request)
    {
        return $this->filterView('Penjualan Produk per Pelanggan', 'laporan.getPenjualanProdukPelangganPrint', 'laporan.getPenjualanProdukPelangganExcel', [
            ['name' => 'pelanggan', 'label' => 'Pelanggan', 'options' => $this->pelangganOptions()],
        ]);
    }

    public function getPenjualanProdukPelangganPrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->penjualanProdukPelangganData($request);

        return $this->printResponse('Penjualan Produk per Pelanggan', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getPenjualanProdukPelangganExcel', $request->query()));
    }

    public function getPenjualanProdukPelangganExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->penjualanProdukPelangganData($request);

        return $this->excelResponse('penjualan-produk-pelanggan-'.now()->format('Ymd-His').'.xls', 'Penjualan Produk per Pelanggan', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    private function penjualanProdukPelangganData(Request $request): array
    {
        $rows = $this->period(DB::table('invoice_item as ii')
            ->join('invoice as i', 'i.invoice_id', '=', 'ii.item_invoice_id'), $request, 'i.invoice_tanggal')
            ->when($request->input('pelanggan'), fn ($q) => $q->where('i.invoice_pelanggan', $request->input('pelanggan')))
            ->select('i.invoice_pelanggan', 'ii.item_deskripsi', DB::raw('SUM(ii.item_qty) as qty'), DB::raw('SUM(ii.item_subtotal) as total'))
            ->groupBy('i.invoice_pelanggan', 'ii.item_deskripsi')->orderBy('i.invoice_pelanggan')->limit(500)->get()
            ->map(fn ($r) => [$r->invoice_pelanggan, $r->item_deskripsi, $r->qty, $this->rp($r->total)])->toArray();

        return [['Pelanggan', 'Produk', 'Qty', 'Total'], $rows, []];
    }

    public function getPenjualanPeriode(Request $request)
    {
        return $this->filterView('Penjualan per Periode', 'laporan.getPenjualanPeriodePrint', 'laporan.getPenjualanPeriodeExcel', [
            ['name' => 'pelanggan', 'label' => 'Pelanggan', 'options' => $this->pelangganOptions()],
        ]);
    }

    public function getPenjualanPeriodePrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->penjualanPeriodeData($request);

        return $this->printResponse('Penjualan per Periode', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getPenjualanPeriodeExcel', $request->query()));
    }

    public function getPenjualanPeriodeExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->penjualanPeriodeData($request);

        return $this->excelResponse('penjualan-periode-'.now()->format('Ymd-His').'.xls', 'Penjualan per Periode', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    private function penjualanPeriodeData(Request $request): array
    {
        $rows = DB::table('invoice_item as ii')->join('invoice as i', 'i.invoice_id', '=', 'ii.item_invoice_id')
            ->whereYear('i.invoice_tanggal', (int) $request->input('tahun', date('Y')))
            ->when($request->input('pelanggan'), fn ($q) => $q->where('i.invoice_pelanggan', $request->input('pelanggan')))
            ->select(DB::raw("strftime('%Y-%m', i.invoice_tanggal) as periode"), DB::raw('SUM(ii.item_subtotal) as total'), DB::raw('COUNT(DISTINCT i.invoice_id) as n'))
            ->groupBy('periode')->orderBy('periode')->get()
            ->map(fn ($r) => [$r->periode, $r->n, $this->rp($r->total)])->toArray();

        return [['Periode', 'Transaksi', 'Total'], $rows, []];
    }

    public function getPenjualanRegion(Request $request)
    {
        return $this->filterView('Penjualan per Region', 'laporan.getPenjualanRegionPrint', 'laporan.getPenjualanRegionExcel');
    }

    public function getPenjualanRegionPrint(Request $request)
    {
        return $this->printResponse('Penjualan per Region', $this->periodeLabel($request).' · Belum ada field region di master pelanggan — tambahkan bila perlu.', ['Region', 'Transaksi', 'Total'], [], [], route('laporan.getPenjualanRegionExcel', $request->query()));
    }

    public function getPenjualanRegionExcel(Request $request)
    {
        return $this->excelResponse('penjualan-region-'.now()->format('Ymd-His').'.xls', 'Penjualan per Region', $this->periodeLabel($request).' · Belum ada field region di master pelanggan — tambahkan bila perlu.', ['Region', 'Transaksi', 'Total'], [], []);
    }

    public function getRiwayatHargaJual(Request $request)
    {
        return $this->filterView('Laporan Riwayat Harga Jual Produk', 'laporan.getRiwayatHargaJualPrint', 'laporan.getRiwayatHargaJualExcel');
    }

    public function getRiwayatHargaJualPrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->riwayatHargaJualData();

        return $this->printResponse('Laporan Riwayat Harga Jual Produk', $this->periodeLabel($request).' · Menampilkan harga master saat ini (riwayat perubahan belum dicatat).', $headers, $rows, $foot, route('laporan.getRiwayatHargaJualExcel', $request->query()));
    }

    public function getRiwayatHargaJualExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->riwayatHargaJualData();

        return $this->excelResponse('riwayat-harga-jual-'.now()->format('Ymd-His').'.xls', 'Laporan Riwayat Harga Jual Produk', $this->periodeLabel($request).' · Menampilkan harga master saat ini (riwayat perubahan belum dicatat).', $headers, $rows, $foot);
    }

    private function riwayatHargaJualData(): array
    {
        $rows = DB::table('produk')->select('produk_kode', 'produk_nama', 'produk_harga_beli', 'produk_harga_jual')
            ->orderBy('produk_kode')->limit(200)->get()
            ->map(fn ($r) => [$r->produk_kode, $r->produk_nama, $this->rp($r->produk_harga_beli), $this->rp($r->produk_harga_jual)])->toArray();

        return [['Kode', 'Produk', 'Harga Beli', 'Harga Jual'], $rows, []];
    }

    public function getPaymentConnect(Request $request)
    {
        return $this->tampil($request, 'Pencairan Payment Connect Kledo', ['Tanggal', 'Referensi', 'Nominal'], [], [], 'Integrasi payment gateway Kledo belum tersedia di sistem ini.');
    }

    public function getPaymentGateway(Request $request)
    {
        return $this->tampil($request, 'Transaksi Payment Gateway Kledo', ['Tanggal', 'Referensi', 'Nominal', 'Status'], [], [], 'Integrasi payment gateway Kledo belum tersedia di sistem ini.');
    }

    public function getReturProduk(Request $request)
    {
        return $this->filterView('Retur per Produk', 'laporan.getReturProdukPrint', 'laporan.getReturProdukExcel');
    }

    public function getReturProdukPrint(Request $request)
    {
        return $this->printResponse('Retur per Produk', $this->periodeLabel($request).' · Belum ada modul retur penjualan — retur dicatat lewat jurnal tipe RETUR.', ['Produk', 'Qty Retur', 'Nominal'], [], [], route('laporan.getReturProdukExcel', $request->query()));
    }

    public function getReturProdukExcel(Request $request)
    {
        return $this->excelResponse('retur-produk-'.now()->format('Ymd-His').'.xls', 'Retur per Produk', $this->periodeLabel($request).' · Belum ada modul retur penjualan — retur dicatat lewat jurnal tipe RETUR.', ['Produk', 'Qty Retur', 'Nominal'], [], []);
    }

    // ---- Pembelian (BAC: filter → print viewer + excel) ----
    private function vendorOptions(): array
    {
        return \App\Models\Vendor::orderBy('vendor_nama')->pluck('vendor_nama', 'vendor_nama')->toArray();
    }

    private function vendorDropdown(): array
    {
        return [['name' => 'vendor', 'label' => 'Vendor', 'options' => $this->vendorOptions()]];
    }

    private function pembelianData(Request $request): array
    {
        $rows = $this->period(DB::table('purchase_order as p')
            ->leftJoin('po_item as pi', 'pi.poi_po_id', '=', 'p.po_id'), $request, 'p.po_tanggal')
            ->when($request->input('vendor'), fn ($q) => $q->where('p.po_vendor', $request->input('vendor')))
            ->select('p.po_nomor', 'p.po_tanggal', 'p.po_vendor', 'p.po_status', DB::raw('SUM(pi.poi_subtotal) as total'))
            ->groupBy('p.po_nomor', 'p.po_tanggal', 'p.po_vendor', 'p.po_status')
            ->orderBy('p.po_tanggal')->limit(5000)->get()
            ->map(fn ($r) => [$r->po_tanggal, $r->po_nomor, $r->po_vendor, $this->rp($r->total), $r->po_status])->toArray();

        return [['Tanggal', 'Nomor', 'Vendor', 'Total', 'Status'], $rows, []];
    }

    public function getPembelian(Request $request)
    {
        return $this->filterView('Pembelian', 'laporan.getPembelianPrint', 'laporan.getPembelianExcel', $this->vendorDropdown());
    }

    public function getPembelianPrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->pembelianData($request);

        return $this->printResponse('Pembelian', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getPembelianExcel', $request->query()));
    }

    public function getPembelianExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->pembelianData($request);

        return $this->excelResponse('pembelian-'.now()->format('Ymd-His').'.xls', 'Pembelian', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    private function detailPembelianData(Request $request): array
    {
        $rows = $this->period(DB::table('po_item as pi')
            ->join('purchase_order as p', 'p.po_id', '=', 'pi.poi_po_id'), $request, 'p.po_tanggal')
            ->when($request->input('vendor'), fn ($q) => $q->where('p.po_vendor', $request->input('vendor')))
            ->select('p.po_nomor', 'p.po_vendor', 'pi.poi_deskripsi', 'pi.poi_qty', 'pi.poi_harga_satuan', 'pi.poi_subtotal')
            ->orderBy('p.po_tanggal')->limit(5000)->get()
            ->map(fn ($r) => [$r->po_nomor, $r->po_vendor, $r->poi_deskripsi, $r->poi_qty, $this->rp($r->poi_harga_satuan), $this->rp($r->poi_subtotal)])->toArray();

        return [['PO', 'Vendor', 'Item', 'Qty', 'Harga', 'Subtotal'], $rows, []];
    }

    public function getDetailPembelian(Request $request)
    {
        return $this->filterView('Detail Pembelian', 'laporan.getDetailPembelianPrint', 'laporan.getDetailPembelianExcel', $this->vendorDropdown());
    }

    public function getDetailPembelianPrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->detailPembelianData($request);

        return $this->printResponse('Detail Pembelian', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getDetailPembelianExcel', $request->query()));
    }

    public function getDetailPembelianExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->detailPembelianData($request);

        return $this->excelResponse('detail-pembelian-'.now()->format('Ymd-His').'.xls', 'Detail Pembelian', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    private function umurHutangData(Request $request): array
    {
        $rows = DB::table('hutang')
            ->when($request->input('vendor'), fn ($q) => $q->where('hutang_pemasok', $request->input('vendor')))
            ->get()->map(function ($r) {
                $sisa = (float) $r->hutang_jumlah - (float) $r->hutang_terbayar;
                if ($sisa <= 0) {
                    return null;
                }
                $jt = $r->hutang_jatuh_tempo ?: $r->hutang_tanggal;
                $hari = (int) floor((strtotime(date('Y-m-d')) - strtotime($jt)) / 86400);
                $bucket = $hari <= 0 ? 'Belum jatuh tempo' : ($hari <= 30 ? '1-30 hari' : ($hari <= 60 ? '31-60 hari' : ($hari <= 90 ? '61-90 hari' : '> 90 hari')));

                return [$r->hutang_pemasok, $r->hutang_tanggal, $jt, $bucket, $this->rp($sisa)];
            })->filter()->values()->toArray();

        return [['Vendor', 'Tanggal', 'Jatuh Tempo', 'Umur', 'Sisa'], $rows, []];
    }

    public function getUmurHutang(Request $request)
    {
        return $this->filterView('Umur Hutang', 'laporan.getUmurHutangPrint', 'laporan.getUmurHutangExcel', $this->vendorDropdown());
    }

    public function getUmurHutangPrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->umurHutangData($request);

        return $this->printResponse('Umur Hutang', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getUmurHutangExcel', $request->query()));
    }

    public function getUmurHutangExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->umurHutangData($request);

        return $this->excelResponse('umur-hutang-'.now()->format('Ymd-His').'.xls', 'Umur Hutang', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    private function hutangVendorData(Request $request): array
    {
        $sisa = $this->sisaHutang();
        if ($request->input('vendor')) {
            $sisa = $sisa->where('kontak', $request->input('vendor'));
        }
        $out = $sisa->map(fn ($r) => [$r->kontak, $this->rp($r->sisa)])->values()->toArray();
        $foot = ['Total Hutang', $this->rp($sisa->sum('sisa'))];

        return [['Vendor', 'Sisa Hutang'], $out, $foot];
    }

    public function getHutangVendor(Request $request)
    {
        return $this->filterView('Laporan Hutang Vendor', 'laporan.getHutangVendorPrint', 'laporan.getHutangVendorExcel', $this->vendorDropdown());
    }

    public function getHutangVendorPrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->hutangVendorData($request);

        return $this->printResponse('Laporan Hutang Vendor', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getHutangVendorExcel', $request->query()));
    }

    public function getHutangVendorExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->hutangVendorData($request);

        return $this->excelResponse('hutang-vendor-'.now()->format('Ymd-His').'.xls', 'Laporan Hutang Vendor', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    private function saldoHutangTerminData(Request $request): array
    {
        $rows = DB::table('hutang')
            ->when($request->input('vendor'), fn ($q) => $q->where('hutang_pemasok', $request->input('vendor')))
            ->select('hutang_jatuh_tempo', DB::raw('SUM(hutang_jumlah - hutang_terbayar) as sisa'))
            ->groupBy('hutang_jatuh_tempo')->orderBy('hutang_jatuh_tempo')->get()
            ->map(fn ($r) => [$r->hutang_jatuh_tempo ?: '-', $this->rp($r->sisa)])->toArray();

        return [['Jatuh Tempo', 'Sisa'], $rows, []];
    }

    public function getSaldoHutangTermin(Request $request)
    {
        return $this->filterView('Saldo Hutang Termin', 'laporan.getSaldoHutangTerminPrint', 'laporan.getSaldoHutangTerminExcel', $this->vendorDropdown());
    }

    public function getSaldoHutangTerminPrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->saldoHutangTerminData($request);

        return $this->printResponse('Saldo Hutang Termin', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getSaldoHutangTerminExcel', $request->query()));
    }

    public function getSaldoHutangTerminExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->saldoHutangTerminData($request);

        return $this->excelResponse('saldo-hutang-termin-'.now()->format('Ymd-His').'.xls', 'Saldo Hutang Termin', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    public function getDebitMemo(Request $request)
    {
        return $this->filterView('Debit Memo', 'laporan.getDebitMemoPrint', 'laporan.getDebitMemoExcel');
    }

    public function getDebitMemoPrint(Request $request)
    {
        $headers = ['Nomor', 'Vendor', 'Tanggal', 'Nominal'];

        return $this->printResponse('Debit Memo', $this->periodeLabel($request), $headers, [], [], route('laporan.getDebitMemoExcel', $request->query()));
    }

    public function getDebitMemoExcel(Request $request)
    {
        $headers = ['Nomor', 'Vendor', 'Tanggal', 'Nominal'];

        return $this->excelResponse('debit-memo-'.now()->format('Ymd-His').'.xls', 'Debit Memo', $this->periodeLabel($request), $headers, []);
    }

    private function tagihanVendorData(Request $request): array
    {
        $rows = DB::table('hutang')
            ->when($request->input('vendor'), fn ($q) => $q->where('hutang_pemasok', $request->input('vendor')))
            ->orderBy('hutang_tanggal')->limit(5000)->get()
            ->map(fn ($r) => [$r->hutang_tanggal, $r->hutang_jatuh_tempo, $r->hutang_pemasok, $this->rp($r->hutang_jumlah), $this->rp($r->hutang_terbayar), $this->rp($r->hutang_jumlah - $r->hutang_terbayar), $r->hutang_status])->toArray();

        return [['Tanggal', 'Jatuh Tempo', 'Vendor', 'Total', 'Terbayar', 'Sisa', 'Status'], $rows, []];
    }

    public function getTagihanVendor(Request $request)
    {
        return $this->filterView('Tagihan Vendor', 'laporan.getTagihanVendorPrint', 'laporan.getTagihanVendorExcel', $this->vendorDropdown());
    }

    public function getTagihanVendorPrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->tagihanVendorData($request);

        return $this->printResponse('Tagihan Vendor', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getTagihanVendorExcel', $request->query()));
    }

    public function getTagihanVendorExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->tagihanVendorData($request);

        return $this->excelResponse('tagihan-vendor-'.now()->format('Ymd-His').'.xls', 'Tagihan Vendor', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    private function pembelianProdukData(Request $request): array
    {
        $rows = $this->period(DB::table('po_item as pi')
            ->join('purchase_order as p', 'p.po_id', '=', 'pi.poi_po_id'), $request, 'p.po_tanggal')
            ->when($request->input('vendor'), fn ($q) => $q->where('p.po_vendor', $request->input('vendor')))
            ->select('pi.poi_deskripsi', DB::raw('SUM(pi.poi_qty) as qty'), DB::raw('SUM(pi.poi_subtotal) as total'))
            ->groupBy('pi.poi_deskripsi')->orderByDesc('total')->get()
            ->map(fn ($r) => [$r->poi_deskripsi, $r->qty, $this->rp($r->total)])->toArray();

        return [['Produk', 'Qty', 'Total'], $rows, []];
    }

    public function getPembelianProduk(Request $request)
    {
        return $this->filterView('Pembelian per Produk', 'laporan.getPembelianProdukPrint', 'laporan.getPembelianProdukExcel', $this->vendorDropdown());
    }

    public function getPembelianProdukPrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->pembelianProdukData($request);

        return $this->printResponse('Pembelian per Produk', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getPembelianProdukExcel', $request->query()));
    }

    public function getPembelianProdukExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->pembelianProdukData($request);

        return $this->excelResponse('pembelian-produk-'.now()->format('Ymd-His').'.xls', 'Pembelian per Produk', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    public function getReturPembelianProduk(Request $request)
    {
        return $this->filterView('Retur Pembelian per Produk', 'laporan.getReturPembelianProdukPrint', 'laporan.getReturPembelianProdukExcel');
    }

    public function getReturPembelianProdukPrint(Request $request)
    {
        $headers = ['Produk', 'Qty Retur', 'Nominal'];

        return $this->printResponse('Retur Pembelian per Produk', $this->periodeLabel($request), $headers, [], [], route('laporan.getReturPembelianProdukExcel', $request->query()));
    }

    public function getReturPembelianProdukExcel(Request $request)
    {
        $headers = ['Produk', 'Qty Retur', 'Nominal'];

        return $this->excelResponse('retur-pembelian-produk-'.now()->format('Ymd-His').'.xls', 'Retur Pembelian per Produk', $this->periodeLabel($request), $headers, []);
    }

    private function pembelianVendorData(Request $request): array
    {
        $rows = $this->period(DB::table('po_item as pi')
            ->join('purchase_order as p', 'p.po_id', '=', 'pi.poi_po_id'), $request, 'p.po_tanggal')
            ->when($request->input('vendor'), fn ($q) => $q->where('p.po_vendor', $request->input('vendor')))
            ->select('p.po_vendor', DB::raw('SUM(pi.poi_subtotal) as total'), DB::raw('COUNT(DISTINCT p.po_id) as n'))
            ->groupBy('p.po_vendor')->orderByDesc('total')->get()
            ->map(fn ($r) => [$r->po_vendor, $r->n, $this->rp($r->total)])->toArray();

        return [['Vendor', 'Transaksi', 'Total'], $rows, []];
    }

    public function getPembelianVendor(Request $request)
    {
        return $this->filterView('Pembelian per Vendor', 'laporan.getPembelianVendorPrint', 'laporan.getPembelianVendorExcel', $this->vendorDropdown());
    }

    public function getPembelianVendorPrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->pembelianVendorData($request);

        return $this->printResponse('Pembelian per Vendor', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getPembelianVendorExcel', $request->query()));
    }

    public function getPembelianVendorExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->pembelianVendorData($request);

        return $this->excelResponse('pembelian-vendor-'.now()->format('Ymd-His').'.xls', 'Pembelian per Vendor', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    private function pengirimanPembelianData(Request $request): array
    {
        $rows = $this->period(DB::table('purchase_order'), $request, 'po_tanggal')
            ->when($request->input('vendor'), fn ($q) => $q->where('po_vendor', $request->input('vendor')))
            ->select('po_nomor', 'po_tanggal', 'po_expected_date', 'po_vendor', 'po_status')->orderBy('po_tanggal')->limit(5000)->get()
            ->map(fn ($r) => [$r->po_nomor, $r->po_tanggal, $r->po_expected_date ?: '-', $r->po_vendor, $r->po_status])->toArray();

        return [['PO', 'Tanggal PO', 'Ekspektasi Datang', 'Vendor', 'Status'], $rows, []];
    }

    public function getPengirimanPembelian(Request $request)
    {
        return $this->filterView('Pengiriman Pembelian', 'laporan.getPengirimanPembelianPrint', 'laporan.getPengirimanPembelianExcel', $this->vendorDropdown());
    }

    public function getPengirimanPembelianPrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->pengirimanPembelianData($request);

        return $this->printResponse('Pengiriman Pembelian', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getPengirimanPembelianExcel', $request->query()));
    }

    public function getPengirimanPembelianExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->pengirimanPembelianData($request);

        return $this->excelResponse('pengiriman-pembelian-'.now()->format('Ymd-His').'.xls', 'Pengiriman Pembelian', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    private function pelunasanPembelianData(Request $request): array
    {
        $rows = $this->period(DB::table('bayar_hutang as b')
            ->leftJoin('hutang as h', 'h.hutang_id', '=', 'b.bh_hutang_id'), $request, 'b.bh_tanggal')
            ->when($request->input('vendor'), fn ($q) => $q->where('h.hutang_pemasok', $request->input('vendor')))
            ->select('b.bh_tanggal', 'h.hutang_pemasok', 'b.bh_jumlah', 'b.bh_catatan')
            ->orderBy('b.bh_tanggal')->limit(5000)->get()
            ->map(fn ($r) => [$r->bh_tanggal, $r->hutang_pemasok, $this->rp($r->bh_jumlah), $r->bh_catatan])->toArray();

        return [['Tanggal', 'Vendor', 'Jumlah', 'Catatan'], $rows, []];
    }

    public function getPelunasanPembelian(Request $request)
    {
        return $this->filterView('Pelunasan Pembayaran Tagihan Pembelian', 'laporan.getPelunasanPembelianPrint', 'laporan.getPelunasanPembelianExcel', $this->vendorDropdown());
    }

    public function getPelunasanPembelianPrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->pelunasanPembelianData($request);

        return $this->printResponse('Pelunasan Pembayaran Tagihan Pembelian', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getPelunasanPembelianExcel', $request->query()));
    }

    public function getPelunasanPembelianExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->pelunasanPembelianData($request);

        return $this->excelResponse('pelunasan-pembelian-'.now()->format('Ymd-His').'.xls', 'Pelunasan Pembayaran Tagihan Pembelian', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    private function pembelianProdukVendorData(Request $request): array
    {
        $rows = $this->period(DB::table('po_item as pi')
            ->join('purchase_order as p', 'p.po_id', '=', 'pi.poi_po_id'), $request, 'p.po_tanggal')
            ->when($request->input('vendor'), fn ($q) => $q->where('p.po_vendor', $request->input('vendor')))
            ->select('p.po_vendor', 'pi.poi_deskripsi', DB::raw('SUM(pi.poi_qty) as qty'), DB::raw('SUM(pi.poi_subtotal) as total'))
            ->groupBy('p.po_vendor', 'pi.poi_deskripsi')->orderBy('p.po_vendor')->limit(5000)->get()
            ->map(fn ($r) => [$r->po_vendor, $r->poi_deskripsi, $r->qty, $this->rp($r->total)])->toArray();

        return [['Vendor', 'Produk', 'Qty', 'Total'], $rows, []];
    }

    public function getPembelianProdukVendor(Request $request)
    {
        return $this->filterView('Pembelian Produk per Vendor', 'laporan.getPembelianProdukVendorPrint', 'laporan.getPembelianProdukVendorExcel', $this->vendorDropdown());
    }

    public function getPembelianProdukVendorPrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->pembelianProdukVendorData($request);

        return $this->printResponse('Pembelian Produk per Vendor', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getPembelianProdukVendorExcel', $request->query()));
    }

    public function getPembelianProdukVendorExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->pembelianProdukVendorData($request);

        return $this->excelResponse('pembelian-produk-vendor-'.now()->format('Ymd-His').'.xls', 'Pembelian Produk per Vendor', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    private function pembelianPeriodeData(Request $request): array
    {
        $rows = DB::table('po_item as pi')->join('purchase_order as p', 'p.po_id', '=', 'pi.poi_po_id')
            ->whereYear('p.po_tanggal', (int) $request->input('tahun', date('Y')))
            ->when($request->input('vendor'), fn ($q) => $q->where('p.po_vendor', $request->input('vendor')))
            ->select(DB::raw("strftime('%Y-%m', p.po_tanggal) as periode"), DB::raw('SUM(pi.poi_subtotal) as total'), DB::raw('COUNT(DISTINCT p.po_id) as n'))
            ->groupBy('periode')->orderBy('periode')->get()
            ->map(fn ($r) => [$r->periode, $r->n, $this->rp($r->total)])->toArray();

        return [['Periode', 'Transaksi', 'Total'], $rows, []];
    }

    public function getPembelianPeriode(Request $request)
    {
        return $this->filterView('Pembelian per Periode', 'laporan.getPembelianPeriodePrint', 'laporan.getPembelianPeriodeExcel', $this->vendorDropdown());
    }

    public function getPembelianPeriodePrint(Request $request)
    {
        [$headers, $rows, $foot] = $this->pembelianPeriodeData($request);

        return $this->printResponse('Pembelian per Periode', $this->periodeLabel($request), $headers, $rows, $foot, route('laporan.getPembelianPeriodeExcel', $request->query()));
    }

    public function getPembelianPeriodeExcel(Request $request)
    {
        [$headers, $rows, $foot] = $this->pembelianPeriodeData($request);

        return $this->excelResponse('pembelian-periode-'.now()->format('Ymd-His').'.xls', 'Pembelian per Periode', $this->periodeLabel($request), $headers, $rows, $foot);
    }

    public function getPembelianRegion(Request $request)
    {
        return $this->filterView('Pembelian per Region', 'laporan.getPembelianRegionPrint', 'laporan.getPembelianRegionExcel');
    }

    public function getPembelianRegionPrint(Request $request)
    {
        $headers = ['Region', 'Transaksi', 'Total'];

        return $this->printResponse('Pembelian per Region', $this->periodeLabel($request), $headers, [], [], route('laporan.getPembelianRegionExcel', $request->query()));
    }

    public function getPembelianRegionExcel(Request $request)
    {
        $headers = ['Region', 'Transaksi', 'Total'];

        return $this->excelResponse('pembelian-region-'.now()->format('Ymd-His').'.xls', 'Pembelian per Region', $this->periodeLabel($request), $headers, []);
    }

    public function getTutupBuku(Request $request)
    {
        $tahun = (int) $request->input('tahun', date('Y'));
        $rows = $this->saldoAkun($tahun, null);
        $pendapatan = $rows->where('akun_tipe', 'PENDAPATAN')->values();
        $beban = $rows->where('akun_tipe', 'BEBAN')->values();
        $totP = (float) $pendapatan->sum('saldo');
        $totB = (float) $beban->sum('saldo');
        $sudah = \App\Models\Jurnal::where('jurnal_nomor_tx', 'TX-TUTUP-'.$tahun)->exists();

        return $this->views('pages.laporan.tutup_buku', [
            'model' => $this->model, 'tahun' => $tahun,
            'pendapatan' => $pendapatan, 'beban' => $beban,
            'totP' => round($totP, 2), 'totB' => round($totB, 2),
            'laba' => round($totP - $totB, 2), 'sudah' => $sudah,
        ]);
    }

    public function postTutupBuku(Request $request)
    {
        $tahun = (int) $request->input('tahun', date('Y'));
        $nomor = 'TX-TUTUP-'.$tahun;
        if (\App\Models\Jurnal::where('jurnal_nomor_tx', $nomor)->exists()) {
            return $this->response($this->payload(TOAST_FAILED, 'Tahun '.$tahun.' sudah ditutup.'));
        }

        $rows = $this->saldoAkun($tahun, null);
        $pendapatan = $rows->where('akun_tipe', 'PENDAPATAN')->filter(fn ($r) => (float) $r->saldo != 0)->values();
        $beban = $rows->where('akun_tipe', 'BEBAN')->filter(fn ($r) => (float) $r->saldo != 0)->values();
        if ($pendapatan->isEmpty() && $beban->isEmpty()) {
            return $this->response($this->payload(TOAST_FAILED, 'Tidak ada pendapatan/beban pada tahun '.$tahun.'.'));
        }

        $akunLaba = \App\Models\Akun::where('akun_kode', '3200')->value('akun_id');
        if (! $akunLaba) {
            return $this->response($this->payload(TOAST_FAILED, 'Akun 3200 Laba Ditahan tidak ditemukan.'));
        }

        $jurnal = DB::transaction(function () use ($tahun, $nomor, $pendapatan, $beban, $akunLaba) {
            $j = \App\Models\Jurnal::create([
                'jurnal_tanggal' => $tahun.'-12-31',
                'jurnal_keterangan' => 'Jurnal penutup akhir tahun '.$tahun,
                'jurnal_kategori' => 'PENUTUP',
                'jurnal_tipe_tx' => 'PENUTUP',
                'jurnal_nomor_tx' => $nomor,
            ]);
            foreach ($pendapatan as $r) {
                \App\Models\DetailJurnal::create(['detail_jurnal_id' => $j->getKey(), 'detail_akun_id' => $r->akun_id, 'detail_debit' => $r->saldo, 'detail_kredit' => 0]);
                \App\Models\DetailJurnal::create(['detail_jurnal_id' => $j->getKey(), 'detail_akun_id' => $akunLaba, 'detail_debit' => 0, 'detail_kredit' => $r->saldo]);
            }
            foreach ($beban as $r) {
                \App\Models\DetailJurnal::create(['detail_jurnal_id' => $j->getKey(), 'detail_akun_id' => $akunLaba, 'detail_debit' => $r->saldo, 'detail_kredit' => 0]);
                \App\Models\DetailJurnal::create(['detail_jurnal_id' => $j->getKey(), 'detail_akun_id' => $r->akun_id, 'detail_debit' => 0, 'detail_kredit' => $r->saldo]);
            }

            return $j;
        });

        return $this->response($this->payload(TOAST_SUCCESS, $jurnal));
    }
}
