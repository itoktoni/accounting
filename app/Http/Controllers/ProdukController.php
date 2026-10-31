<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Models\Produk;

class ProdukController extends Controller
{
    use ControllerTrait {
        getData as traitGetData;
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
}
