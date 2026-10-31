<?php /** @var App\Models\PurchaseOrder $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)

                <x-input col="6" name="po_nomor" />
                <x-input col="6" type="date" name="po_tanggal" />
                <x-input col="6" type="date" name="po_expected_date" label="Tanggal Harap Diterima" />
                <div class="col-span-12 md:col-span-6">
                    <x-select name="po_vendor" label="Vendor" col="12" :options="$vendorOptions" />
                    <button type="button" onclick="document.getElementById('vendorDialog').showModal()" class="text-sm text-primary font-semibold mt-1">+ Tambah Vendor</button>
                </div>
                <x-input col="6" name="po_telepon" />
                <x-input col="6" name="po_alamat" />
                <x-input col="4" type="number" step="0.01" name="po_diskon" label="Diskon Global (Rp)" id="fld-diskon" />
                <x-input col="4" type="number" step="0.01" name="po_ongkir" label="Ongkir (Rp)" id="fld-ongkir" />
                <x-input col="4" type="number" step="0.01" name="po_biaya_lain" label="Biaya Lain (Rp)" id="fld-biaya" />
                <x-select col="6" name="po_status" :options="$status" />
                <x-input col="6" name="po_catatan" />

            @endbind
        </x-card>

        <x-card label="Items" :noGrid="true">
            <div class="overflow-x-auto">
                <style>
                    @media (max-width: 767px) {
                        #itemsTable thead { display: none; }
                        #itemsTable, #itemsTable tbody, #itemsTable tr.item-row, #itemsTable td { display: block; width: 100%; max-width: 100%; }
                        #itemsTable { min-width: 0 !important; }
                        #itemsTable tr.item-row { border: 1px solid #e5e7eb; border-radius: .75rem; margin-bottom: .75rem; padding: .5rem .5rem .25rem; }
                        #itemsTable td { border: 0; padding: .2rem .25rem; }
                        #itemsTable .row-lbl { display: block; font-size: 11px; font-weight: 600; color: #6b7280; margin-bottom: 2px; }
                        #itemsTable td.row-subcell { display: flex; justify-content: space-between; align-items: center; font-size: 14px; }
                        #itemsTable td.row-del { text-align: right; }
                    }
                    @media (min-width: 768px) {
                        #itemsTable .row-lbl { display: none; }
                    }
                </style>
                <table class="w-full text-sm" id="itemsTable">
                    <thead>
                        <tr class="border-b font-bold">
                            <th class="p-2 text-left">Barang</th>
                            <th class="p-2 text-right w-24">Qty</th>
                            <th class="p-2 text-right w-32">Harga</th>
                            <th class="p-2 text-left w-32">Diskon %</th>
                            <th class="p-2 text-right w-32">Subtotal</th>
                            <th class="p-2 w-10"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(($model->exists ? $model->hasItems : [null]) as $i => $it)
                        @php
                            $lineBruto = $it ? (float) $it->poi_qty * (float) $it->poi_harga_satuan : 0;
                            $curDiskon = ($it && $lineBruto > 0) ? round((float) $it->poi_diskon / $lineBruto * 100, 2) : 0;
                            $diskonList = collect($diskonOpts)->keyBy('v')->toArray();
                            if (!isset($diskonList[(string) $curDiskon]) && !isset($diskonList[$curDiskon])) $diskonList[] = ['v' => $curDiskon, 'l' => $curDiskon.'%'];
                            $inMaster = $it && isset($produkMap[$it->poi_deskripsi]);
                        @endphp
                        <tr class="border-b item-row">
                            <td class="p-2" style="min-width:200px">
                                <span class="row-lbl">Barang</span>
                                <select onchange="pilihProduk(this)" class="border rounded px-2 py-1 text-sm w-full prod-select">
                                    <option value="">— Pilih barang —</option>
                                    @foreach($produkMap as $nama => $harga)
                                    <option value="{{ $nama }}" @selected($inMaster && $it->poi_deskripsi === $nama)>{{ $nama }}</option>
                                    @endforeach
                                    <option value="__manual" @selected($it && !$inMaster)>Ketik manual...</option>
                                </select>
                                <input name="items[{{ $i }}][deskripsi]" value="{{ $it?->poi_deskripsi }}" class="border rounded px-2 py-1 text-sm w-full mt-1 prod-text" style="{{ $inMaster || !$it ? 'display:none' : '' }}" @if(!$inMaster) required @endif />
                            </td>
                            <td class="p-2"><span class="row-lbl">Qty</span><input name="items[{{ $i }}][qty]" type="number" step="0.01" min="0" value="{{ $it?->poi_qty ?? 1 }}" class="border rounded px-2 py-1 text-sm w-full text-right" required /></td>
                            <td class="p-2"><span class="row-lbl">Harga</span><input name="items[{{ $i }}][harga]" type="number" step="0.01" min="0" value="{{ $it?->poi_harga_satuan ?? 0 }}" class="border rounded px-2 py-1 text-sm w-full text-right" required /></td>
                            <td class="p-2"><span class="row-lbl">Diskon %</span>
                                <select name="items[{{ $i }}][diskon_persen]" class="border rounded px-2 py-1 text-sm w-full">
                                    @foreach($diskonList as $o)
                                    <option value="{{ $o['v'] }}" @selected($curDiskon == $o['v'])>{{ $o['l'] }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="p-2 text-right row-subtotal row-subcell whitespace-nowrap"><span class="row-lbl">Subtotal</span><span class="row-val">0</span></td>
                            <td class="p-2 text-center row-del"><button type="button" onclick="this.closest('tr').remove();calcLive()" class="text-red-600 font-bold text-lg leading-none">×</button></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <x-button variant="soft" type="button" onclick="tambahBaris()">+ Tambah Baris</x-button>
            </div>

            <div class="mt-2 p-3 rounded-xl bg-surface-container text-sm" id="liveBox">
                <div class="grid grid-cols-2 gap-1">
                    <span class="text-on-surface-variant">Bruto</span><span class="text-right" id="live-bruto">0</span>
                    <span class="text-on-surface-variant">Diskon per barang</span><span class="text-right" id="live-diskon-item">0</span>
                    <span class="text-on-surface-variant">Diskon global</span><span class="text-right" id="live-diskon-global">0</span>
                    <span class="text-on-surface-variant">Ongkir + Biaya lain</span><span class="text-right" id="live-lain">0</span>
                    <span class="font-bold">Total</span><span class="text-right font-bold" id="live-total">0</span>
                </div>
            </div>
        </x-card>

        <x-action :model="$model" :action="($model->exists && $model->po_status !== 'DRAFT') ? ['cancel'] : ['save']">
            @if(isset($model) && $model->exists)
                <a href="{{ moduleRoute('getCetak', ['id' => $model->field_primary]) }}" target="_blank" class="inline-flex items-center justify-center gap-1 h-8 md:h-10 px-2.5 md:px-4 text-xs md:text-sm font-semibold rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container transition-all shrink-0">
                    <span class="material-symbols-outlined text-base md:text-xl">print</span>
                    <span class="hidden sm:inline">Cetak PDF</span>
                </a>
                @if($model->po_status === 'DRAFT')
                <button type="submit" formaction="{{ moduleRoute('postTerima', ['id' => $model->field_primary]) }}" formmethod="POST" formnovalidate onclick="return confirm('Terima PO ini? Stok/hutang/jurnal akan dicatat.')" class="inline-flex items-center justify-center gap-1 h-8 md:h-10 px-2.5 md:px-4 text-xs md:text-sm font-semibold rounded-lg bg-primary text-on-primary hover:bg-primary/90 shadow-sm transition-all active:scale-95 shrink-0">
                    <span class="material-symbols-outlined text-base md:text-xl">check_circle</span>
                    <span class="hidden sm:inline">Terima</span>
                </button>
                @else
                <span class="inline-flex items-center h-8 md:h-10 px-2.5 md:px-4 text-xs md:text-sm font-semibold rounded-lg bg-surface-container-high shrink-0">{{ $model->po_status }}</span>
                @endif
            @endif
        </x-action>
    </x-form>

    <script>
    var PRODUK_HARGA = @json($produkMap);
    var DISKON_OPTS = @json($diskonOpts);
    function pilihProduk(sel) {
        const tr = sel.closest('tr');
        const text = tr.querySelector('.prod-text');
        const inp = tr.querySelector('input[name$="[harga]"]');
        if (sel.value === '__manual') {
            text.style.display = '';
            text.required = true;
            text.focus();
        } else if (sel.value === '') {
            text.value = '';
            text.style.display = 'none';
            text.required = false;
        } else {
            text.value = sel.value;
            text.style.display = 'none';
            text.required = false;
            const harga = PRODUK_HARGA[sel.value];
            if (harga !== undefined && inp && (!inp.value || inp.value == 0)) inp.value = harga;
        }
    }
    function optList(opts, cur) {
        const list = [...opts];
        const cv = Number(cur) || 0;
        if (!list.some(o => Number(o.v) === cv)) list.push({v: cv, l: cv + '%'});
        return list.sort((a, b) => a.v - b.v).map(o => `<option value="${o.v}"${cv === Number(o.v) ? ' selected' : ''}>${o.l}</option>`).join('');
    }
    function tambahBaris() {
        const tbody = document.querySelector('#itemsTable tbody');
        const i = tbody.rows.length;
        const prodOpts = Object.keys(PRODUK_HARGA).map(n => `<option value="${n}">${n}</option>`).join('');
        const tr = document.createElement('tr');
        tr.className = 'border-b item-row';
        tr.innerHTML = `
            <td class="p-1" style="min-width:200px">
                <span class="row-lbl">Barang</span>
                <select onchange="pilihProduk(this)" class="border rounded px-2 py-1 text-sm w-full prod-select">
                    <option value="">— Pilih barang —</option>${prodOpts}<option value="__manual">Ketik manual...</option>
                </select>
                <input name="items[${i}][deskripsi]" class="border rounded px-2 py-1 text-sm w-full mt-1 prod-text" style="display:none" />
            </td>
            <td class="p-2"><span class="row-lbl">Qty</span><input name="items[${i}][qty]" type="number" step="0.01" min="0" value="1" class="border rounded px-2 py-1 text-sm w-full text-right" required /></td>
            <td class="p-2"><span class="row-lbl">Harga</span><input name="items[${i}][harga]" type="number" step="0.01" min="0" value="0" class="border rounded px-2 py-1 text-sm w-full text-right" required /></td>
            <td class="p-2"><span class="row-lbl">Diskon %</span><select name="items[${i}][diskon_persen]" class="border rounded px-2 py-1 text-sm w-full">${optList(DISKON_OPTS, 0)}</select></td>
            <td class="p-2 text-right row-subtotal row-subcell whitespace-nowrap"><span class="row-lbl">Subtotal</span><span class="row-val">0</span></td>
            <td class="p-1 text-center row-del"><button type="button" onclick="this.closest('tr').remove();calcLive()" class="text-red-600 font-bold text-lg leading-none">×</button></td>`;
        tbody.appendChild(tr);
        calcLive();
    }
    function fmtRp(n) { return Math.round(n).toLocaleString('id-ID'); }
    function calcLive() {
        if (!document.getElementById('itemsTable')) return;
        let bruto = 0, dItem = 0;
        document.querySelectorAll('#itemsTable tbody tr').forEach(tr => {
            const q = parseFloat(tr.querySelector('input[name$="[qty]"]')?.value) || 0;
            const h = parseFloat(tr.querySelector('input[name$="[harga]"]')?.value) || 0;
            const d = parseFloat(tr.querySelector('select[name$="[diskon_persen]"]')?.value) || 0;
            const line = q * h, dd = line * d / 100, netto = line - dd;
            bruto += line; dItem += dd;
            const sub = tr.querySelector('.row-subtotal .row-val');
            if (sub) sub.textContent = fmtRp(netto);
        });
        const dg = parseFloat(document.getElementById('fld-diskon')?.value) || 0;
        const ok = parseFloat(document.getElementById('fld-ongkir')?.value) || 0;
        const ln = parseFloat(document.getElementById('fld-biaya')?.value) || 0;
        const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = fmtRp(v); };
        set('live-bruto', bruto);
        set('live-diskon-item', dItem);
        set('live-diskon-global', dg);
        set('live-lain', ok + ln);
        set('live-total', bruto - dItem - dg + ok + ln);
    }
    document.addEventListener('input', calcLive);
    document.addEventListener('change', calcLive);
    calcLive();
    </script>

    <style>
        #vendorDialog { margin: auto auto 0 auto; border-radius: 1rem 1rem 0 0; width: min(28rem, calc(100vw - 2rem)); max-height: 90vh; }
        #vendorDialog::backdrop { background: rgba(0,0,0,.45); }
    </style>
    <dialog id="vendorDialog" class="p-0 w-full max-w-md">
        <form method="dialog" class="p-5">
            <h3 class="font-bold text-lg mb-3">Tambah Vendor Baru</h3>
            <input type="hidden" id="vendorCsrf" value="{{ csrf_token() }}" />
            <label class="text-sm font-bold block mb-1">Nama *</label>
            <input id="vendorNama" class="w-full border rounded-lg px-3 py-2 text-sm mb-2" required />
            <label class="text-sm font-bold block mb-1">Telepon</label>
            <input id="vendorTelepon" class="w-full border rounded-lg px-3 py-2 text-sm mb-2" />
            <label class="text-sm font-bold block mb-1">Alamat</label>
            <input id="vendorAlamat" class="w-full border rounded-lg px-3 py-2 text-sm mb-2" />
            <p id="vendorError" class="text-sm text-red-600 mb-2"></p>
            <div class="flex justify-end gap-2 mt-2">
                <x-button variant="soft" type="button" onclick="document.getElementById('vendorDialog').close()">Batal</x-button>
                <x-button variant="primary" type="button" onclick="simpanVendor()">Simpan</x-button>
            </div>
        </form>
    </dialog>

    <script>
    async function simpanVendor() {
        const err = document.getElementById('vendorError');
        err.textContent = '';
        const nama = document.getElementById('vendorNama').value.trim();
        if (!nama) { err.textContent = 'Nama wajib diisi.'; return; }
        try {
            const res = await fetch("{{ route('vendor.postCreate') }}", {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.getElementById('vendorCsrf').value,
                },
                body: JSON.stringify({
                    vendor_nama: nama,
                    vendor_telepon: document.getElementById('vendorTelepon').value,
                    vendor_alamat: document.getElementById('vendorAlamat').value,
                }),
            });
            const json = await res.json();
            if (!res.ok || !json.status) {
                err.textContent = json.message || 'Gagal menyimpan vendor.';
                return;
            }
            const sel = document.getElementById('select-po_vendor');
            const opt = document.createElement('option');
            opt.value = json.data.vendor_nama;
            opt.textContent = json.data.vendor_nama;
            sel.appendChild(opt);
            sel.value = json.data.vendor_nama;
            document.getElementById('vendorDialog').close();
            document.getElementById('vendorNama').value = '';
            document.getElementById('vendorTelepon').value = '';
            document.getElementById('vendorAlamat').value = '';
        } catch (e) {
            err.textContent = 'Koneksi gagal, coba lagi.';
        }
    }
    </script>
</x-layouts::app>
