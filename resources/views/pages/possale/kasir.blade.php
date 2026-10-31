<?php /** @var App\Models\PosSale $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => 'Kasir']]" />

    <div class="grid grid-cols-12 gap-4">
        <div class="col-span-12 lg:col-span-7">
            <x-card label="Produk">
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                    @forelse($products as $product)
                    <form method="POST" action="{{ moduleRoute('postCheckout') }}">
                        @csrf
                        <input type="hidden" name="sale_total" value="{{ $product->field_name ?? 0 }}">
                        <input type="hidden" name="sale_bayar" value="{{ $product->field_name ?? 0 }}">
                        <input type="hidden" name="items[0][product_id]" value="{{ $product->field_primary }}">
                        <input type="hidden" name="items[0][qty]" value="1">
                        <button type="submit" class="w-full border border-outline-variant rounded-xl p-3 text-left hover:border-primary">
                            <p class="text-sm font-bold truncate">{{ $product->field_name }}</p>
                            <p class="text-xs text-on-surface-variant">Stok: {{ $product->product_stok ?? $product->stok ?? $product->stock ?? '-' }}</p>
                        </button>
                    </form>
                    @empty
                    <p class="text-sm text-on-surface-variant col-span-full">Belum ada produk. Tambah lewat modul produk.</p>
                    @endforelse
                </div>
            </x-card>
        </div>

        <div class="col-span-12 lg:col-span-5">
            <x-form :model="$model" action="{{ moduleRoute('postCheckout') }}">
                <x-card label="Checkout">
                    <x-select col="12" name="sale_shift_id" label="Shift" :options="$shifts->pluck('shift_username', 'shift_id')->toArray()" />
                    <x-input col="12" name="sale_customer" label="Customer" />
                    <x-select col="12" name="sale_metode_bayar" label="Metode Bayar" :options="['TUNAI' => 'TUNAI', 'QRIS' => 'QRIS', 'TRANSFER' => 'TRANSFER']" />
                    <x-input col="12" type="number" step="0.01" name="sale_total" label="Total" />
                    <x-input col="12" type="number" step="0.01" name="sale_bayar" label="Bayar" />
                </x-card>

                <x-action :model="$model" :action="['save']" />
            </x-form>
        </div>
    </div>
</x-layouts::app>
