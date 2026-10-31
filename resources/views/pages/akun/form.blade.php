<?php /** @var App\Models\Akun $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)

                <x-input col="6" name="akun_kode" />
                <x-input col="6" name="akun_nama" />
                <x-select col="6" name="akun_tipe" :options="$tipeOptions" />
                <x-input col="6" name="akun_subtipe" />
                <x-select col="6" name="akun_saldo_normal" :options="['DEBIT' => 'DEBIT', 'KREDIT' => 'KREDIT']" />
                <x-select col="6" name="akun_peran" :options="['' => '-', 'PENYUSUTAN' => 'Penyusutan', 'BUNGA' => 'Bunga', 'PAJAK' => 'Pajak']" />
                <x-checkbox col="6" name="akun_is_rekening" label="Rekening Kas/Bank" />
                <x-input col="6" name="akun_no_rekening" />

            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>
</x-layouts::app>
