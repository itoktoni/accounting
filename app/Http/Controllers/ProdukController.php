<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Http\Requests\GeneralRequest;
use App\Models\Produk;

class ProdukController extends Controller
{
    use ControllerTrait {
        getData as traitGetData;
        postCreate as traitPostCreate;
    }

    public function __construct(Produk $model)
    {
        $this->model = $model::getModel();
    }

    protected function share($data = [])
    {
        $default = [
            'model' => $this->model,
            'kategoriOptions' => $this->model->distinct()->whereNotNull('produk_kategori')->pluck('produk_kategori', 'produk_kategori')->toArray(),
        ];

        return array_merge($default, $data);
    }

    protected function getData()
    {
        return $this->traitGetData()->where('produk.produk_aktif', 1);
    }

    public function postCreate(GeneralRequest $request)
    {
        if (empty($request->input('produk_kode'))) {
            $request->merge(['produk_kode' => 'BRG-'.strtoupper(unicString(6))]);
        }
        if (empty($request->input('produk_satuan'))) {
            $request->merge(['produk_satuan' => 'pcs']);
        }
        $request->merge(['produk_aktif' => true]);

        return $this->traitPostCreate($request);
    }
}
