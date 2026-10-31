<?php /** @var App\Models\Hutang $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)

                <x-date col="6" name="hutang_tanggal" />
                <x-date col="6" name="hutang_jatuh_tempo" />
                <x-input col="6" name="hutang_pemasok" />
                <x-input col="6" type="number" name="hutang_jumlah" />
                <x-input col="6" name="hutang_akun_kode" />
                <x-textarea col="6" name="hutang_keterangan" />

            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>
</x-layouts::app>
