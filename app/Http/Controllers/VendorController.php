<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Models\Vendor;

class VendorController extends Controller
{
    use ControllerTrait;

    public function __construct(Vendor $model)
    {
        $this->model = $model::getModel();
    }
}
