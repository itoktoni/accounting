<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Http\Requests\GeneralRequest;
use App\Models\DetailJurnal;
use App\Models\Invoice;
use App\Models\Jurnal;
use App\Models\Penawaran;
use App\Models\Proyek;
use Illuminate\Support\Facades\DB;

class ProyekController extends Controller
{
    use ControllerTrait;

    public function __construct(Proyek $model)
    {
        $this->model = $model::getModel();
    }

    protected function share($data = [])
    {
        $default = [
            'model' => $this->model,
            'status' => ['AKTIF' => 'AKTIF', 'SELESAI' => 'SELESAI', 'BATAL' => 'BATAL'],
        ];

        return array_merge($default, $data);
    }

    public function getDetail(GeneralRequest $request, $id)
    {
        $proyek = $this->model->findOrFail($id);

        $jurnals = Jurnal::where('jurnal_proyek_id', $proyek->getKey())
            ->withSum('hasDetails as total_debit', 'detail_debit')
            ->withSum('hasDetails as total_kredit', 'detail_kredit')
            ->orderBy('jurnal_tanggal')
            ->get();

        $rincian = DetailJurnal::whereIn('detail_jurnal_id', $jurnals->pluck('jurnal_id'))
            ->join('akun as a', 'a.akun_id', '=', 'detail_jurnal.detail_akun_id')
            ->select('a.akun_kode', 'a.akun_nama', DB::raw('SUM(detail_debit) as debit'), DB::raw('SUM(detail_kredit) as kredit'))
            ->groupBy('a.akun_kode', 'a.akun_nama')
            ->orderBy('a.akun_kode')
            ->get();

        $invoices = Invoice::where('invoice_pelanggan', $proyek->proyek_pelanggan)->orderBy('invoice_tanggal')->limit(50)->get();
        $penawarans = Penawaran::where('penawaran_pelanggan', $proyek->proyek_pelanggan)->orderBy('penawaran_tanggal')->limit(50)->get();
        $estimasis = \App\Models\ProyekEstimasi::where('estimasi_proyek_id', $proyek->getKey())->orderBy('estimasi_id')->get();
        $estimasiMasuks = \App\Models\ProyekEstimasiPendapatan::where('estimasi_masuk_proyek_id', $proyek->getKey())->orderBy('estimasi_masuk_id')->get();

        return $this->views($this->template(), [
            'model' => $proyek,
            'jurnals' => $jurnals,
            'rincian' => $rincian,
            'totalDebit' => (float) $jurnals->sum('total_debit'),
            'totalKredit' => (float) $jurnals->sum('total_kredit'),
            'invoices' => $invoices,
            'penawarans' => $penawarans,
            'estimasis' => $estimasis,
            'totalEstimasi' => (float) $estimasis->sum('estimasi_subtotal'),
            'estimasiMasuks' => $estimasiMasuks,
            'totalEstimasiMasuk' => (float) $estimasiMasuks->sum('estimasi_masuk_subtotal'),
            'bebanOptions' => \App\Models\Akun::where('akun_tipe', 'BEBAN')->orderBy('akun_kode')->get()->mapWithKeys(fn ($a) => [$a->akun_id => $a->akun_kode.' - '.$a->akun_nama])->toArray(),
            'pendapatanOptions' => \App\Models\Akun::where('akun_tipe', 'PENDAPATAN')->orderBy('akun_kode')->get()->mapWithKeys(fn ($a) => [$a->akun_id => $a->akun_kode.' - '.$a->akun_nama])->toArray(),
            'kasOptions' => \App\Models\Akun::where(fn ($q) => $q->where('akun_is_rekening', true)->orWhereIn('akun_kode', ['1100', '1110']))->orderBy('akun_kode')->get()->mapWithKeys(fn ($a) => [$a->akun_id => $a->akun_kode.' - '.$a->akun_nama])->toArray(),
        ]);
    }

    private function catatTransaksi(int $proyekId, array $data, string $tipe): Jurnal
    {
        return DB::transaction(function () use ($proyekId, $data, $tipe) {
            $proyek = $this->model->findOrFail($proyekId);
            $jurnal = Jurnal::create([
                'jurnal_tanggal' => $data['tanggal'],
                'jurnal_keterangan' => '['.$proyek->proyek_kode.'] '.$data['keterangan'],
                'jurnal_kategori' => 'PROYEK',
                'jurnal_tipe_tx' => $tipe,
                'jurnal_nomor_tx' => 'TX-PRJ-'.date('Ym').'-'.strtoupper(unicString(4)),
                'jurnal_pihak' => $proyek->proyek_pelanggan,
                'jurnal_proyek_id' => $proyek->getKey(),
            ]);
            if ($tipe === 'PENGELUARAN') {
                // D Beban / K Kas
                DetailJurnal::create(['detail_jurnal_id' => $jurnal->getKey(), 'detail_akun_id' => $data['akun_lawan'], 'detail_debit' => $data['nominal'], 'detail_kredit' => 0]);
                DetailJurnal::create(['detail_jurnal_id' => $jurnal->getKey(), 'detail_akun_id' => $data['akun_kas'], 'detail_debit' => 0, 'detail_kredit' => $data['nominal']]);
            } else {
                // D Kas / K Pendapatan
                DetailJurnal::create(['detail_jurnal_id' => $jurnal->getKey(), 'detail_akun_id' => $data['akun_kas'], 'detail_debit' => $data['nominal'], 'detail_kredit' => 0]);
                DetailJurnal::create(['detail_jurnal_id' => $jurnal->getKey(), 'detail_akun_id' => $data['akun_lawan'], 'detail_debit' => 0, 'detail_kredit' => $data['nominal']]);
            }

            return $jurnal;
        });
    }

