<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\GeneralRequest;
use App\Models\Akun;
use App\Models\Jurnal;
use Illuminate\Support\Facades\DB;

class JurnalController extends Controller
{
    use ControllerTrait;

    public function __construct(Jurnal $model)
    {
        $this->model = $model::getModel();
    }

    protected function akunOptions(): array
    {
        return Akun::orderBy('akun_kode')->get()->mapWithKeys(function ($a) {
            return [$a->akun_id => $a->akun_kode.' - '.$a->akun_nama];
        })->toArray();
    }

    protected function share($data = [])
    {
        return array_merge([
            'model' => $this->model,
            'akunOptions' => $this->akunOptions(),
        ], $data);
    }

    protected function getData()
    {
        return $this->model->with('hasDetails.hasAkun')->filter()->sort();
    }

    public function getCreate(GeneralRequest $request)
    {
        return $this->views($this->template(), [
            'model' => $this->model,
        ]);
    }

    public function getUpdate(GeneralRequest $request, $id)
    {
        $data = $this->model->with('hasDetails.hasAkun')->findOrFail($id);

        return $this->views($this->template(), [
            'model' => $data,
        ]);
    }

    public function postCreate(GeneralRequest $request)
    {
        [$header, $lines] = $this->validateJurnal($request);

        try {
            $jurnal = DB::transaction(function () use ($header, $lines) {
                if (empty($header['jurnal_nomor_tx'])) {
                    $header['jurnal_nomor_tx'] = $this->generateNomorTx($header['jurnal_tanggal']);
                }

                $jurnal = Jurnal::create($header);

                foreach ($lines as $line) {
                    $jurnal->hasDetails()->create($line);
                }

                return $jurnal;
            });
        } catch (\Throwable $th) {
            return $this->response(['code' => 422, 'status' => false, 'message' => TOAST_FAILED, 'data' => $th->getMessage()]);
        }

        return $this->response(['code' => 200, 'status' => true, 'message' => TOAST_SUCCESS, 'data' => $jurnal]);
    }

    public function postUpdate(GeneralRequest $request, $id)
    {
        [$header, $lines] = $this->validateJurnal($request, $id);

        try {
            $jurnal = DB::transaction(function () use ($id, $header, $lines) {
                $jurnal = $this->model->findOrFail($id);
                $jurnal->update($header);
                $jurnal->hasDetails()->delete();

                foreach ($lines as $line) {
                    $jurnal->hasDetails()->create($line);
                }

                return $jurnal;
            });
        } catch (\Throwable $th) {
            return $this->response(['code' => 422, 'status' => false, 'message' => TOAST_FAILED, 'data' => $th->getMessage()]);
        }

        return $this->response(['code' => 200, 'status' => true, 'message' => TOAST_SUCCESS, 'data' => $jurnal]);
    }

    private function validateJurnal(GeneralRequest $request, $ignoreId = null): array
    {
        $header = $request->validate([
            'jurnal_tanggal' => 'required|date',
            'jurnal_keterangan' => 'required|string',
            'jurnal_referensi' => 'nullable|string|max:255',
            'jurnal_kategori' => 'nullable|string|max:100',
            'jurnal_tipe_tx' => 'nullable|string|max:50',
            'jurnal_nomor_tx' => 'nullable|string|max:100|unique:jurnal,jurnal_nomor_tx'.($ignoreId ? ','.$ignoreId.',jurnal_id' : ''),
            'jurnal_pihak' => 'nullable|string|max:255',
            'jurnal_proyek_id' => 'nullable|integer',
            'lines' => 'required|array|min:2',
            'lines.*.detail_akun_id' => 'required|integer|exists:akun,akun_id',
            'lines.*.detail_debit' => 'nullable|numeric|min:0',
            'lines.*.detail_kredit' => 'nullable|numeric|min:0',
        ]);

        $lines = collect($header['lines'] ?? [])->map(function ($l) {
            return [
                'detail_akun_id' => (int) $l['detail_akun_id'],
                'detail_debit' => (float) ($l['detail_debit'] ?? 0),
                'detail_kredit' => (float) ($l['detail_kredit'] ?? 0),
            ];
        })->filter(fn ($l) => $l['detail_debit'] > 0 || $l['detail_kredit'] > 0)->values();

        if ($lines->count() < 2) {
            abort(422, 'Jurnal minimal 2 baris dengan nominal debit/kredit.');
        }

        $debit = $lines->sum('detail_debit');
        $kredit = $lines->sum('detail_kredit');

        if ($debit <= 0 || abs($debit - $kredit) > 0.01) {
            abort(422, 'Jurnal tidak balance: total debit ('.$debit.') harus sama dengan total kredit ('.$kredit.').');
        }

        unset($header['lines']);
        $header['jurnal_kategori'] = $header['jurnal_kategori'] ?? 'OPERASIONAL';
        $header['jurnal_tipe_tx'] = $header['jurnal_tipe_tx'] ?? 'JURNAL';
        $header['jurnal_pihak'] = $header['jurnal_pihak'] ?? '';

        return [$header, $lines->toArray()];
    }

    private function generateNomorTx(string $tanggal): string
    {
        $prefix = 'TX-'.date('Ym', strtotime($tanggal)).'-';
        $count = Jurnal::where('jurnal_nomor_tx', 'like', $prefix.'%')->count() + 1;

        return $prefix.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }
}
