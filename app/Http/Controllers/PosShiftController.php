<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Http\Requests\GeneralRequest;
use App\Models\PosShift;

class PosShiftController extends Controller
{
    use ControllerTrait;

    public function __construct(PosShift $model)
    {
        $this->model = $model::getModel();
    }

    public function postOpen(GeneralRequest $request)
    {
        $data = $request->validate([
            'shift_saldo_awal' => 'nullable|numeric',
            'shift_catatan' => 'nullable|string|max:255',
        ]);

        $user = $request->user();

        $this->model->create([
            'shift_user_id' => $user?->getKey(),
            'shift_username' => $user?->name ?? $user?->email ?? 'kasir',
            'shift_dibuka' => now(),
            'shift_saldo_awal' => $data['shift_saldo_awal'] ?? 0,
            'shift_status' => 'OPEN',
            'shift_catatan' => $data['shift_catatan'] ?? '',
        ]);

        return $this->response(['status' => true, 'message' => TOAST_SUCCESS, 'data' => null]);
    }

    public function postClose(GeneralRequest $request, $id)
    {
        $data = $request->validate([
            'shift_kas_sistem' => 'nullable|numeric',
            'shift_kas_fisik' => 'required|numeric',
        ]);

        $shift = $this->model->findOrFail($id);
        $kasSistem = $data['shift_kas_sistem'] ?? $shift->shift_kas_sistem;
        $kasFisik = $data['shift_kas_fisik'];

        $shift->update([
            'shift_ditutup' => now(),
            'shift_kas_sistem' => $kasSistem,
            'shift_kas_fisik' => $kasFisik,
            'shift_selisih' => $kasFisik - ($shift->shift_saldo_awal + $kasSistem),
            'shift_status' => 'CLOSED',
        ]);

        return $this->response(['status' => true, 'message' => TOAST_SUCCESS, 'data' => $shift]);
    }
}
