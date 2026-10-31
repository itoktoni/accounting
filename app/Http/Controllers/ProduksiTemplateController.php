<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Models\ProduksiTemplate;

class ProduksiTemplateController extends Controller
{
    use ControllerTrait;

    public function __construct(ProduksiTemplate $model)
    {
        $this->model = $model::getModel();
    }

    protected function share($data = [])
    {
        $default = [
            'model' => $this->model,
            'produkOptions' => \App\Models\Produk::getOptions(),
        ];

        return array_merge($default, $data);
    }
}
