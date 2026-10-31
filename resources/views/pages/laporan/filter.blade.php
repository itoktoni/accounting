<x-layouts::app>
    <x-breadcrumb :items="[['url' => '/dashboard', 'label' => 'Home'], ['url' => '', 'label' => $judul]]" />
    <div class="content mt-4 lg:mt-0 space-y-4">

        <form method="GET" action="{{ route($printRoute) }}" target="_blank">
            <x-card :label="'Filter '.$judul" :icon="'filter_alt'">
                <x-input col="4" type="number" name="tahun" label="Tahun" value="{{ request('tahun', date('Y')) }}" />
                <x-input col="4" type="number" name="bulan" label="Bulan (opsional)" value="{{ request('bulan') }}" />
                @foreach ($dropdowns as $dd)
                <x-select col="4" name="{{ $dd['name'] }}" label="{{ $dd['label'] }}" :options="$dd['options']" :default="request($dd['name'])" />
                @endforeach
            </x-card>

            <div class="no-print print:hidden">
                <style>
                    @media (min-width: 768px) { .action-bar { bottom: 0 !important; } }
                </style>
                <div class="action-bar fixed left-0 right-0 lg:left-72 bg-surface-container-lowest border-t border-outline-variant shadow-[0_-4px_12px_rgba(0,0,0,0.08)] px-3 md:px-6 py-2 md:py-3 z-[45]" style="bottom: 4rem">
                    <div class="flex items-center justify-end max-w-full mx-auto gap-2 md:gap-3">
                        <button type="submit"
                            class="inline-flex items-center justify-center gap-1 h-8 md:h-10 px-2.5 md:px-4 text-xs md:text-sm font-semibold rounded-lg bg-primary text-on-primary hover:bg-primary/90 shadow-sm transition-all active:scale-95 shrink-0">
                            <span class="material-symbols-outlined text-base md:text-xl">print</span>
                            <span class="hidden sm:inline">Tampilkan</span>
                        </button>
                        <button type="submit" formaction="{{ route($excelRoute) }}" formtarget="_self"
                            class="inline-flex items-center justify-center gap-1 h-8 md:h-10 px-2.5 md:px-4 text-xs md:text-sm font-semibold rounded-lg bg-green-600 text-white hover:opacity-90 shadow-sm transition-all active:scale-95 shrink-0">
                            <span class="material-symbols-outlined text-base md:text-xl">download</span>
                            <span class="hidden sm:inline">Export Excel</span>
                        </button>
                    </div>
                </div>
            </div>
            <style>
                @media print {
                    .no-print { display: none !important; }
                }
            </style>
        </form>

    </div>
</x-layouts::app>
