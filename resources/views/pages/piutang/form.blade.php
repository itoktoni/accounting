<?php /** @var App\Models\Piutang $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)

                <x-date col="6" name="piutang_tanggal" />
                <x-date col="6" name="piutang_jatuh_tempo" />
                <x-input col="6" name="piutang_pelanggan" />
                <x-input col="6" type="number" name="piutang_jumlah" />
                <x-textarea col="12" name="piutang_keterangan" />

            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>
</x-layouts::app>
