<?php /** @var App\Models\AsetTetap $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)

                <x-input col="6" name="aset_nama" />
                <x-input col="6" name="aset_kategori" />
                <x-input col="6" type="number" step="0.01" name="aset_harga_beli" />
                <x-date col="6" name="aset_tanggal_beli" />
                <x-input col="6" type="number" name="aset_masa_pakai" helper="Masa pakai dalam bulan" />
                <x-input col="6" type="number" step="0.01" name="aset_penyusutan_bulan" />
                <x-input col="6" type="number" step="0.01" name="aset_akumulasi" />
                <x-toggle col="6" name="aset_aktif" />

            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>
</x-layouts::app>
