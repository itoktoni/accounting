<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Models\Anggaran;

class AnggaranController extends Controller
{
    use ControllerTrait;

    public function __construct(Anggaran $model)
    {
        $this->model = $model::getModel();
    }
}
