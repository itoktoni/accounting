<?php

namespace App\Http\Controllers;

use App\Concerns\ControllerTrait;
use App\Http\Requests\GeneralRequest;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InvoiceController extends Controller
{
    use ControllerTrait {
        postCreate as traitPostCreate;
        postUpdate as traitPostUpdate;
    }

    public function __construct(Invoice $model)
    {
        $this->model = $model::getModel();
    }

    protected function share($data = [])
    {
        $default = [
            'model' => $this->model,
            'status' => ['DRAFT' => 'DRAFT', 'POSTED' => 'POSTED', 'BATAL' => 'BATAL'],
            'pelangganOptions' => \App\Models\Customer::orderBy('customer_nama')->pluck('customer_nama', 'customer_nama')->toArray(),
            'terminOptions' => \App\Models\Termin::where('termin_aktif', true)->orderBy('termin_hari')->get()->mapWithKeys(fn ($t) => [$t->termin_kode => $t->termin_nama])->toArray(),
            'terminHari' => \App\Models\Termin::where('termin_aktif', true)->get()->mapWithKeys(fn ($t) => [$t->termin_kode => (int) $t->termin_hari])->toArray(),
            'produkMap' => \App\Models\Produk::where('produk_aktif', 1)->orderBy('produk_nama')->get(['produk_nama', 'produk_harga_jual'])->mapWithKeys(fn ($p) => [$p->produk_nama => (float) $p->produk_harga_jual])->toArray(),
            'diskonOpts' => \App\Models\Diskon::where('diskon_aktif', true)->orderBy('diskon_persen')->get()->map(fn ($d) => ['v' => (float) $d->diskon_persen, 'l' => $d->diskon_nama])->values()->toArray() ?: [['v' => 0, 'l' => 'Tanpa Diskon'], ['v' => 5, 'l' => 'Diskon 5%'], ['v' => 10, 'l' => 'Diskon 10%'], ['v' => 15, 'l' => 'Diskon 15%'], ['v' => 20, 'l' => 'Diskon 20%'], ['v' => 25, 'l' => 'Diskon 25%'], ['v' => 50, 'l' => 'Diskon 50%'], ['v' => 100, 'l' => 'Diskon 100%']],
            'pajakOpts' => \App\Models\Pajak::where('pajak_aktif', true)->orderBy('pajak_persen')->get()->map(fn ($p) => ['v' => (float) $p->pajak_persen, 'l' => $p->pajak_nama])->values()->toArray() ?: [['v' => 0, 'l' => 'Tanpa Pajak'], ['v' => 10, 'l' => 'PPN 10%'], ['v' => 11, 'l' => 'PPN 11%'], ['v' => 12, 'l' => 'PPN 12%']],
        ];

        return array_merge($default, $data);
    }

    public function getCreate(GeneralRequest $request)
    {
        $model = $this->model;
        $model->invoice_tanggal = now()->toDateString();

        return $this->views($this->template(), [
            'model' => $model,
        ]);
    }

    private function validateItems(GeneralRequest $request): void
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_deskripsi' => 'required|string',
            'items.*.item_qty' => 'required|numeric|min:0',
            'items.*.item_harga_satuan' => 'required|numeric|min:0',
            'items.*.item_diskon_persen' => 'nullable|numeric|min:0|max:100',
        ]);
    }

    private function mergeTermin(GeneralRequest $request): void
    {
        $kode = $request->input('termin_kode');
        if (empty($kode)) {
            return;
        }
        $termin = \App\Models\Termin::where('termin_kode', $kode)->first();
        if (! $termin) {
            return;
        }
        $hari = (int) $termin->termin_hari;
        $tgl = $request->input('invoice_tanggal', now()->toDateString());
        $request->merge([
            'invoice_top_hari' => $hari,
            'invoice_jatuh_tempo' => date('Y-m-d', strtotime($tgl.' +'.$hari.' days')),
        ]);
    }

    public function postCreate(GeneralRequest $request)
    {
        $this->validateItems($request);
        $this->mergeTermin($request);
        $response = $this->traitPostCreate($request);
        if ($response['status'] && $response['data'] instanceof Invoice) {
            $this->syncItems($response['data'], $request->input('items', []));
        }

        return $this->response($response);
    }

    public function postUpdate(GeneralRequest $request, $id)
    {
        $this->validateItems($request);
        $this->mergeTermin($request);
        $response = $this->traitPostUpdate($request, $id);
        if ($response['status']) {
            $this->syncItems($this->model->findOrFail($id), $request->input('items', []));
        }

        return $this->response($response);
    }

    private function syncItems(Invoice $invoice, array $items): void
    {
        $rows = collect($items)->filter(fn ($r) => ! empty($r['item_deskripsi']))->values();
        if ($rows->isEmpty()) {
            return;
        }
        $invoice->hasItems()->delete();
        foreach ($rows as $r) {
            $qty = (float) ($r['item_qty'] ?? 1);
            $harga = (float) ($r['item_harga_satuan'] ?? 0);
            $bruto = $qty * $harga;
            $diskon = round($bruto * (float) ($r['item_diskon_persen'] ?? 0) / 100, 2);
            $invoice->hasItems()->create([
                'item_deskripsi' => $r['item_deskripsi'],
                'item_qty' => $qty,
                'item_satuan' => $r['item_satuan'] ?? 'pcs',
                'item_harga_satuan' => $harga,
                'item_diskon' => $diskon,
                'item_subtotal' => $bruto - $diskon,
            ]);
        }
    }

    public function getCetak(GeneralRequest $request, $id)
    {
        $invoice = $this->model->findOrFail($id);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice->load('hasItems'),
            't' => $invoice->hitungTotal(),
            'termin' => \App\Models\Termin::where('termin_hari', $invoice->invoice_top_hari)->value('termin_nama'),
            'customer' => \App\Models\Customer::where('customer_nama', $invoice->invoice_pelanggan)->first(),
            'bank' => config('website.bank', []),
            'co' => config('website'),
        ]);

        return $pdf->stream($invoice->invoice_nomor.'.pdf');
    }

    public function postPosting(GeneralRequest $request, $id)
    {
        try {
            $total = DB::transaction(function () use ($id) {
                $invoice = $this->model->with('hasItems')->findOrFail($id);

                $subtotal = 0;
                foreach ($invoice->hasItems as $item) {
                    $line = (float) $item->item_qty * (float) $item->item_harga_satuan - (float) $item->item_diskon;
                    $item->update(['item_subtotal' => $line]);
                    $subtotal += $line;
                }

                $dasar = $subtotal - (float) $invoice->invoice_diskon;
                $pajak = round($dasar * (float) $invoice->invoice_pajak_persen / 100, 2);
                $total = $dasar + $pajak + (float) $invoice->invoice_ongkir + (float) $invoice->invoice_biaya_lain;

                $invoice->update([
                    'invoice_pajak_nominal' => $pajak,
                    'invoice_status' => 'POSTED',
                ]);

                // shortcut: integrasi jurnal/piutang hanya bila modul sibling sudah ada (tabel + model).
                // Upgrade: panggil JurnalAction/PiutangAction milik modul A/C saat tersedia.
                $jurnalId = $this->tryCreateJurnal($invoice, $total);
                if ($jurnalId) {
                    $invoice->update(['invoice_jurnal_id' => $jurnalId]);
                }
                $this->tryCreatePiutang($invoice, $total);

                return $total;
            });

            return $this->response($this->payload(TOAST_SUCCESS, ['total' => $total]));
        } catch (\Throwable $th) {
            return $this->response($this->payload(TOAST_FAILED, $th->getMessage()));
        }
    }

    private function tryCreateJurnal(Invoice $invoice, float $total): mixed
    {
        // D Piutang 1120 / K Pendapatan 4100, ikut skema modul akuntansi inti.
        try {
            if (! class_exists(\App\Models\Jurnal::class) || ! Schema::hasTable('jurnal')) {
                return null;
            }
            $jurnal = \App\Models\Jurnal::create([
                'jurnal_tanggal' => $invoice->invoice_tanggal->format('Y-m-d'),
                'jurnal_keterangan' => 'Posting invoice '.$invoice->invoice_nomor,
                'jurnal_referensi' => $invoice->invoice_nomor,
                'jurnal_kategori' => 'PENJUALAN',
                'jurnal_tipe_tx' => 'INVOICE',
                'jurnal_nomor_tx' => 'JRNL-INV-'.$invoice->invoice_id.'-'.time(),
                'jurnal_pihak' => $invoice->invoice_pelanggan,
            ]);
            if (class_exists(\App\Models\DetailJurnal::class) && Schema::hasTable('detail_jurnal')
                && class_exists(\App\Models\Akun::class) && Schema::hasTable('akun')) {
                $debit = \App\Models\Akun::where('akun_kode', '1120')->value('akun_id');
                $kredit = \App\Models\Akun::where('akun_kode', '4100')->value('akun_id');
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

    private function tryCreatePiutang(Invoice $invoice, float $total): void
    {
        try {
            if (! class_exists(\App\Models\Piutang::class) || ! Schema::hasTable('piutang')) {
                return;
            }
            \App\Models\Piutang::create([
                'piutang_tanggal' => $invoice->invoice_tanggal->format('Y-m-d'),
                'piutang_jatuh_tempo' => $invoice->invoice_jatuh_tempo?->format('Y-m-d'),
                'piutang_pelanggan' => $invoice->invoice_pelanggan,
                'piutang_keterangan' => 'Invoice '.$invoice->invoice_nomor,
                'piutang_jumlah' => $total,
                'piutang_status' => 'BELUM LUNAS',
                'piutang_jurnal_id' => $invoice->invoice_jurnal_id,
                'piutang_invoice_id' => $invoice->getKey(),
            ]);
        } catch (\Throwable) {
        }
    }
}
