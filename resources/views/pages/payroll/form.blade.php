<?php /** @var App\Models\Payroll $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)

                <x-input col="6" type="number" name="payroll_karyawan_id" />
                <x-input col="3" type="number" name="payroll_bulan" />
                <x-input col="3" type="number" name="payroll_tahun" />
                <x-input col="6" type="number" step="0.01" name="payroll_gaji_pokok" />
                <x-input col="6" type="number" step="0.01" name="payroll_tunjangan" />
                <x-input col="6" type="number" step="0.01" name="payroll_bonus" />
                <x-input col="6" type="number" step="0.01" name="payroll_potongan" />
                <x-input col="6" type="number" step="0.01" name="payroll_gaji_bersih" />
                <x-input col="6" name="payroll_catatan" />

            @endbind
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>
</x-layouts::app>
