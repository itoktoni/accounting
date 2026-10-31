<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Http\Requests\GeneralRequest;
use App\Models\Akun;
use App\Models\BayarHutang;
use App\Models\DetailJurnal;
use App\Models\Hutang;
use App\Models\Jurnal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HutangController extends Controller
{
    use ControllerTrait;

    public function __construct(Hutang $model)
    {
        $this->model = $model::getModel();
    }

    public function getRiwayat(GeneralRequest $request, $id)
    {
        $record = $this->model->findOrFail($id);

        return $this->views($this->template(), [
            "model" => $record,
            "riwayat" => $record->hasBayar()->orderBy("bh_tanggal")->get(),
        ]);
    }

    public function postBayar(GeneralRequest $request, $id)
    {
        $hutang = $this->model->findOrFail($id);
        $sisa = (float) $hutang->hutang_jumlah - (float) $hutang->hutang_terbayar;

        $data = $request->validate([
            "bh_tanggal" => "required|date",
            "bh_jumlah" => "required|numeric|min:1|max:".$sisa,
            "bh_catatan" => "nullable|string",
        ]);

        DB::transaction(function () use ($hutang, $data) {
            BayarHutang::create([
                "bh_hutang_id" => $hutang->hutang_id,
                "bh_tanggal" => $data["bh_tanggal"],
                "bh_jumlah" => $data["bh_jumlah"],
                "bh_catatan" => $data["bh_catatan"] ?? null,
                "bh_jurnal_id" => $this->buatJurnalPelunasan($hutang, (float) $data["bh_jumlah"]),
            ]);

            $terbayar = (float) $hutang->hutang_terbayar + (float) $data["bh_jumlah"];
            $hutang->update([
                "hutang_terbayar" => $terbayar,
                "hutang_status" => $terbayar >= (float) $hutang->hutang_jumlah ? "LUNAS" : "BELUM LUNAS",
            ]);
        });

        return $this->response($this->payload(TOAST_SUCCESS, $hutang->fresh()));
    }

    // Jurnal D Hutang 2100 K Kas — hanya bila tabel jurnal tersedia.
    protected function buatJurnalPelunasan(Hutang $hutang, float $jumlah): ?int
    {
        try {
            $accHutang = Akun::where('akun_kode', '2100')->value('akun_id');
            $kas = Akun::where('akun_kode', '1100')->value('akun_id');
            if (! $accHutang || ! $kas) {
                return null;
            }
            $jurnal = Jurnal::create([
                'jurnal_tanggal' => now()->toDateString(),
                'jurnal_keterangan' => 'Pelunasan hutang '.$hutang->hutang_pemasok.' #'.$hutang->hutang_id,
                'jurnal_kategori' => 'OPERASIONAL',
                'jurnal_tipe_tx' => 'PENGELUARAN',
                'jurnal_nomor_tx' => 'TX-BH-'.date('Ym').'-'.$hutang->hutang_id.'-'.time(),
                'jurnal_pihak' => $hutang->hutang_pemasok,
            ]);
            DetailJurnal::create(['detail_jurnal_id' => $jurnal->getKey(), 'detail_akun_id' => $accHutang, 'detail_debit' => $jumlah, 'detail_kredit' => 0]);
            DetailJurnal::create(['detail_jurnal_id' => $jurnal->getKey(), 'detail_akun_id' => $kas, 'detail_debit' => 0, 'detail_kredit' => $jumlah]);

            return (int) $jurnal->getKey();
        } catch (\Throwable) {
            return null;
        }
    }
}
