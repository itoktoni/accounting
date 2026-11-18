<?php /** @var App\Models\Produk $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model" enctype="multipart/form-data">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)

                <x-input col="6" name="produk_kode" />
                <x-input col="6" name="produk_nama" />
                <x-input col="6" name="produk_varian" />
                <x-input col="6" name="produk_satuan" />
                <div class="col-span-12 md:col-span-6">
                    <x-select name="produk_kategori" label="Kategori" col="12" :options="$kategoriOptions" />
                    <button type="button" onclick="document.getElementById('katDialog').showModal()" class="text-sm text-primary font-semibold mt-1">+ Kategori Baru</button>
                </div>
                <x-input col="6" name="produk_barcode" />
                <x-input col="6" type="number" name="produk_harga_beli" />
                <x-input col="6" type="number" name="produk_harga_jual" />
                <x-input col="6" type="number" name="produk_stok" />
                <x-input col="6" type="number" name="produk_min_stok" />
                <x-select col="4" name="produk_favorit" :options="['1' => 'Ya', '0' => 'Tidak']" />
                <x-select col="4" name="produk_pos_tampil" :options="['1' => 'Ya', '0' => 'Tidak']" />
                <x-select col="4" name="produk_aktif" :options="['1' => 'Ya', '0' => 'Tidak']" />

            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>

    <style>
        #katDialog { margin: auto auto 0 auto; border-radius: 1rem 1rem 0 0; width: min(28rem, calc(100vw - 2rem)); max-height: 90vh; }
        #katDialog::backdrop { background: rgba(0,0,0,.45); }
    </style>
    <dialog id="katDialog" class="p-0 w-full max-w-md">
        <form method="dialog" class="p-5">
            <h3 class="font-bold text-lg mb-3">Tambah Kategori Baru</h3>
            <label class="text-sm font-bold block mb-1">Nama kategori *</label>
            <input id="katNama" class="w-full border rounded-lg px-3 py-2 text-sm mb-2" required />
            <p id="katError" class="text-sm text-red-600 mb-2"></p>
            <div class="flex justify-end gap-2 mt-2">
                <x-button variant="soft" type="button" onclick="document.getElementById('katDialog').close()">Batal</x-button>
                <x-button variant="primary" type="button" onclick="simpanKategori()">Simpan</x-button>
            </div>
        </form>
    </dialog>

    <script>
    function simpanKategori() {
        const err = document.getElementById('katError');
        err.textContent = '';
        const nama = document.getElementById('katNama').value.trim();
        if (!nama) { err.textContent = 'Nama kategori wajib diisi.'; return; }
        const sel = document.getElementById('select-produk_kategori');
        if (![...sel.options].some(o => o.value === nama)) {
            const opt = document.createElement('option');
            opt.value = nama;
            opt.textContent = nama;
            sel.appendChild(opt);
        }
        sel.value = nama;
        document.getElementById('katDialog').close();
        document.getElementById('katNama').value = '';
    }
    </script>
</x-layouts::app>
