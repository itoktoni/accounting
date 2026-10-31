<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Http\Requests\GeneralRequest;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Penawaran;
use App\Models\PenawaranItem;
use Illuminate\Support\Facades\DB;

class PenawaranController extends Controller
{
    use ControllerTrait;

    public function __construct(Penawaran $model)
    {
        $this->model = $model::getModel();
    }

    protected function share($data = [])
    {
        return array_merge([
            'model' => $this->model,
            'pajakMode' => ['GLOBAL' => 'Global (atas DPP)', 'PER_ITEM' => 'Per barang'],
            'pelangganOptions' => \App\Models\Customer::orderBy('customer_nama')->pluck('customer_nama', 'customer_nama')->toArray(),
            'terminOptions' => \App\Models\Termin::where('termin_aktif', true)->orderBy('termin_hari')->get()->mapWithKeys(fn ($t) => [$t->termin_kode => $t->termin_nama])->toArray(),
            'produkMap' => \App\Models\Produk::where('produk_aktif', 1)->orderBy('produk_nama')->get(['produk_nama', 'produk_harga_jual'])->mapWithKeys(fn ($p) => [$p->produk_nama => (float) $p->produk_harga_jual])->toArray(),
            'diskonOpts' => \App\Models\Diskon::where('diskon_aktif', true)->orderBy('diskon_persen')->get()->map(fn ($d) => ['v' => (float) $d->diskon_persen, 'l' => $d->diskon_nama])->values()->toArray() ?: [['v' => 0, 'l' => 'Tanpa Diskon'], ['v' => 5, 'l' => 'Diskon 5%'], ['v' => 10, 'l' => 'Diskon 10%'], ['v' => 15, 'l' => 'Diskon 15%'], ['v' => 20, 'l' => 'Diskon 20%'], ['v' => 25, 'l' => 'Diskon 25%'], ['v' => 50, 'l' => 'Diskon 50%'], ['v' => 100, 'l' => 'Diskon 100%']],
            'pajakOpts' => \App\Models\Pajak::where('pajak_aktif', true)->orderBy('pajak_persen')->get()->map(fn ($p) => ['v' => (float) $p->pajak_persen, 'l' => $p->pajak_nama])->values()->toArray() ?: [['v' => 0, 'l' => 'Tanpa Pajak'], ['v' => 10, 'l' => 'PPN 10%'], ['v' => 11, 'l' => 'PPN 11%'], ['v' => 12, 'l' => 'PPN 12%']],
            'totals' => isset($data['model']) && $data['model']->exists ? $data['model']->hitungTotal() : null,
        ], $data);
    }

    public function getCreate(GeneralRequest $request)
    {
        $model = $this->model;
        $model->penawaran_tanggal = now()->toDateString();
        $model->penawaran_berlaku_sampai = now()->addDays(7)->toDateString();
        $model->penawaran_pajak_mode = 'GLOBAL';
        $model->penawaran_tampil_bank = true;

        return $this->views($this->template(), [
            'model' => $model,
        ]);
    }

    private function validateInput(GeneralRequest $request): array
    {
        if (empty($request->input('penawaran_nomor'))) {
            $request->merge(['penawaran_nomor' => 'Q-'.date('Ym').'-'.strtoupper(unicString(4))]);
        }
        if (empty($request->input('penawaran_pajak_mode'))) {
            $request->merge(['penawaran_pajak_mode' => 'GLOBAL']);
        }
        $data = $request->validate(array_merge($this->model->rules(), [            'items' => 'required|array|min:1',
            'items.*.item_deskripsi' => 'required|string',
            'items.*.item_qty' => 'required|numeric|min:0',
            'items.*.item_harga_satuan' => 'required|numeric|min:0',
            'items.*.item_diskon_persen' => 'nullable|numeric|min:0|max:100',
            'items.*.item_pajak_persen' => 'nullable|numeric|min:0|max:100',
        ]));

        foreach (['penawaran_pajak_persen', 'penawaran_diskon_global', 'penawaran_ongkir', 'penawaran_biaya_lain'] as $f) {
            if (!isset($data[$f]) || $data[$f] === '') {
                $data[$f] = 0;
            }
        }
        $data['penawaran_tampil_bank'] = ! empty($data['penawaran_tampil_bank']);
        foreach ($data['items'] as $k => $it) {
            foreach (['item_diskon_persen', 'item_pajak_persen'] as $f) {
                if (!isset($it[$f]) || $it[$f] === '') {
                    $data['items'][$k][$f] = 0;
                }
            }
        }

        return $data;
    }

