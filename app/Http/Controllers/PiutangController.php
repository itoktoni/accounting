<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Http\Requests\GeneralRequest;
use App\Models\Akun;
use App\Models\BayarPiutang;
use App\Models\DetailJurnal;
use App\Models\Jurnal;
use App\Models\Piutang;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PiutangController extends Controller
{
    use ControllerTrait;

    public function __construct(Piutang $model)
    {
        $this->model = $model::getModel();
    }

    public function getRiwayat(GeneralRequest $request, $id)
    {
        $record = $this->model->findOrFail($id);

        return $this->views($this->template(), [
            "model" => $record,
            "riwayat" => $record->hasBayar()->orderBy("bayar_tanggal")->get(),
        ]);
    }

    public function postBayar(GeneralRequest $request, $id)
    {
        $piutang = $this->model->findOrFail($id);
        $sisa = (float) $piutang->piutang_jumlah - (float) $piutang->piutang_terbayar;

        $data = $request->validate([
            "bayar_tanggal" => "required|date",
            "bayar_jumlah" => "required|numeric|min:1|max:".$sisa,
            "bayar_catatan" => "nullable|string",
        ]);

        DB::transaction(function () use ($piutang, $data) {
            BayarPiutang::create([
                "bayar_piutang_id" => $piutang->piutang_id,
                "bayar_tanggal" => $data["bayar_tanggal"],
                "bayar_jumlah" => $data["bayar_jumlah"],
                "bayar_catatan" => $data["bayar_catatan"] ?? null,
                "bayar_jurnal_id" => $this->buatJurnalPelunasan($piutang, (float) $data["bayar_jumlah"]),
            ]);

            $terbayar = (float) $piutang->piutang_terbayar + (float) $data["bayar_jumlah"];
            $piutang->update([
                "piutang_terbayar" => $terbayar,
                "piutang_status" => $terbayar >= (float) $piutang->piutang_jumlah ? "LUNAS" : "BELUM LUNAS",
            ]);
        });

        return $this->response($this->payload(TOAST_SUCCESS, $piutang->fresh()));
    }

    // Jurnal D Kas K Piutang 1120 — hanya bila tabel jurnal tersedia.
    protected function buatJurnalPelunasan(Piutang $piutang, float $jumlah): ?int
    {
        try {
            $kas = Akun::where('akun_kode', '1100')->value('akun_id');
            $accPiutang = Akun::where('akun_kode', '1120')->value('akun_id');
            if (! $kas || ! $accPiutang) {
                return null;
            }
            $jurnal = Jurnal::create([
                'jurnal_tanggal' => now()->toDateString(),
                'jurnal_keterangan' => 'Pelunasan piutang '.$piutang->piutang_pelanggan.' #'.$piutang->piutang_id,
                'jurnal_kategori' => 'OPERASIONAL',
                'jurnal_tipe_tx' => 'PENERIMAAN',
                'jurnal_nomor_tx' => 'TX-BP-'.date('Ym').'-'.$piutang->piutang_id.'-'.time(),
                'jurnal_pihak' => $piutang->piutang_pelanggan,
            ]);
            DetailJurnal::create(['detail_jurnal_id' => $jurnal->getKey(), 'detail_akun_id' => $kas, 'detail_debit' => $jumlah, 'detail_kredit' => 0]);
            DetailJurnal::create(['detail_jurnal_id' => $jurnal->getKey(), 'detail_akun_id' => $accPiutang, 'detail_debit' => 0, 'detail_kredit' => $jumlah]);

            return (int) $jurnal->getKey();
        } catch (\Throwable) {
            return null;
        }
    }
}
