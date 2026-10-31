<?php /** @var App\Models\Diskon $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)

                <x-input col="4" name="diskon_kode" label="Kode (mis. D10)" />
                <x-input col="8" name="diskon_nama" label="Nama (mis. Diskon 10%)" />
                <x-input col="4" type="number" step="0.01" name="diskon_persen" label="Persen" />

            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']" />
    </x-form>
</x-layouts::app>
