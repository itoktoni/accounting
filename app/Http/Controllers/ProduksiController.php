<?php

namespace App\Http\Controllers;

use App\Actions\CreateAction;
use App\Actions\UpdateAction;
use App\Concerns\ControllerTrait;
use App\Http\Requests\GeneralRequest;
use App\Models\PergerakanStok;
use App\Models\Produk;
use App\Models\Produksi;
use App\Models\ProduksiBahan;
use Illuminate\Support\Facades\DB;

class ProduksiController extends Controller
{
    use ControllerTrait;

    public function __construct(Produksi $model)
    {
        $this->model = $model::getModel();
    }

    protected function share($data = [])
    {
        $default = [
            'model' => $this->model,
            'produkOptions' => Produk::getOptions(),
        ];

        return array_merge($default, $data);
    }

    public function postCreate(GeneralRequest $request)
    {
        $this->mergeHpp($request);

        DB::beginTransaction();
        try {
            $response = CreateAction::run($request, $this->model);
            if ($response['status']) {
                $this->catatStok($response['data'], $request);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $response = ['code' => 500, 'status' => false, 'message' => TOAST_FAILED, 'data' => $e->getMessage()];
        }

        return $this->response($response);
    }

    public function postUpdate(GeneralRequest $request, $id)
    {
        $this->mergeHpp($request);

        DB::beginTransaction();
        try {
            $response = UpdateAction::run($request, $id, $this->model);
            if ($response['status']) {
                $this->balikkanStok($id);
                $this->catatStok($response['data'], $request);
            }
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $response = ['code' => 500, 'status' => false, 'message' => TOAST_FAILED, 'data' => $e->getMessage()];
        }

        return $this->response($response);
    }

    // Hitung total HPP dari bahan + biaya tambahan, merge ke request sebelum validasi.
    private function mergeHpp(GeneralRequest $request): void
    {
        $total = (int) $request->input('produksi_biaya_tambahan', 0);
        foreach ($request->input('bahan', []) as $row) {
            $total += (int) ($row['bahan_qty'] ?? 0) * (int) ($row['bahan_harga'] ?? 0);
        }
        $hasil = max(1, (int) $request->input('produksi_qty_hasil', 1));
        $request->merge([
            'produksi_total_hpp' => $total,
            'produksi_hpp_per_unit' => intdiv($total, $hasil),
        ]);
    }

    // Kurangi stok bahan (KELUAR) + tambah stok produk jadi (PRODUKSI).
    private function catatStok(Produksi $produksi, GeneralRequest $request): void
    {
        foreach ($request->input('bahan', []) as $row) {
            if (empty($row['bahan_produk_id']) || empty($row['bahan_qty'])) {
                continue;
            }
            $qty = (int) $row['bahan_qty'];
            $harga = (int) ($row['bahan_harga'] ?? 0);
            ProduksiBahan::create([
                'bahan_produksi_id' => $produksi->produksi_id,
                'bahan_produk_id' => $row['bahan_produk_id'],
                'bahan_qty' => $qty,
                'bahan_harga' => $harga,
                'bahan_subtotal' => $qty * $harga,
            ]);
            Produk::where('produk_id', $row['bahan_produk_id'])->decrement('produk_stok', $qty);
            PergerakanStok::create([
                'stok_produk_id' => $row['bahan_produk_id'],
                'stok_tanggal' => $produksi->produksi_tanggal,
                'stok_jenis' => 'KELUAR',
                'stok_qty' => -$qty,
                'stok_harga' => $harga,
                'stok_keterangan' => 'Produksi #'.$produksi->produksi_id,
                'stok_jurnal_id' => $produksi->produksi_id,
            ]);
        }

        Produk::where('produk_id', $produksi->produksi_produk_jadi_id)->increment('produk_stok', $produksi->produksi_qty_hasil);
        PergerakanStok::create([
            'stok_produk_id' => $produksi->produksi_produk_jadi_id,
            'stok_tanggal' => $produksi->produksi_tanggal,
            'stok_jenis' => 'PRODUKSI',
            'stok_qty' => $produksi->produksi_qty_hasil,
            'stok_harga' => $produksi->produksi_hpp_per_unit,
            'stok_keterangan' => 'Produksi #'.$produksi->produksi_id,
            'stok_jurnal_id' => $produksi->produksi_id,
        ]);
    }

    // Kembalikan stok sebelum hitung ulang (dipakai saat update).
    private function balikkanStok(int $produksiId): void
    {
        foreach (PergerakanStok::where('stok_jurnal_id', $produksiId)->get() as $gerak) {
            Produk::where('produk_id', $gerak->stok_produk_id)->decrement('produk_stok', $gerak->stok_qty);
        }
        PergerakanStok::where('stok_jurnal_id', $produksiId)->delete();
        ProduksiBahan::where('bahan_produksi_id', $produksiId)->delete();
    }
}
