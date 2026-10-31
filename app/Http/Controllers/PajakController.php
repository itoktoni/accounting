<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Models\Pajak;

class PajakController extends Controller
{
    use ControllerTrait;

    public function __construct(Pajak $model)
    {
        $this->model = $model::getModel();
    }
}
