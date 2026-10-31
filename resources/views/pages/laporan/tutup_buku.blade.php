<x-layouts::app>
    <x-breadcrumb :items="[['url' => '/dashboard', 'label' => 'Home'], ['url' => '', 'label' => 'Tutup Buku Akhir Tahun']]" />
    <div class="content mt-4 lg:mt-0 space-y-4">

        <form method="GET" action="{{ moduleRoute('getTutupBuku') }}">
            <x-card :label="'Tutup Buku Akhir Tahun'" :icon="'lock'">
                <x-input col="4" type="number" name="tahun" label="Tahun" value="{{ $tahun }}" />
            </x-card>

            <div class="no-print print:hidden">
                <div class="action-bar fixed left-0 right-0 lg:left-72 bg-surface-container-lowest border-t border-outline-variant shadow-[0_-4px_12px_rgba(0,0,0,0.08)] px-3 md:px-6 py-2 md:py-3 z-[45]" style="bottom: 4rem">
                    <div class="flex items-center justify-end max-w-full mx-auto gap-2 md:gap-3">
                        <button type="submit"
                            class="inline-flex items-center justify-center gap-1 h-8 md:h-10 px-2.5 md:px-4 text-xs md:text-sm font-semibold rounded-lg bg-primary text-on-primary hover:bg-primary/90 shadow-sm transition-all active:scale-95 shrink-0">
                            <span class="material-symbols-outlined text-base md:text-xl">visibility</span>
                            <span class="hidden sm:inline">Tampilkan</span>
                        </button>
                    </div>
                </div>
            </div>
            <style>
                @media print { .no-print { display: none !important; } }
                @media (min-width: 768px) { .action-bar { bottom: 0 !important; } }
            </style>
        </form>

        <x-card label="Pratinjau Jurnal Penutup {{ $tahun }}">
            <div class="col-span-12 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="border-b font-bold"><th class="p-2 text-left">Akun</th><th class="p-2 text-right">Debit</th><th class="p-2 text-right">Kredit</th></tr></thead>
                    <tbody>
                        <tr><td class="p-2 font-bold" colspan="3">Pendapatan (D ... / K 3200)</td></tr>
                        @foreach($pendapatan as $r)
                        <tr class="border-b"><td class="p-2 pl-6">{{ $r->akun_kode }} - {{ $r->akun_nama }}</td><td class="p-2 text-right">{{ formatAngka((int) round($r->saldo)) }}</td><td class="p-2 text-right">{{ formatAngka((int) round($r->saldo)) }}</td></tr>
                        @endforeach
                        <tr><td class="p-2 font-bold" colspan="3">Beban (D 3200 / K ...)</td></tr>
                        @foreach($beban as $r)
                        <tr class="border-b"><td class="p-2 pl-6">{{ $r->akun_kode }} - {{ $r->akun_nama }}</td><td class="p-2 text-right">{{ formatAngka((int) round($r->saldo)) }}</td><td class="p-2 text-right">{{ formatAngka((int) round($r->saldo)) }}</td></tr>
                        @endforeach
                        <tr class="font-bold bg-surface-container"><td class="p-2">Total</td><td class="p-2 text-right">{{ formatAngka((int) round($totP + $totB)) }}</td><td class="p-2 text-right">{{ formatAngka((int) round($totP + $totB)) }}</td></tr>
                        <tr class="font-bold"><td class="p-2">Laba / (Rugi) Tahun {{ $tahun }}</td><td class="p-2 text-right" colspan="2">{{ formatAngka((int) round($laba)) }}</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="col-span-12 mt-3">
                @if($sudah)
                <p class="text-sm font-semibold text-green-700">Tahun {{ $tahun }} sudah ditutup (TX-TUTUP-{{ $tahun }}).</p>
                @else
                <form method="POST" action="{{ moduleRoute('postTutupBuku') }}" onsubmit="return confirm('Kunci dan tutup buku tahun {{ $tahun }}? Jurnal penutup akan dibuat dan tidak bisa dibatalkan dari sini.')">
                    @csrf
                    <input type="hidden" name="tahun" value="{{ $tahun }}" />
                    <button type="submit" class="inline-flex items-center justify-center gap-1 h-10 px-5 text-sm font-semibold rounded-lg bg-error text-on-error hover:opacity-90 shadow-sm transition-all">
                        <span class="material-symbols-outlined text-lg">lock</span> Tutup Buku {{ $tahun }}
                    </button>
                </form>
                @endif
            </div>
        </x-card>

    </div>
</x-layouts::app>
