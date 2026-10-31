<?php /** @var App\Models\Termin $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)

                <x-input col="4" name="termin_kode" label="Kode (mis. NET30)" />
                <x-input col="8" name="termin_nama" label="Nama (mis. NET 30 Hari)" />
                <x-input col="4" type="number" name="termin_hari" label="Hari (0 = tunai)" />
                <x-input col="8" name="termin_keterangan" label="Keterangan" />

            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']" />
    </x-form>
</x-layouts::app>
