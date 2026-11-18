@props(['jurnalId' => null, 'jurnalIds' => [], 'dialog' => 'jurnalDialog'])
@php
$ids = collect(is_array($jurnalIds) ? $jurnalIds : [$jurnalIds])
    ->merge([$jurnalId])->filter()->unique()->values();
$__list = $ids->isEmpty() ? collect() : \App\Models\Jurnal::with(['hasDetails.hasAkun'])->whereIn('jurnal_id', $ids)->orderBy('jurnal_id')->get();
@endphp
@foreach($__list as $__ix => $__j)
@php($__dlg = $__list->count() > 1 ? $dialog.'-'.$__j->getKey() : $dialog)
<button type="button" onclick="document.getElementById('{{ $__dlg }}').showModal()" class="inline-flex items-center justify-center gap-1 h-8 md:h-10 px-2.5 md:px-4 text-xs md:text-sm font-semibold rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container transition-all shrink-0">
    <span class="material-symbols-outlined text-base md:text-xl">account_balance</span>
    <span class="hidden sm:inline">Lihat Jurnal{{ $__list->count() > 1 ? ' #'.$__j->jurnal_id : '' }}</span>
</button>
<style>
    #{{ $__dlg }} { margin: auto; border-radius: 1rem; width: min(36rem, calc(100vw - 2rem)); max-height: 90vh; }
    #{{ $__dlg }}::backdrop { background: rgba(0,0,0,.45); }
</style>
<dialog id="{{ $__dlg }}" class="p-0 w-full max-w-2xl">
    <div class="p-5">
        <h3 class="font-bold text-lg mb-1">Jurnal {{ $__j->jurnal_nomor_tx }}</h3>
        <p class="text-sm text-on-surface-variant mb-3">{{ $__j->jurnal_keterangan }} &middot; {{ $__j->jurnal_tanggal?->format('d/m/Y') }}</p>
        <table class="w-full text-sm border">
            <thead>
                <tr class="border-b font-bold bg-surface-container">
                    <th class="p-2 text-left">Akun</th>
                    <th class="p-2 text-right">Debit</th>
                    <th class="p-2 text-right">Kredit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($__j->hasDetails as $d)
                <tr class="border-b">
                    <td class="p-2">{{ $d->hasAkun?->akun_kode }} — {{ $d->hasAkun?->akun_nama }}</td>
                    <td class="p-2 text-right">{{ formatAngka($d->detail_debit) }}</td>
                    <td class="p-2 text-right">{{ formatAngka($d->detail_kredit) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="flex justify-end mt-3">
            <x-button variant="soft" type="button" onclick="document.getElementById('{{ $__dlg }}').close()">Tutup</x-button>
        </div>
    </div>
</dialog>
@endforeach
