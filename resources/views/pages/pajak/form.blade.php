<?php /** @var App\Models\Pajak $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)

                <x-input col="4" name="pajak_kode" label="Kode (mis. PPN11)" />
                <x-input col="8" name="pajak_nama" label="Nama (mis. PPN 11%)" />
                <x-input col="4" type="number" step="0.01" name="pajak_persen" label="Persen" />

            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']" />
    </x-form>
</x-layouts::app>
