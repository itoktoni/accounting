<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Http\Requests\GeneralRequest;
use App\Models\PosSale;
use App\Models\PosShift;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PosSaleController extends Controller
{
    use ControllerTrait;

    public function __construct(PosSale $model)
    {
        $this->model = $model::getModel();
    }

    public function getKasir(GeneralRequest $request)
    {
        $products = collect();
        if (class_exists(\App\Models\Produk::class) && Schema::hasTable((new \App\Models\Produk)->getTable())) {
            $products = \App\Models\Produk::query()->where('produk_aktif', 1)->limit(50)->get();
        }

        $shifts = PosShift::query()->where('shift_status', 'OPEN')->limit(20)->get();

        return $this->views($this->template('kasir', 'possale'), [
            'products' => $products,
            'shifts' => $shifts,
        ]);
    }

    public function postCheckout(GeneralRequest $request)
    {
        $data = $request->validate([
            'sale_shift_id' => 'nullable|integer',
            'sale_customer' => 'nullable|string|max:255',
            'sale_metode_bayar' => 'nullable|string|max:20',
            'sale_akun_kas' => 'nullable|string|max:20',
            'sale_total' => 'required|numeric|min:0',
            'sale_bayar' => 'required|numeric|min:0',
            'items' => 'nullable|array',
            'items.*.product_id' => 'nullable|integer',
            'items.*.qty' => 'nullable|numeric|min:0',
        ]);

        $sale = DB::transaction(function () use ($data) {
            $sale = $this->model->create([
                'sale_nomor_struk' => 'POS-'.date('Ymd').'-'.strtoupper(unicString(6)),
                'sale_tanggal' => now()->toDateString(),
                'sale_shift_id' => $data['sale_shift_id'] ?? null,
                'sale_customer' => $data['sale_customer'] ?? '',
                'sale_metode_bayar' => $data['sale_metode_bayar'] ?? 'TUNAI',
                'sale_akun_kas' => $data['sale_akun_kas'] ?? '1100',
                'sale_total' => $data['sale_total'],
                'sale_bayar' => $data['sale_bayar'],
                'sale_kembalian' => $data['sale_bayar'] - $data['sale_total'],
                'sale_status' => 'SELESAI',
            ]);

            // Kurangi stok produk bila modul produk tersedia.
            if (! empty($data['items']) && class_exists(\App\Models\Produk::class)) {
                try {
                    $productModel = new \App\Models\Produk;
                    if (Schema::hasTable($productModel->getTable())) {
                        foreach ($data['items'] as $item) {
                            if (empty($item['product_id']) || empty($item['qty'])) {
                                continue;
                            }
                            $product = \App\Models\Produk::query()->find($item['product_id']);
                            if (! $product) {
                                continue;
                            }
                            $product->decrement('produk_stok', $item['qty']);
                        }
                    }
                } catch (\Throwable $th) {
                    // stok best-effort; penjualan tetap sah
                }
            }

            // Jurnal penjualan best-effort bila modul jurnal tersedia.
            if (class_exists(\App\Models\Jurnal::class)) {
                try {
                    $jurnalModel = new \App\Models\Jurnal;
                    if (Schema::hasTable($jurnalModel->getTable())) {
                        $jurnal = \App\Models\Jurnal::create([
                            'jurnal_tanggal' => $sale->sale_tanggal,
                            'jurnal_keterangan' => 'Penjualan '.$sale->sale_nomor_struk,
                            'jurnal_tipe_tx' => 'PENJUALAN',
                            'jurnal_nomor_tx' => $sale->sale_nomor_struk,
                        ]);
                        $sale->update(['sale_jurnal_id' => $jurnal->getKey()]);
                    }
                } catch (\Throwable $th) {
                    // abaikan bila skema jurnal berbeda
                }
            }

            return $sale;
        });

        return $this->response(['status' => true, 'message' => TOAST_SUCCESS, 'data' => $sale]);
    }
}
