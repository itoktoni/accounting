<?php /** @var App\Models\PosShift $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)

                <x-input col="6" name="shift_username" />
                <x-select col="6" name="shift_status" :options="['OPEN' => 'OPEN', 'CLOSED' => 'CLOSED']" />
                <x-input col="6" type="number" step="0.01" name="shift_saldo_awal" />
                <x-input col="6" type="number" step="0.01" name="shift_kas_sistem" />
                <x-input col="6" type="number" step="0.01" name="shift_kas_fisik" />
                <x-input col="6" type="number" step="0.01" name="shift_selisih" />
                <x-date col="6" name="shift_dibuka" />
                <x-date col="6" name="shift_ditutup" />
                <x-input col="12" name="shift_catatan" />

            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>
</x-layouts::app>
