<?php /** @var App\Models\Produksi $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model" enctype="multipart/form-data">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)

                <x-date col="6" name="produksi_tanggal" />
                <x-select col="6" name="produksi_produk_jadi_id" :options="$produkOptions" />
                <x-input col="6" type="number" name="produksi_qty_hasil" />
                <x-input col="6" type="number" name="produksi_biaya_tambahan" />
                <x-textarea col="12" name="produksi_keterangan" />

            @endbind
        </x-card>

        {{-- shortcut: 5 baris bahan statis, cukup untuk produksi kecil; upgrade ke repeater JS bila butuh >5 bahan --}}
        <x-card label="Bahan Baku">
            @php
                $existing = isset($model) && $model->exists
                    ? $model->hasBahan->map(fn ($b) => ['bahan_produk_id' => $b->bahan_produk_id, 'bahan_qty' => $b->bahan_qty, 'bahan_harga' => $b->bahan_harga])->toArray()
                    : [];
                $rows = array_pad(array_values(old('bahan', $existing)), 5, []);
            @endphp
            @foreach ($rows as $i => $row)
                <x-select col="6" name="bahan[{{ $i }}][bahan_produk_id]" label="Bahan #{{ $i + 1 }}" :options="$produkOptions" :default="$row['bahan_produk_id'] ?? ''" />
                <x-input col="3" type="number" name="bahan[{{ $i }}][bahan_qty]" label="Qty #{{ $i + 1 }}" :value="$row['bahan_qty'] ?? ''" />
                <x-input col="3" type="number" name="bahan[{{ $i }}][bahan_harga]" label="Harga #{{ $i + 1 }}" :value="$row['bahan_harga'] ?? ''" />
            @endforeach
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>
</x-layouts::app>
