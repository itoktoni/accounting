<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Enums\AkunTipeEnum;
use App\Http\Controllers\Controller;
use App\Models\Akun;

class AkunController extends Controller
{
    use ControllerTrait;

    public function __construct(Akun $model)
    {
        $this->model = $model::getModel();
    }

    protected function share($data = [])
    {
        return array_merge([
            'model' => $this->model,
            'tipeOptions' => AkunTipeEnum::getOptions(),
        ], $data);
    }

    public function getTable(\App\Http\Requests\GeneralRequest $request)
    {
        $data = $this->model
            ->withSum('hasDetails as total_debit', 'detail_debit')
            ->withSum('hasDetails as total_kredit', 'detail_kredit')
            ->orderBy('akun_kode')
            ->get();

        return $this->views($this->template(), [
            'data' => $data,
            'fields' => $this->getFields(),
        ]);
    }
}
