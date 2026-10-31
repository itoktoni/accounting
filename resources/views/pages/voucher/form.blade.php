<?php /** @var App\Models\Voucher $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)

                <x-input col="6" name="voucher_kode" />
                <x-select col="6" name="voucher_tipe" :options="['NOMINAL' => 'NOMINAL', 'PERSEN' => 'PERSEN']" />
                <x-input col="6" type="number" step="0.01" name="voucher_nilai" />
                <x-input col="6" type="number" step="0.01" name="voucher_min_belanja" />
                <x-input col="6" type="number" name="voucher_kuota" />
                <x-input col="6" type="number" name="voucher_terpakai" />
                <x-date col="6" name="voucher_berlaku_dari" />
                <x-date col="6" name="voucher_berlaku_sampai" />
                <x-toggle col="6" name="voucher_aktif" />
                <x-input col="6" name="voucher_catatan" />

            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>
</x-layouts::app>
