<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Http\Requests\GeneralRequest;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PurchaseOrderController extends Controller
{
    use ControllerTrait {
        postCreate as traitPostCreate;
        postUpdate as traitPostUpdate;
    }

    public function __construct(PurchaseOrder $model)
    {
        $this->model = $model::getModel();
    }

    protected function share($data = [])
    {
        $default = [
            'model' => $this->model,
            'status' => ['DRAFT' => 'DRAFT', 'DITERIMA' => 'DITERIMA', 'BATAL' => 'BATAL'],
            'vendorOptions' => \App\Models\Vendor::orderBy('vendor_nama')->pluck('vendor_nama', 'vendor_nama')->toArray(),
            'produkMap' => \App\Models\Produk::where('produk_aktif', 1)->orderBy('produk_nama')->get(['produk_nama', 'produk_harga_beli'])->mapWithKeys(fn ($p) => [$p->produk_nama => (float) $p->produk_harga_beli])->toArray(),
            'diskonOpts' => \App\Models\Diskon::where('diskon_aktif', true)->orderBy('diskon_persen')->get()->map(fn ($d) => ['v' => (float) $d->diskon_persen, 'l' => $d->diskon_nama])->values()->toArray() ?: [['v' => 0, 'l' => 'Tanpa Diskon'], ['v' => 5, 'l' => 'Diskon 5%'], ['v' => 10, 'l' => 'Diskon 10%'], ['v' => 15, 'l' => 'Diskon 15%'], ['v' => 20, 'l' => 'Diskon 20%'], ['v' => 25, 'l' => 'Diskon 25%'], ['v' => 50, 'l' => 'Diskon 50%'], ['v' => 100, 'l' => 'Diskon 100%']],
        ];

        return array_merge($default, $data);
    }

    public function getCreate(GeneralRequest $request)
    {
        $model = $this->model;
        $model->po_tanggal = now()->toDateString();

        return $this->views($this->template(), [
            'model' => $model,
        ]);
    }

    public function postCreate(GeneralRequest $request)
    {
        $response = $this->traitPostCreate($request);
        if ($response['status'] && $response['data'] instanceof PurchaseOrder) {
            $this->syncItems($response['data'], $request->input('items', []));
        }

        return $this->response($response);
    }

    public function postUpdate(GeneralRequest $request, $id)
    {
        $response = $this->traitPostUpdate($request, $id);
        if ($response['status']) {
            $this->syncItems($this->model->findOrFail($id), $request->input('items', []));
        }

        return $this->response($response);
    }

    private function syncItems(PurchaseOrder $po, array $items): void
    {
        $rows = collect($items)->filter(fn ($r) => ! empty($r['deskripsi']))->values();
        if ($rows->isEmpty()) {
            return;
        }
        $po->hasItems()->delete();
        foreach ($rows as $r) {
            $qty = (float) ($r['qty'] ?? 1);
            $harga = (float) ($r['harga'] ?? 0);
            $diskonPersen = (float) ($r['diskon_persen'] ?? 0);
            $diskonNominal = round($qty * $harga * $diskonPersen / 100, 2);
            $po->hasItems()->create([
                'poi_deskripsi' => $r['deskripsi'],
                'poi_qty' => $qty,
                'poi_satuan' => $r['satuan'] ?? 'pcs',
                'poi_harga_satuan' => $harga,
                'poi_diskon' => $diskonNominal,
                'poi_subtotal' => $qty * $harga - $diskonNominal,
            ]);
        }
    }

    public function getCetak(GeneralRequest $request, $id)
    {
        $po = $this->model->with('hasItems')->findOrFail($id);
        $bruto = 0;
        $diskonItem = 0;
        foreach ($po->hasItems as $it) {
            $line = (float) $it->poi_qty * (float) $it->poi_harga_satuan;
            $bruto += $line;
            $diskonItem += (float) $it->poi_diskon;
        }
        $diskonGlobal = (float) $po->po_diskon;
        $ongkir = (float) $po->po_ongkir;
        $lain = (float) $po->po_biaya_lain;
        $t = [
            'bruto' => round($bruto, 2),
            'diskon_item' => round($diskonItem, 2),
            'diskon_global' => round($diskonGlobal, 2),
            'ongkir' => round($ongkir, 2),
            'biaya_lain' => round($lain, 2),
            'total' => round($bruto - $diskonItem - $diskonGlobal + $ongkir + $lain, 2),
        ];
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.purchaseorder', [
            'po' => $po,
            't' => $t,
            'vendor' => \App\Models\Vendor::where('vendor_nama', $po->po_vendor)->first(),
            'co' => config('website'),
        ]);

        return $pdf->stream($po->po_nomor.'.pdf');
    }

    public function postTerima(GeneralRequest $request, $id)
    {
        try {
            $total = DB::transaction(function () use ($id) {
                $po = $this->model->with('hasItems')->findOrFail($id);

                $subtotal = 0;
                foreach ($po->hasItems as $item) {
                    $line = (float) $item->poi_qty * (float) $item->poi_harga_satuan - (float) $item->poi_diskon;
                    $item->update(['poi_subtotal' => $line]);
                    $subtotal += $line;
                }
                $total = $subtotal - (float) $po->po_diskon + (float) $po->po_ongkir + (float) $po->po_biaya_lain;

                // shortcut: integrasi hutang/jurnal hanya bila modul sibling sudah ada.
                // Upgrade: panggil HutangAction/JurnalAction milik modul A/C saat tersedia.
                $jurnalId = $this->tryCreateJurnal($po, $total);
                $hutangId = $this->tryCreateHutang($po, $total, $jurnalId);

                $po->update(array_filter([
                    'po_status' => 'DITERIMA',
                    'po_hutang_id' => $hutangId,
                    'po_jurnal_id' => $jurnalId,
                ], fn ($v) => $v !== null));

                return $total;
            });

            return $this->response($this->payload(TOAST_SUCCESS, ['total' => $total]));
        } catch (\Throwable $th) {
            return $this->response($this->payload(TOAST_FAILED, $th->getMessage()));
        }
    }

    private function tryCreateHutang(PurchaseOrder $po, float $total, mixed $jurnalId = null): mixed
    {
        try {
            if (! class_exists(\App\Models\Hutang::class) || ! Schema::hasTable('hutang')) {
                return null;
            }

            return \App\Models\Hutang::create([
                'hutang_tanggal' => $po->po_tanggal->format('Y-m-d'),
                'hutang_pemasok' => $po->po_vendor,
                'hutang_keterangan' => 'Terima PO '.$po->po_nomor,
                'hutang_jumlah' => $total,
                'hutang_status' => 'BELUM LUNAS',
                'hutang_akun_kode' => '2100',
                'hutang_jurnal_id' => $jurnalId,
            ])->getKey();
        } catch (\Throwable) {
        }

        return null;
    }

    private function tryCreateJurnal(PurchaseOrder $po, float $total): mixed
    {
        // D Persediaan 1130 / K Hutang 2100, ikut skema modul akuntansi inti.
        try {
            if (! class_exists(\App\Models\Jurnal::class) || ! Schema::hasTable('jurnal')) {
                return null;
            }
            $jurnal = \App\Models\Jurnal::create([
                'jurnal_tanggal' => $po->po_tanggal->format('Y-m-d'),
                'jurnal_keterangan' => 'Terima PO '.$po->po_nomor,
                'jurnal_referensi' => $po->po_nomor,
                'jurnal_kategori' => 'PEMBELIAN',
                'jurnal_tipe_tx' => 'PO',
                'jurnal_nomor_tx' => 'JRNL-PO-'.$po->po_id.'-'.time(),
                'jurnal_pihak' => $po->po_vendor,
            ]);
            if (class_exists(\App\Models\DetailJurnal::class) && Schema::hasTable('detail_jurnal')
                && class_exists(\App\Models\Akun::class) && Schema::hasTable('akun')) {
                $debit = \App\Models\Akun::where('akun_kode', '1130')->value('akun_id');
                $kredit = \App\Models\Akun::where('akun_kode', '2100')->value('akun_id');
                if ($debit) {
                    \App\Models\DetailJurnal::create([
                        'detail_jurnal_id' => $jurnal->getKey(),
                        'detail_akun_id' => $debit,
                        'detail_debit' => $total,
                        'detail_kredit' => 0,
                    ]);
                }
                if ($kredit) {
                    \App\Models\DetailJurnal::create([
                        'detail_jurnal_id' => $jurnal->getKey(),
                        'detail_akun_id' => $kredit,
                        'detail_debit' => 0,
                        'detail_kredit' => $total,
                    ]);
                }
            }

            return $jurnal->getKey();
        } catch (\Throwable) {
        }

        return null;
    }
}
