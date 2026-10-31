<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Http\Requests\GeneralRequest;
use App\Models\AsetTetap;
use Illuminate\Support\Facades\Schema;

class AsetTetapController extends Controller
{
    use ControllerTrait;

    public function __construct(AsetTetap $model)
    {
        $this->model = $model::getModel();
    }

    public function postSusutkan(GeneralRequest $request, $id)
    {
        $aset = $this->model->findOrFail($id);

        $susut = $aset->aset_penyusutan_bulan;
        if (empty($susut) && $aset->aset_masa_pakai > 0) {
            $susut = $aset->aset_harga_beli / $aset->aset_masa_pakai;
        }

        $aset->update([
            'aset_penyusutan_bulan' => $susut,
            'aset_akumulasi' => $aset->aset_akumulasi + $susut,
        ]);

        // Jurnal penyusutan best-effort: D Beban Penyusutan 6130 K Akumulasi 1290.
        if (class_exists(\App\Models\Jurnal::class)) {
            try {
                $jurnalModel = new \App\Models\Jurnal;
                if (Schema::hasTable($jurnalModel->getTable())) {
                    $jurnal = \App\Models\Jurnal::create([
                        'jurnal_tanggal' => now()->toDateString(),
                        'jurnal_keterangan' => 'Penyusutan '.$aset->aset_nama,
                        'jurnal_total' => $susut,
                    ]);
                    $aset->update(['aset_jurnal_id' => $jurnal->getKey()]);
                }
            } catch (\Throwable $th) {
                // abaikan bila skema jurnal berbeda
            }
        }

        return $this->response(['status' => true, 'message' => TOAST_SUCCESS, 'data' => $aset]);
    }
}
