<?php /** @var App\Models\Piutang $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => 'Riwayat Pembayaran']]" />

    <x-card :label="'Piutang #' . $model->piutang_id . ' - ' . $model->piutang_pelanggan">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-4">
            <div>
                <p class="text-[10px] uppercase tracking-wide opacity-60 mb-0.5">Tanggal</p>
                <p class="text-sm font-medium">{{ formatDate($model->piutang_tanggal) }}</p>
            </div>
            <div>
                <p class="text-[10px] uppercase tracking-wide opacity-60 mb-0.5">Jumlah</p>
                <p class="text-sm font-medium">{{ formatQty($model->piutang_jumlah) }}</p>
            </div>
            <div>
                <p class="text-[10px] uppercase tracking-wide opacity-60 mb-0.5">Terbayar</p>
                <p class="text-sm font-medium">{{ formatQty($model->piutang_terbayar) }}</p>
            </div>
            <div>
                <p class="text-[10px] uppercase tracking-wide opacity-60 mb-0.5">Sisa</p>
                <p class="text-sm font-medium">{{ formatQty($model->sisa) }} ({{ $model->piutang_status }})</p>
            </div>
        </div>
    </x-card>

    <x-card :label="'Riwayat Pembayaran'">
        <x-table>
            <x-slot:head>
                <th>Tanggal</th>
                <th>Jumlah</th>
                <th>Catatan</th>
            </x-slot:head>
            <x-slot:body>
                @forelse($riwayat as $bayar)
                <tr>
                    <td>{{ formatDate($bayar->bayar_tanggal) }}</td>
                    <td>{{ formatQty($bayar->bayar_jumlah) }}</td>
                    <td>{{ $bayar->bayar_catatan ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="3" class="text-center">Belum ada pembayaran</td></tr>
                @endforelse
            </x-slot:body>
        </x-table>
    </x-card>

    @if($model->sisa > 0)
    <x-card :label="'Bayar Piutang (sisa: ' . formatQty($model->sisa) . ')'">
        <form method="POST" action="{{ moduleRoute('postBayar', ['id' => $model->piutang_id]) }}">
            @csrf
            <div class="grid grid-cols-12 gap-3">
                <x-date col="4" name="bayar_tanggal" />
                <x-input col="4" type="number" name="bayar_jumlah" />
                <x-input col="4" name="bayar_catatan" />
            </div>
            <x-button variant="primary" type="submit">Bayar</x-button>
        </form>
    </x-card>
    @endif
</x-layouts::app>
