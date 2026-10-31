<?php /** @var App\Models\ProduksiTemplate $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model" enctype="multipart/form-data">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)

                <x-input col="6" name="template_nama" />
                <x-select col="6" name="template_produk_jadi_id" :options="$produkOptions" />
                <x-input col="6" type="number" name="template_qty_hasil" />
                <x-input col="6" type="number" name="template_biaya_tambahan" />
                <x-input col="6" type="number" name="template_harga_jual" />
                <x-input col="6" name="template_kategori" />
                <x-select col="6" name="template_pos_aktif" :options="['1' => 'Ya', '0' => 'Tidak']" />

            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>
</x-layouts::app>