    private function syncItems(Penawaran $penawaran, array $items): void
    {
        $penawaran->hasItems()->delete();
        foreach ($items as $it) {
            $line = (float) $it['item_qty'] * (float) $it['item_harga_satuan'];
            $line -= $line * (float) ($it['item_diskon_persen'] ?? 0) / 100;
            PenawaranItem::create([
                'item_penawaran_id' => $penawaran->penawaran_id,
                'item_deskripsi' => $it['item_deskripsi'],
                'item_qty' => $it['item_qty'],
                'item_satuan' => $it['item_satuan'] ?? 'pcs',
                'item_harga_satuan' => $it['item_harga_satuan'],
                'item_diskon_persen' => $it['item_diskon_persen'] ?? 0,
                'item_pajak_persen' => $it['item_pajak_persen'] ?? 0,
                'item_subtotal' => round($line, 2),
            ]);
        }
    }

    public function postCreate(GeneralRequest $request)
    {
        $data = $this->validateInput($request);

        $penawaran = DB::transaction(function () use ($data) {
            $data['penawaran_nomor'] = $data['penawaran_nomor'] ?: 'Q-'.date('Ym').'-'.strtoupper(unicString(4));
            $data['penawaran_status'] = 'DRAFT';
            $items = $data['items'];
            unset($data['items']);
            $penawaran = $this->model->create($data);
            $this->syncItems($penawaran, $items);

            return $penawaran;
        });

        return $this->response($this->payload(TOAST_SUCCESS, $penawaran));
    }

    public function postUpdate(GeneralRequest $request, $id)
    {
        $data = $this->validateInput($request);
        $penawaran = $this->model->findOrFail($id);
        if ($penawaran->penawaran_status !== 'DRAFT') {
            return $this->response($this->payload(TOAST_FAILED, 'Hanya DRAFT yang bisa diubah. Tarik kembali dulu bila perlu revisi.'));
        }

        DB::transaction(function () use ($data, $penawaran) {
            $items = $data['items'];
            unset($data['items'], $data['penawaran_nomor']);
            $penawaran->update($data);
            $penawaran->increment('penawaran_revisi');
            $this->syncItems($penawaran, $items);
        });

        return $this->response($this->payload(TOAST_SUCCESS, $penawaran->fresh()));
    }

    private function setStatus($id, string $from, string $to)
    {
        $penawaran = $this->model->findOrFail($id);
        if ($penawaran->penawaran_status !== $from) {
            return $this->response($this->payload(TOAST_FAILED, "Status harus {$from}."));
        }
        $penawaran->update(['penawaran_status' => $to]);

        return $this->response($this->payload(TOAST_SUCCESS, $penawaran));
    }

    public function postTarikKembali(GeneralRequest $request, $id)
    {
        return $this->setStatus($id, 'DIKIRIM', 'DRAFT');
    }

    public function postBatalSetuju(GeneralRequest $request, $id)
    {
        $penawaran = $this->model->findOrFail($id);
        if ($penawaran->penawaran_invoice_id) {
            return $this->response($this->payload(TOAST_FAILED, 'Sudah dibuatkan invoice, tidak bisa dibatalkan.'));
        }

        return $this->setStatus($id, 'DISETUJUI', 'DRAFT');
    }

    public function postKirim(GeneralRequest $request, $id)
    {
        return $this->setStatus($id, 'DRAFT', 'DIKIRIM');
    }

    public function postSetuju(GeneralRequest $request, $id)
    {
        return $this->setStatus($id, 'DIKIRIM', 'DISETUJUI');
    }

    public function postTolak(GeneralRequest $request, $id)
    {
        return $this->setStatus($id, 'DIKIRIM', 'DITOLAK');
    }

