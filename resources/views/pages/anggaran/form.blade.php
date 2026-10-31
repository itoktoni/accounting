<?php /** @var App\Models\Anggaran $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)

                <x-input col="6" type="number" name="anggaran_bulan" helper="1-12" />
                <x-input col="6" type="number" name="anggaran_tahun" />
                <x-input col="6" name="anggaran_akun_kode" />
                <x-input col="6" type="number" name="anggaran_nominal" />
                <x-input col="12" name="anggaran_catatan" />

            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>
</x-layouts::app>
