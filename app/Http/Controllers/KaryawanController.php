<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Models\Karyawan;

class KaryawanController extends Controller
{
    use ControllerTrait;

    public function __construct(Karyawan $model)
    {
        $this->model = $model::getModel();
    }
}
