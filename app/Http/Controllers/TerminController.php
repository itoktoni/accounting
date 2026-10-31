<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Models\Termin;

class TerminController extends Controller
{
    use ControllerTrait;

    public function __construct(Termin $model)
    {
        $this->model = $model::getModel();
    }
}