    public function postPengeluaran(GeneralRequest $request, $id)
    {
        $data = $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'required|string|max:255',
            'akun_lawan' => 'required|integer|exists:akun,akun_id',
            'akun_kas' => 'required|integer|exists:akun,akun_id',
            'nominal' => 'required|numeric|min:1',
        ]);
        $jurnal = $this->catatTransaksi((int) $id, $data, 'PENGELUARAN');

        return $this->response($this->payload(TOAST_SUCCESS, $jurnal));
    }

    public function postTambahEstimasi(GeneralRequest $request, $id)
    {
        $proyek = $this->model->findOrFail($id);
        $data = $request->validate([
            'estimasi_deskripsi' => 'required|string|max:255',
            'estimasi_qty' => 'required|numeric|min:0',
            'estimasi_satuan' => 'nullable|string|max:20',
            'estimasi_harga' => 'required|numeric|min:0',
        ]);
        $estimasi = \App\Models\ProyekEstimasi::create(array_merge($data, [
            'estimasi_proyek_id' => $proyek->getKey(),
            'estimasi_satuan' => $data['estimasi_satuan'] ?? 'paket',
            'estimasi_subtotal' => round((float) $data['estimasi_qty'] * (float) $data['estimasi_harga'], 2),
        ]));

        return $this->response($this->payload(TOAST_SUCCESS, $estimasi));
    }

    public function postHapusEstimasi(GeneralRequest $request, $id, $estimasiId)
    {
        $proyek = $this->model->findOrFail($id);
        $estimasi = \App\Models\ProyekEstimasi::where('estimasi_proyek_id', $proyek->getKey())->findOrFail($estimasiId);
        $estimasi->delete();

        return $this->response($this->payload(TOAST_SUCCESS, $estimasi));
    }

    public function postTambahEstimasiMasuk(GeneralRequest $request, $id)
    {
        $proyek = $this->model->findOrFail($id);
        $data = $request->validate([
            'estimasi_masuk_deskripsi' => 'required|string|max:255',
            'estimasi_masuk_qty' => 'required|numeric|min:0',
            'estimasi_masuk_satuan' => 'nullable|string|max:20',
            'estimasi_masuk_harga' => 'required|numeric|min:0',
        ]);
        $estimasi = \App\Models\ProyekEstimasiPendapatan::create(array_merge($data, [
            'estimasi_masuk_proyek_id' => $proyek->getKey(),
            'estimasi_masuk_satuan' => $data['estimasi_masuk_satuan'] ?? 'paket',
            'estimasi_masuk_subtotal' => round((float) $data['estimasi_masuk_qty'] * (float) $data['estimasi_masuk_harga'], 2),
        ]));

        return $this->response($this->payload(TOAST_SUCCESS, $estimasi));
    }

    public function postHapusEstimasiMasuk(GeneralRequest $request, $id, $estimasiId)
    {
        $proyek = $this->model->findOrFail($id);
        $estimasi = \App\Models\ProyekEstimasiPendapatan::where('estimasi_masuk_proyek_id', $proyek->getKey())->findOrFail($estimasiId);
        $estimasi->delete();

        return $this->response($this->payload(TOAST_SUCCESS, $estimasi));
    }

    public function getCetak(GeneralRequest $request, $id)
    {
        $proyek = $this->model->findOrFail($id);
        $jurnals = \App\Models\Jurnal::where('jurnal_proyek_id', $proyek->getKey())
            ->withSum('hasDetails as total_debit', 'detail_debit')
            ->withSum('hasDetails as total_kredit', 'detail_kredit')
            ->orderBy('jurnal_tanggal')->get();
        $rincian = \App\Models\DetailJurnal::whereIn('detail_jurnal_id', $jurnals->pluck('jurnal_id'))
            ->join('akun as a', 'a.akun_id', '=', 'detail_jurnal.detail_akun_id')
            ->select('a.akun_kode', 'a.akun_nama', DB::raw('SUM(detail_debit) as debit'), DB::raw('SUM(detail_kredit) as kredit'))
            ->groupBy('a.akun_kode', 'a.akun_nama')->orderBy('a.akun_kode')->get();
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.proyek', [
            'proyek' => $proyek,
            'estimasis' => $proyek->hasEstimasi()->orderBy('estimasi_id')->get(),
            'estimasiMasuks' => $proyek->hasEstimasiMasuk()->orderBy('estimasi_masuk_id')->get(),
            'jurnals' => $jurnals,
            'rincian' => $rincian,
            'co' => config('website'),
        ]);

        return $pdf->stream('proyek-'.$proyek->proyek_kode.'.pdf');
    }

    public function postPemasukan(GeneralRequest $request, $id)
    {
        $data = $request->validate([
            'tanggal' => 'required|date',
            'keterangan' => 'required|string|max:255',
            'akun_lawan' => 'required|integer|exists:akun,akun_id',
            'akun_kas' => 'required|integer|exists:akun,akun_id',
            'nominal' => 'required|numeric|min:1',
        ]);
        $jurnal = $this->catatTransaksi((int) $id, $data, 'PEMASUKAN');

        return $this->response($this->payload(TOAST_SUCCESS, $jurnal));
    }
}
