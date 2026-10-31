<?php /** @var App\Models\Karyawan $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)

                <x-input col="6" name="karyawan_no" />
                <x-input col="6" name="karyawan_nama" />
                <x-input col="6" name="karyawan_jabatan" />
                <x-select col="6" name="karyawan_status_kerja" :options="['TETAP' => 'TETAP', 'KONTRAK' => 'KONTRAK', 'HARIAN' => 'HARIAN']" />
                <x-input col="6" type="number" step="0.01" name="karyawan_gaji_pokok" />
                <x-input col="6" type="number" step="0.01" name="karyawan_tunjangan" />
                <x-toggle col="12" name="karyawan_aktif" />

            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>
</x-layouts::app>
