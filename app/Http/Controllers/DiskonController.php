<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Models\Diskon;

class DiskonController extends Controller
{
    use ControllerTrait;

    public function __construct(Diskon $model)
    {
        $this->model = $model::getModel();
    }
}