    public function getCetak(GeneralRequest $request, $id)
    {
        $penawaran = $this->model->findOrFail($id);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.penawaran', [
            'penawaran' => $penawaran->load('hasItems'),
            't' => $penawaran->hitungTotal(),
            'termin' => \App\Models\Termin::where('termin_kode', $penawaran->penawaran_termin_kode)->value('termin_nama'),
            'customer' => \App\Models\Customer::where('customer_nama', $penawaran->penawaran_pelanggan)->first(),
            'bank' => config('website.bank', []),
            'co' => config('website'),
        ]);

        return $pdf->stream($penawaran->penawaran_nomor.'.pdf');
    }

    public function postBuatInvoice(GeneralRequest $request, $id)
    {
        $penawaran = $this->model->findOrFail($id);
        if ($penawaran->penawaran_status !== 'DISETUJUI') {
            return $this->response($this->payload(TOAST_FAILED, 'Hanya penawaran DISETUJUI yang bisa dibuatkan invoice.'));
        }
        if ($penawaran->penawaran_invoice_id) {
            return $this->response($this->payload(TOAST_FAILED, 'Sudah dibuatkan invoice.'));
        }

        $t = $penawaran->hitungTotal();
        $hari = (int) (\App\Models\Termin::where('termin_kode', $penawaran->penawaran_termin_kode)->value('termin_hari') ?? 0);
        $tglInv = now()->toDateString();
        $custMaster = \App\Models\Customer::where('customer_nama', $penawaran->penawaran_pelanggan)->first();
        $invNomor = preg_replace('/^Q-/i', 'INV-', $penawaran->penawaran_nomor);
        $i = 1;
        $baseNomor = $invNomor;
        while (Invoice::where('invoice_nomor', $invNomor)->exists()) {
            $invNomor = $baseNomor.'-'.$i;
            $i++;
        }
        $invoice = DB::transaction(function () use ($penawaran, $t, $hari, $tglInv, $custMaster, $invNomor) {
            $inv = Invoice::create([
                'invoice_nomor' => $invNomor,
                'invoice_tanggal' => $tglInv,
                'invoice_top_hari' => $hari,
                'invoice_jatuh_tempo' => date('Y-m-d', strtotime($tglInv.' +'.$hari.' days')),
                'invoice_pelanggan' => $penawaran->penawaran_pelanggan,
                'invoice_alamat' => $penawaran->penawaran_alamat ?: $custMaster?->customer_alamat,
                'invoice_telepon' => $penawaran->penawaran_telepon ?: $custMaster?->customer_telepon,
                'invoice_diskon' => $t['diskon_item'] + $t['diskon_global'],
                'invoice_ongkir' => $penawaran->penawaran_ongkir,
                'invoice_biaya_lain' => $penawaran->penawaran_biaya_lain,
                'invoice_pajak_persen' => $penawaran->penawaran_pajak_mode === 'GLOBAL' ? $penawaran->penawaran_pajak_persen : 0,
                'invoice_pajak_nominal' => $penawaran->penawaran_pajak_mode === 'PER_ITEM' ? $t['pajak'] : 0,
                'invoice_catatan' => 'Dari '.$penawaran->penawaran_nomor,
                'invoice_status' => 'DRAFT',
            ]);
            foreach ($penawaran->hasItems as $it) {
                $bruto = (float) $it->item_qty * (float) $it->item_harga_satuan;
                InvoiceItem::create([
                    'item_invoice_id' => $inv->invoice_id,
                    'item_deskripsi' => $it->item_deskripsi,
                    'item_qty' => $it->item_qty,
                    'item_satuan' => $it->item_satuan,
                    'item_harga_satuan' => $it->item_harga_satuan,
                    'item_diskon' => round($bruto * (float) $it->item_diskon_persen / 100, 2),
                    'item_subtotal' => $it->item_subtotal,
                ]);
            }
            $penawaran->update(['penawaran_invoice_id' => $inv->invoice_id]);

            return $inv;
        });

        return $this->response($this->payload(TOAST_SUCCESS, $invoice));
    }
}
