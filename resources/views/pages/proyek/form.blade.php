<?php /** @var App\Models\Proyek $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)

                <x-input col="6" name="proyek_kode" />
                <x-input col="6" name="proyek_nama" />
                <x-input col="6" name="proyek_pelanggan" />
                <x-select col="6" name="proyek_status" :options="$status" />
                <x-input col="6" type="number" name="proyek_nilai_kontrak" />
                <x-input col="6" type="number" name="proyek_anggaran_biaya" />
                <x-input col="6" type="date" name="proyek_tanggal_mulai" />
                <x-input col="6" type="date" name="proyek_target_selesai" />
                <x-input col="12" name="proyek_catatan" />

            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>
</x-layouts::app>
