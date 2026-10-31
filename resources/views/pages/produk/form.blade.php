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
                <x-select col="6" name="produk_kategori" :options="$kategoriOptions" />
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
</x-layouts::app>
