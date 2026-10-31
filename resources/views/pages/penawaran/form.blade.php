<?php /** @var App\Models\Penawaran $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)

                <x-input col="4" name="penawaran_nomor" label="Nomor (otomatis bila kosong)" />
                <x-input col="4" type="date" name="penawaran_tanggal" label="Tanggal" />
                <x-input col="4" type="date" name="penawaran_berlaku_sampai" label="Berlaku Sampai" />
                <div class="col-span-12 md:col-span-8">
                    <x-select name="penawaran_pelanggan" label="Pelanggan" col="12" :options="$pelangganOptions" />
                    <button type="button" onclick="document.getElementById('custDialog').showModal()" class="text-sm text-primary font-semibold mt-1">+ Tambah Pelanggan Baru</button>
                </div>
                <x-select col="4" name="penawaran_termin_kode" label="Termin Pembayaran" :options="$terminOptions" />

                <div class="col-span-12 overflow-x-auto mt-2">
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
                                <th class="p-2 text-left">Barang / Jasa</th>
                                <th class="p-2 text-right w-24">Qty</th>
                                <th class="p-2 text-right w-32">Harga</th>
                                <th class="p-2 text-left w-32">Diskon %</th>
                                <th class="p-2 text-left w-32">Pajak %</th>
                                <th class="p-2 text-right w-32">Subtotal</th>
                                <th class="p-2 w-10"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(($model->exists ? $model->hasItems : [null]) as $i => $it)
                            @php
                                $curDiskon = $it ? (float) $it->item_diskon_persen : 0;
                                $curPajak = $it ? (float) $it->item_pajak_persen : 0;
                                $diskonList = collect($diskonOpts)->keyBy('v')->toArray();
                                if (!isset($diskonList[(string) $curDiskon]) && !isset($diskonList[$curDiskon])) $diskonList[] = ['v' => $curDiskon, 'l' => $curDiskon.'%'];
                                $pajakList = collect($pajakOpts)->keyBy('v')->toArray();
                                if (!isset($pajakList[(string) $curPajak]) && !isset($pajakList[$curPajak])) $pajakList[] = ['v' => $curPajak, 'l' => $curPajak.'%'];
                                $inMaster = $it && isset($produkMap[$it->item_deskripsi]);
                            @endphp
                            <tr class="border-b item-row">
                                <td class="p-1" style="min-width:200px">
                                    <span class="row-lbl">Barang / Jasa</span>
                                    <select onchange="pilihProduk(this)" class="border rounded px-2 py-1 text-sm w-full prod-select">
                                        <option value="">— Pilih barang/jasa —</option>
                                        @foreach($produkMap as $nama => $harga)
                                        <option value="{{ $nama }}" @selected($inMaster && $it->item_deskripsi === $nama)>{{ $nama }}</option>
                                        @endforeach
                                        <option value="__manual" @selected($it && !$inMaster)>Ketik manual...</option>
                                    </select>
                                    <input name="items[{{ $i }}][item_deskripsi]" value="{{ $it?->item_deskripsi }}" class="border rounded px-2 py-1 text-sm w-full mt-1 prod-text" style="{{ $inMaster || !$it ? 'display:none' : '' }}" @if(!$inMaster) required @endif />
                                </td>
                                <td class="p-2"><span class="row-lbl">Qty</span><input name="items[{{ $i }}][item_qty]" type="number" step="0.01" min="0" value="{{ $it?->item_qty ?? 1 }}" class="border rounded px-2 py-1 text-sm w-full text-right" required /></td>
                                <td class="p-2"><span class="row-lbl">Harga</span><input name="items[{{ $i }}][item_harga_satuan]" type="number" step="0.01" min="0" value="{{ $it?->item_harga_satuan ?? 0 }}" class="border rounded px-2 py-1 text-sm w-full text-right" required /></td>
                                <td class="p-2"><span class="row-lbl">Diskon %</span>
                                    <select name="items[{{ $i }}][item_diskon_persen]" class="border rounded px-2 py-1 text-sm w-full">
                                        @foreach($diskonList as $o)
                                        <option value="{{ $o['v'] }}" @selected($curDiskon == $o['v'])>{{ $o['l'] }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="p-2"><span class="row-lbl">Pajak %</span>
                                    <select name="items[{{ $i }}][item_pajak_persen]" class="border rounded px-2 py-1 text-sm w-full">
                                        @foreach($pajakList as $o)
                                        <option value="{{ $o['v'] }}" @selected($curPajak == $o['v'])>{{ $o['l'] }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="p-2 text-right row-subtotal row-subcell whitespace-nowrap"><span class="row-lbl">Subtotal</span><span class="row-val">0</span></td>
                                <td class="p-1 text-center row-del"><button type="button" onclick="this.closest('tr').remove()" class="text-red-600 font-bold text-lg leading-none">×</button></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <x-button variant="soft" type="button" onclick="tambahBaris()">+ Tambah Baris</x-button>
                    <p class="text-xs text-on-surface-variant mt-1">Kolom Pajak % per baris dipakai bila mode pajak = Per barang.</p>
                </div>

                <div class="col-span-12 mt-2 p-3 rounded-xl bg-surface-container text-sm" id="liveBox">
                    <div class="grid grid-cols-2 gap-1">
                        <span class="text-on-surface-variant">Bruto</span><span class="text-right" id="live-bruto">0</span>
                        <span class="text-on-surface-variant">Diskon per barang</span><span class="text-right" id="live-diskon-item">0</span>
                        <span class="text-on-surface-variant">Diskon global</span><span class="text-right" id="live-diskon-global">0</span>
                        <span class="text-on-surface-variant">Pajak</span><span class="text-right" id="live-pajak">0</span>
                        <span class="text-on-surface-variant">Ongkir + Biaya lain</span><span class="text-right" id="live-lain">0</span>
                        <span class="font-bold">Total</span><span class="text-right font-bold" id="live-total">0</span>
                    </div>
                </div>

                <x-select col="4" name="penawaran_pajak_mode" label="Mode Pajak" :options="$pajakMode" />
                <x-input col="4" type="number" step="0.01" name="penawaran_pajak_persen" label="Pajak Global %" id="fld-pajak-persen" />
                <x-input col="4" type="number" step="0.01" name="penawaran_diskon_global" label="Diskon Global (Rp)" id="fld-diskon-global" />
                <x-input col="4" type="number" step="0.01" name="penawaran_ongkir" label="Ongkir (Rp)" id="fld-ongkir" />
                <x-input col="4" type="number" step="0.01" name="penawaran_biaya_lain" label="Biaya Lain (Rp)" id="fld-biaya" />
                <x-wysiwyg col="12" name="penawaran_catatan" label="Catatan" />
                <x-toggle col="12" name="penawaran_tampil_bank" label="Tampilkan info rekening transfer di PDF" />

            @endbind
        </x-card>

        <x-action :model="$model" :action="($model->exists && $model->penawaran_status !== 'DRAFT') ? ['cancel'] : ['save']">
            @if(isset($model) && $model->exists)
                @if($model->penawaran_status !== 'DRAFT')
                <span class="inline-flex items-center h-8 md:h-10 px-2.5 md:px-4 text-xs md:text-sm font-semibold rounded-lg bg-surface-container-high shrink-0">Terkunci ({{ $model->penawaran_status }})</span>
                @endif
                <span class="hidden md:inline-flex items-center h-8 md:h-10 px-2.5 md:px-4 text-xs md:text-sm font-semibold rounded-lg bg-surface-container-high shrink-0">Rev-{{ $model->penawaran_revisi }}</span>
                <a href="{{ moduleRoute('getCetak', ['id' => $model->field_primary]) }}" target="_blank" class="inline-flex items-center justify-center gap-1 h-8 md:h-10 px-2.5 md:px-4 text-xs md:text-sm font-semibold rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container transition-all shrink-0">
                    <span class="material-symbols-outlined text-base md:text-xl">print</span>
                    <span class="hidden sm:inline">Cetak PDF</span>
                </a>
                @if($model->penawaran_status === 'DRAFT')
                <button type="submit" formaction="{{ moduleRoute('postKirim', ['id' => $model->field_primary]) }}" formmethod="POST" formnovalidate class="inline-flex items-center justify-center gap-1 h-8 md:h-10 px-2.5 md:px-4 text-xs md:text-sm font-semibold rounded-lg bg-primary text-on-primary hover:bg-primary/90 shadow-sm transition-all active:scale-95 shrink-0">
                    <span class="material-symbols-outlined text-base md:text-xl">send</span>
                    <span class="hidden sm:inline">Kirim</span>
                </button>
                @endif
                @if($model->penawaran_status === 'DIKIRIM')
                <button type="submit" formaction="{{ moduleRoute('postTarikKembali', ['id' => $model->field_primary]) }}" formmethod="POST" formnovalidate class="inline-flex items-center justify-center gap-1 h-8 md:h-10 px-2.5 md:px-4 text-xs md:text-sm font-semibold rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container transition-all shrink-0">
                    <span class="material-symbols-outlined text-base md:text-xl">undo</span>
                    <span class="hidden sm:inline">Tarik Kembali</span>
                </button>
                <button type="submit" formaction="{{ moduleRoute('postSetuju', ['id' => $model->field_primary]) }}" formmethod="POST" formnovalidate class="inline-flex items-center justify-center gap-1 h-8 md:h-10 px-2.5 md:px-4 text-xs md:text-sm font-semibold rounded-lg bg-primary text-on-primary hover:bg-primary/90 shadow-sm transition-all active:scale-95 shrink-0">
                    <span class="material-symbols-outlined text-base md:text-xl">check_circle</span>
                    <span class="hidden sm:inline">Setujui</span>
                </button>
                <button type="submit" formaction="{{ moduleRoute('postTolak', ['id' => $model->field_primary]) }}" formmethod="POST" onclick="return confirm('Tolak penawaran ini?')" formnovalidate class="inline-flex items-center justify-center gap-1 h-8 md:h-10 px-2.5 md:px-4 text-xs md:text-sm font-semibold rounded-lg border border-error text-error hover:bg-error/10 transition-all shrink-0">
                    <span class="material-symbols-outlined text-base md:text-xl">cancel</span>
                    <span class="hidden sm:inline">Tolak</span>
                </button>
                @endif
                @if($model->penawaran_status === 'DISETUJUI' && !$model->penawaran_invoice_id)
                <button type="submit" formaction="{{ moduleRoute('postBatalSetuju', ['id' => $model->field_primary]) }}" formmethod="POST" formnovalidate onclick="return confirm('Batalkan persetujuan dan kembalikan ke DRAFT?')" class="inline-flex items-center justify-center gap-1 h-8 md:h-10 px-2.5 md:px-4 text-xs md:text-sm font-semibold rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container transition-all shrink-0">
                    <span class="material-symbols-outlined text-base md:text-xl">undo</span>
                    <span class="hidden sm:inline">Batalkan Persetujuan</span>
                </button>
                <button type="submit" formaction="{{ moduleRoute('postBuatInvoice', ['id' => $model->field_primary]) }}" formmethod="POST" formnovalidate class="inline-flex items-center justify-center gap-1 h-8 md:h-10 px-2.5 md:px-4 text-xs md:text-sm font-semibold rounded-lg bg-primary text-on-primary hover:bg-primary/90 shadow-sm transition-all active:scale-95 shrink-0">
                    <span class="material-symbols-outlined text-base md:text-xl">receipt_long</span>
                    <span class="hidden sm:inline">Buat Invoice</span>
                </button>
                @endif
            @endif
        </x-action>
    </x-form>

    <script>
    var PRODUK_HARGA = @json($produkMap);
    var DISKON_OPTS = @json($diskonOpts);
    var PAJAK_OPTS = @json($pajakOpts);
    function pilihProduk(sel) {
        const tr = sel.closest('tr');
        const text = tr.querySelector('.prod-text');
        const inp = tr.querySelector('input[name$="[item_harga_satuan]"]');
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
    function tambahBaris() {        const tbody = document.querySelector('#itemsTable tbody');
        const i = tbody.rows.length;
        const prodOpts = Object.keys(PRODUK_HARGA).map(n => `<option value="${n}">${n}</option>`).join('');
        const tr = document.createElement('tr');
        tr.className = 'border-b item-row';
        tr.innerHTML = `
            <td class="p-1" style="min-width:200px">
                <span class="row-lbl">Barang / Jasa</span>
                <select onchange="pilihProduk(this)" class="border rounded px-2 py-1 text-sm w-full prod-select">
                    <option value="">— Pilih barang/jasa —</option>${prodOpts}<option value="__manual">Ketik manual...</option>
                </select>
                <input name="items[${i}][item_deskripsi]" class="border rounded px-2 py-1 text-sm w-full mt-1 prod-text" style="display:none" />
            </td>
            <td class="p-2"><span class="row-lbl">Qty</span><input name="items[${i}][item_qty]" type="number" step="0.01" min="0" value="1" class="border rounded px-2 py-1 text-sm w-full text-right" required /></td>
            <td class="p-2"><span class="row-lbl">Harga</span><input name="items[${i}][item_harga_satuan]" type="number" step="0.01" min="0" value="0" class="border rounded px-2 py-1 text-sm w-full text-right" required /></td>
            <td class="p-2"><span class="row-lbl">Diskon %</span><select name="items[${i}][item_diskon_persen]" class="border rounded px-2 py-1 text-sm w-full">${optList(DISKON_OPTS, 0)}</select></td>
            <td class="p-2"><span class="row-lbl">Pajak %</span><select name="items[${i}][item_pajak_persen]" class="border rounded px-2 py-1 text-sm w-full">${optList(PAJAK_OPTS, 0)}</select></td>
            <td class="p-2 text-right row-subtotal row-subcell whitespace-nowrap"><span class="row-lbl">Subtotal</span><span class="row-val">0</span></td>
            <td class="p-1 text-center row-del"><button type="button" onclick="this.closest('tr').remove()" class="text-red-600 font-bold text-lg leading-none">×</button></td>`;
        tbody.appendChild(tr);
        calcLive();
    }
    function fmtRp(n) { return Math.round(n).toLocaleString('id-ID'); }
    function calcLive() {
        if (!document.getElementById('itemsTable')) return;
        const mode = document.getElementById('select-penawaran_pajak_mode')?.value || 'GLOBAL';
        let bruto = 0, dItem = 0, pItem = 0;
        document.querySelectorAll('#itemsTable tbody tr').forEach(tr => {
            const q = parseFloat(tr.querySelector('input[name$="[item_qty]"]')?.value) || 0;
            const h = parseFloat(tr.querySelector('input[name$="[item_harga_satuan]"]')?.value) || 0;
            const d = parseFloat(tr.querySelector('select[name$="[item_diskon_persen]"]')?.value) || 0;
            const p = parseFloat(tr.querySelector('select[name$="[item_pajak_persen]"]')?.value) || 0;
            const line = q * h, dd = line * d / 100, netto = line - dd;
            const rowTax = mode === 'PER_ITEM' ? netto * p / 100 : 0;
            bruto += line; dItem += dd; pItem += netto * p / 100;
            const sub = tr.querySelector('.row-subtotal .row-val');
            if (sub) sub.textContent = fmtRp(netto + rowTax);
        });
        const gp = parseFloat(document.getElementById('fld-pajak-persen')?.value) || 0;
        const dg = parseFloat(document.getElementById('fld-diskon-global')?.value) || 0;
        const ok = parseFloat(document.getElementById('fld-ongkir')?.value) || 0;
        const ln = parseFloat(document.getElementById('fld-biaya')?.value) || 0;
        const dpp = bruto - dItem - dg;
        const pajak = mode === 'PER_ITEM' ? pItem : dpp * gp / 100;
        const set = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = fmtRp(v); };
        set('live-bruto', bruto);
        set('live-diskon-item', dItem);
        set('live-diskon-global', dg);
        set('live-pajak', pajak);
        set('live-lain', ok + ln);
        set('live-total', dpp + pajak + ok + ln);
    }
    document.addEventListener('input', calcLive);
    document.addEventListener('change', e => {
        if (e.target.matches('select[name$="[item_pajak_persen]"]') && (parseFloat(e.target.value) || 0) > 0) {
            const m = document.getElementById('select-penawaran_pajak_mode');
            if (m) m.value = 'PER_ITEM';
        }
        calcLive();
    });
    calcLive();
    </script>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tinymce@6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
<script>
function initPenawaranTiny() {
    var el = document.getElementById('wysiwyg-penawaran_catatan');
    if (!el || !window.tinymce) return;
    var existing = window.tinymce.get('wysiwyg-penawaran_catatan');
    if (existing) existing.remove();
    window.tinymce.init({
        selector: '#wysiwyg-penawaran_catatan',
        height: 220,
        menubar: false,
        plugins: 'lists link table',
        toolbar: 'bold italic underline | bullist numlist | link | table | removeformat',
    });
}
document.addEventListener('DOMContentLoaded', initPenawaranTiny);
document.addEventListener('livewire:navigated', initPenawaranTiny);
initPenawaranTiny();
</script>
@endpush
    <style>
        #custDialog { margin: auto auto 0 auto; border-radius: 1rem 1rem 0 0; width: min(28rem, calc(100vw - 2rem)); max-height: 90vh; }
        #custDialog::backdrop { background: rgba(0,0,0,.45); }
    </style>
    <dialog id="custDialog" class="p-0 w-full max-w-md">
        <form method="dialog" class="p-5">
            <h3 class="font-bold text-lg mb-3">Tambah Pelanggan Baru</h3>
            <input type="hidden" id="custCsrf" value="{{ csrf_token() }}" />
            <label class="text-sm font-bold block mb-1">Nama *</label>
            <input id="custNama" class="w-full border rounded-lg px-3 py-2 text-sm mb-2" required />
            <label class="text-sm font-bold block mb-1">Telepon</label>
            <input id="custTelepon" class="w-full border rounded-lg px-3 py-2 text-sm mb-2" />
            <label class="text-sm font-bold block mb-1">Alamat</label>
            <input id="custAlamat" class="w-full border rounded-lg px-3 py-2 text-sm mb-2" />
            <p id="custError" class="text-sm text-red-600 mb-2"></p>
            <div class="flex justify-end gap-2 mt-2">
                <x-button variant="soft" type="button" onclick="document.getElementById('custDialog').close()">Batal</x-button>
                <x-button variant="primary" type="button" onclick="simpanCustomer()">Simpan</x-button>
            </div>
        </form>
    </dialog>

    <script>
    async function simpanCustomer() {
        const err = document.getElementById('custError');
        err.textContent = '';
        const nama = document.getElementById('custNama').value.trim();
        if (!nama) { err.textContent = 'Nama wajib diisi.'; return; }
        try {
            const res = await fetch("{{ route('customer.postCreate') }}", {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.getElementById('custCsrf').value,
                },
                body: JSON.stringify({
                    customer_nama: nama,
                    customer_telepon: document.getElementById('custTelepon').value,
                    customer_alamat: document.getElementById('custAlamat').value,
                }),
            });
            const json = await res.json();
            if (!res.ok || !json.status) {
                err.textContent = json.message || 'Gagal menyimpan pelanggan.';
                return;
            }
            const sel = document.getElementById('select-penawaran_pelanggan');
            const opt = document.createElement('option');
            opt.value = json.data.customer_nama;
            opt.textContent = json.data.customer_nama;
            sel.appendChild(opt);
            sel.value = json.data.customer_nama;
            document.getElementById('custDialog').close();
            document.getElementById('custNama').value = '';
            document.getElementById('custTelepon').value = '';
            document.getElementById('custAlamat').value = '';
        } catch (e) {
            err.textContent = 'Koneksi gagal, coba lagi.';
        }
    }
    </script>
</x-layouts::app>
