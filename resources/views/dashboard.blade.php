<x-layouts::app title="Dashboard">
    <div>
        <div class="mb-6 flex items-center justify-between flex-wrap gap-2">
            <div>
                <h2 class="text-2xl font-bold text-on-surface">Dashboard</h2>
                <p class="text-sm text-on-surface-variant">Periode {{ DateTime::createFromFormat('!m', $bulan)->format('F') }} {{ $tahun }}</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('invoice.getCreate') }}" class="inline-flex items-center gap-1 h-10 px-4 text-sm font-semibold rounded-lg bg-primary text-on-primary hover:bg-primary/90 shadow-sm transition-all">
                    <span class="material-symbols-outlined text-lg">add</span> Buat Invoice
                </a>
                <a href="{{ route('possale.getKasir') }}" class="inline-flex items-center gap-1 h-10 px-4 text-sm font-semibold rounded-lg border border-outline-variant hover:bg-surface-container transition-all">
                    <span class="material-symbols-outlined text-lg">point_of_sale</span> Kasir
                </a>
            </div>
        </div>

        {{-- KPI --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4">
                <div class="flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined text-primary">account_balance_wallet</span>
                    <span class="text-xs font-semibold text-on-surface-variant uppercase">Kas & Bank</span>
                </div>
                <p class="text-xl font-bold text-on-surface">Rp {{ formatAngka((int) round($kas)) }}</p>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4">
                <div class="flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined text-green-700">trending_up</span>
                    <span class="text-xs font-semibold text-on-surface-variant uppercase">Omzet Bulan Ini</span>
                </div>
                <p class="text-xl font-bold text-on-surface">Rp {{ formatAngka((int) round($omzetBulan)) }}</p>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4">
                <div class="flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined text-warning">call_received</span>
                    <span class="text-xs font-semibold text-on-surface-variant uppercase">Piutang</span>
                </div>
                <p class="text-xl font-bold text-on-surface">Rp {{ formatAngka((int) round($piutang)) }}</p>
            </div>
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-4">
                <div class="flex items-center gap-2 mb-1">
                    <span class="material-symbols-outlined text-error">call_made</span>
                    <span class="text-xs font-semibold text-on-surface-variant uppercase">Hutang</span>
                </div>
                <p class="text-xl font-bold text-on-surface">Rp {{ formatAngka((int) round($hutang)) }}</p>
            </div>
        </div>

        {{-- Grafik --}}
        <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6 mb-5">
            <h3 class="font-bold text-on-surface pb-3 mb-4 border-b border-outline-variant flex items-center gap-2">
                <span class="material-symbols-outlined text-primary">bar_chart</span>
                Omzet 6 Bulan Terakhir
            </h3>
            @if(!empty($grafik))
            @php
                $W = 600; $H = 180; $P = 30;
                $n = count($grafik);
                $pts = [];
                foreach ($grafik as $i => $g) {
                    $x = $n > 1 ? $P + $i * ($W - 2 * $P) / ($n - 1) : $W / 2;
                    $y = $H - $P - ($g['total'] / $maxGrafik) * ($H - 2 * $P);
                    $pts[] = ['x' => $x, 'y' => $y, 'label' => $g['label'], 'total' => $g['total']];
                }
                $line = implode(' ', array_map(fn ($p) => round($p['x'], 1).','.round($p['y'], 1), $pts));
                $area = $P.','.($H - $P).' '.$line.' '.($W - $P).','.($H - $P);
            @endphp
            <svg viewBox="0 0 {{ $W }} {{ $H }}" class="w-full h-44" preserveAspectRatio="none">
                @for($gl = 0; $gl <= 3; $gl++)
                @php($gy = $P + $gl * ($H - 2 * $P) / 3)
                <line x1="{{ $P }}" y1="{{ $gy }}" x2="{{ $W - $P }}" y2="{{ $gy }}" stroke="currentColor" stroke-opacity="0.12" />
                @endfor
                <polygon points="{{ $area }}" fill="var(--color-primary, #1e40af)" fill-opacity="0.12" />
                <polyline points="{{ $line }}" fill="none" stroke="var(--color-primary, #1e40af)" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke" />
                @foreach($pts as $p)
                <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="4" fill="var(--color-primary, #1e40af)" stroke="#fff" stroke-width="2" />
                @endforeach
            </svg>
            <div class="flex gap-3 mt-1">
                @foreach($pts as $p)
                <div class="flex-1 text-center min-w-0">
                    <p class="text-xs font-semibold text-on-surface whitespace-nowrap">{{ $p['total'] >= 1000000 ? round($p['total'] / 1000000, 1).' jt' : formatAngka((int) round($p['total'])) }}</p>
                    <p class="text-xs text-on-surface-variant">{{ $p['label'] }}</p>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-on-surface-variant text-center py-4">Belum ada data penjualan.</p>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            {{-- Invoice terbaru --}}
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6">
                <h3 class="font-bold text-on-surface pb-3 mb-2 border-b border-outline-variant flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary">description</span>
                    Invoice Terbaru
                </h3>
                @forelse($invoiceBaru as $inv)
                <a href="{{ route('invoice.getUpdate', ['id' => $inv->invoice_id]) }}" class="flex items-center justify-between py-2.5 border-b border-outline-variant/50 last:border-0 hover:bg-surface-container rounded-lg px-2 -mx-2 transition-colors">
                    <div class="min-w-0">
                        <p class="text-sm font-bold truncate">{{ $inv->invoice_nomor }}</p>
                        <p class="text-xs text-on-surface-variant truncate">{{ $inv->invoice_pelanggan }} · {{ $inv->invoice_tanggal }}</p>
                    </div>
                    <span class="text-xs font-bold px-2 py-1 rounded-full shrink-0 {{ $inv->invoice_status === 'POSTED' ? 'bg-primary/10 text-primary' : 'bg-surface-container-high text-on-surface-variant' }}">{{ $inv->invoice_status }}</span>
                </a>
                @empty
                <p class="text-sm text-on-surface-variant text-center py-4">Belum ada invoice.</p>
                @endforelse
            </div>

            {{-- Jatuh tempo --}}
            <div class="bg-surface-container-lowest border border-outline-variant rounded-xl p-6">
                <h3 class="font-bold text-on-surface pb-3 mb-2 border-b border-outline-variant flex items-center gap-2">
                    <span class="material-symbols-outlined text-error">alarm</span>
                    Piutang Mendesak
                </h3>
                @forelse($tempo as $t)
                <a href="{{ route('piutang.getRiwayat', ['id' => $t->piutang_id]) }}" class="flex items-center justify-between py-2.5 border-b border-outline-variant/50 last:border-0 hover:bg-surface-container rounded-lg px-2 -mx-2 transition-colors">
                    <div class="min-w-0">
                        <p class="text-sm font-bold truncate">{{ $t->piutang_pelanggan }}</p>
                        <p class="text-xs text-on-surface-variant">Jatuh tempo {{ $t->piutang_jatuh_tempo ?: '-' }} · Sisa Rp {{ formatAngka((int) round($t->sisa)) }}</p>
                    </div>
                    <span class="text-xs font-bold px-2 py-1 rounded-full shrink-0 {{ $t->hari < 0 ? 'bg-error/10 text-error' : ($t->hari <= 7 ? 'bg-warning/15 text-warning' : 'bg-surface-container-high text-on-surface-variant') }}">
                        {{ $t->hari < 0 ? abs($t->hari).' hari lewat' : ($t->hari === 0 ? 'Hari ini' : $t->hari.' hari lagi') }}
                    </span>
                </a>
                @empty
                <p class="text-sm text-on-surface-variant text-center py-4">Tidak ada piutang outstanding.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts::app>
