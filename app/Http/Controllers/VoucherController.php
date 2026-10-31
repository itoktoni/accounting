<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Models\Voucher;

class VoucherController extends Controller
{
    use ControllerTrait;

    public function __construct(Voucher $model)
    {
        $this->model = $model::getModel();
    }
}
