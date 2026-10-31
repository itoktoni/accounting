<?php /** @var App\Models\PosSale $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)

                <x-input col="6" name="sale_nomor_struk" />
                <x-date col="6" name="sale_tanggal" />
                <x-input col="6" name="sale_customer" />
                <x-select col="6" name="sale_metode_bayar" :options="['TUNAI' => 'TUNAI', 'QRIS' => 'QRIS', 'TRANSFER' => 'TRANSFER']" />
                <x-input col="6" name="sale_akun_kas" />
                <x-input col="6" type="number" step="0.01" name="sale_total" />
                <x-input col="6" type="number" step="0.01" name="sale_bayar" />
                <x-input col="6" type="number" step="0.01" name="sale_kembalian" />
                <x-select col="6" name="sale_status" :options="['SELESAI' => 'SELESAI', 'BATAL' => 'BATAL']" />
                <x-input col="6" type="number" name="sale_shift_id" />

            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>
</x-layouts::app>
