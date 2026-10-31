<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Http\Requests\GeneralRequest;
use App\Models\Payroll;
use Illuminate\Support\Facades\Schema;

class PayrollController extends Controller
{
    use ControllerTrait;

    public function __construct(Payroll $model)
    {
        $this->model = $model::getModel();
    }

    public function postProses(GeneralRequest $request, $id)
    {
        $payroll = $this->model->findOrFail($id);

        $bersih = ($payroll->payroll_gaji_pokok + $payroll->payroll_tunjangan + $payroll->payroll_bonus) - $payroll->payroll_potongan;
        $payroll->update(['payroll_gaji_bersih' => $bersih]);

        // Jurnal beban gaji best-effort: D Beban Gaji 6100 K Kas/Hutang Gaji.
        if (class_exists(\App\Models\Jurnal::class)) {
            try {
                $jurnalModel = new \App\Models\Jurnal;
                if (Schema::hasTable($jurnalModel->getTable())) {
                    $jurnal = \App\Models\Jurnal::create([
                        'jurnal_tanggal' => now()->toDateString(),
                        'jurnal_keterangan' => 'Gaji '.$payroll->payroll_bulan.'/'.$payroll->payroll_tahun,
                        'jurnal_total' => $bersih,
                    ]);
                    $payroll->update(['payroll_jurnal_id' => $jurnal->getKey()]);
                }
            } catch (\Throwable $th) {
                // abaikan bila skema jurnal berbeda
            }
        }

        return $this->response(['status' => true, 'message' => TOAST_SUCCESS, 'data' => $payroll]);
    }
}
