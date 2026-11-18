<?php /** @var App\Models\Hutang $model */ ?>
@php
    $persen = $model->hutang_jumlah > 0 ? min(100, round($model->hutang_terbayar / $model->hutang_jumlah * 100, 1)) : 0;
    $lunas = $model->hutang_status === 'LUNAS';
@endphp

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => 'Riwayat Pembayaran']]" />

    <div class="content mt-4 lg:mt-0 space-y-4">
        {{-- Ringkasan --}}
        <div class="rounded-2xl overflow-hidden shadow-sm border border-outline-variant">
            <div class="bg-primary px-5 py-4 text-on-primary">
                <div class="flex items-center justify-between gap-3 flex-wrap">
                    <div class="min-w-0">
                        <p class="text-xs opacity-80">Hutang ke</p>
                        <h2 class="text-lg font-bold truncate">{{ $model->hutang_pemasok }}</h2>
                        <p class="text-xs opacity-80">{{ formatDate($model->hutang_tanggal) }} · Jatuh tempo {{ $model->hutang_jatuh_tempo ? formatDate($model->hutang_jatuh_tempo) : '-' }}</p>
                    </div>
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold {{ $lunas ? 'bg-green-500 text-white' : 'bg-white/20 text-white' }}">
                        <span class="material-symbols-outlined text-base">{{ $lunas ? 'task_alt' : 'schedule' }}</span>
                        {{ $model->hutang_status }}
                    </span>
                </div>
            </div>
            <div class="bg-surface-container-lowest px-5 py-4">
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div>
                        <p class="text-[11px] uppercase tracking-wide text-on-surface-variant">Total</p>
                        <p class="text-base font-bold">{{ formatQty($model->hutang_jumlah) }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase tracking-wide text-on-surface-variant">Terbayar</p>
                        <p class="text-base font-bold text-green-700">{{ formatQty($model->hutang_terbayar) }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] uppercase tracking-wide text-on-surface-variant">Sisa</p>
                        <p class="text-base font-bold text-error">{{ formatQty($model->sisa) }}</p>
                    </div>
                </div>
                <div class="mt-3 h-2.5 rounded-full bg-surface-container-high overflow-hidden">
                    <div class="h-full rounded-full bg-gradient-to-r from-primary to-green-500 transition-all" style="width: {{ $persen }}%"></div>
                </div>
                <p class="text-xs text-on-surface-variant mt-1 text-right">{{ $persen }}% terbayar</p>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-4">
            {{-- Riwayat --}}
            <div class="col-span-12 lg:col-span-7">
                <x-card label="Riwayat Pembayaran" icon="history">
                    @forelse($riwayat as $bayar)
                    <div class="col-span-12 flex items-center gap-3 py-3 border-b border-outline-variant/60 last:border-0">
                        <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center shrink-0">
                            <span class="material-symbols-outlined">payments</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold">{{ formatQty($bayar->bh_jumlah) }}</p>
                            <p class="text-xs text-on-surface-variant truncate">{{ formatDate($bayar->bh_tanggal) }}{{ $bayar->bh_catatan ? ' · '.$bayar->bh_catatan : '' }}</p>
                        </div>
                        <x-jurnal-lihat :jurnal-id="$bayar->bh_jurnal_id ?? null" dialog="jurnalDialog{{ $bayar->getKey() }}" />
                    </div>
                    @empty
                    <p class="col-span-12 text-sm text-on-surface-variant text-center py-6">Belum ada pembayaran.</p>
                    @endforelse
                </x-card>
            </div>

            {{-- Form bayar --}}
            <div class="col-span-12 lg:col-span-5">
                @if($model->sisa > 0)
                <x-card label="Bayar Hutang" icon="point_of_sale">
                    <form method="POST" action="{{ moduleRoute('postBayar', ['id' => $model->hutang_id]) }}" class="col-span-12 space-y-3">
                        @csrf
                        <x-date col="12" name="bh_tanggal" label="Tanggal Bayar" />
                        <x-input col="12" type="number" step="0.01" min="1" max="{{ $model->sisa }}" name="bh_jumlah" label="Jumlah (sisa {{ formatQty($model->sisa) }})" />
                        <x-input col="12" name="bh_catatan" label="Catatan" />
                        <div class="col-span-12 flex gap-2">
                            <button type="button" onclick="document.querySelector('input[name=bh_jumlah]').value='{{ $model->sisa }}'" class="flex-1 h-10 text-sm font-semibold rounded-lg border border-outline-variant hover:bg-surface-container transition-all">Lunasi ({{ formatQty($model->sisa) }})</button>
                            <x-button variant="primary" type="submit" class="flex-1">Bayar</x-button>
                        </div>
                    </form>
                </x-card>
                @else
                <x-card label="Lunas" icon="task_alt">
                    <p class="col-span-12 text-sm text-green-700 font-semibold text-center py-4">Hutang sudah lunas. Jurnal pelunasan bisa dilihat di tiap riwayat.</p>
                </x-card>
                @endif
            </div>
        </div>
    </div>
</x-layouts::app>
