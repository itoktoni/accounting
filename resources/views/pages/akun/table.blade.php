<?php /** @var App\Models\Akun $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => '/dashboard', 'label' => 'Home'], ['url' => '', 'label' => moduleLabel()]]" />
    <div class="content mt-4 lg:mt-0">

        <x-table>
            <x-slot:head>
                <x-table-checkbox :model="$model" onchange="toggleAll(this)" />
                <th>Actions</th>
                <th class="text-left">Kode</th>
                <th class="text-left">Nama Akun</th>
                <th class="text-right">Debit</th>
                <th class="text-right">Kredit</th>
                <th class="text-right">Saldo</th>
            </x-slot:head>

            <x-slot:body>
                @php
                    $lastTipe = null; $lastSub = null;
                    $subD = $subK = 0; $tipeD = $tipeK = 0;
                    $saldo = fn ($r) => $r->akun_saldo_normal === 'DEBIT' ? (float) $r->total_debit - (float) $r->total_kredit : (float) $r->total_kredit - (float) $r->total_debit;
                @endphp
                @foreach($data as $table)
                    @if($table->akun_tipe !== $lastTipe)
                        @if($lastTipe !== null)
                            @if($lastSub !== null)
                            <tr class="bg-surface-container font-semibold"><td></td><td></td><td class="p-2 pl-12" colspan="2">Subtotal {{ $lastSub }}</td><td class="p-2 text-right">{{ formatAngka((int) round($subD)) }}</td><td class="p-2 text-right">{{ formatAngka((int) round($subK)) }}</td><td></td></tr>
                            @php $subD = $subK = 0; @endphp
                            @endif
                            @php
                                $tD = $tipeD; $tK = $tipeK;
                            @endphp
                            <tr class="bg-surface-container-high font-bold"><td></td><td></td><td class="p-2" colspan="4">Total {{ $lastTipe }}: D {{ formatAngka((int) round($tD)) }} / K {{ formatAngka((int) round($tK)) }}</td><td></td></tr>
                            @php $tipeD = $tipeK = 0; @endphp
                        @endif
                        <tr><td colspan="7" class="p-2 pt-4 font-bold text-primary text-sm uppercase tracking-wide">{{ $table->akun_tipe }}</td></tr>
                        @php $lastTipe = $table->akun_tipe; $lastSub = null; @endphp
                    @endif
                    @if($table->akun_subtipe !== $lastSub)
                        @if($lastSub !== null)
                            <tr class="bg-surface-container font-semibold"><td></td><td></td><td class="p-2 pl-12" colspan="2">Subtotal {{ $lastSub }}</td><td class="p-2 text-right">{{ formatAngka((int) round($subD)) }}</td><td class="p-2 text-right">{{ formatAngka((int) round($subK)) }}</td><td></td></tr>
                            @php $subD = $subK = 0; @endphp
                        @endif
                        <tr><td></td><td></td><td class="p-2 pl-8 font-semibold text-sm" colspan="5">{{ $table->akun_subtipe ?: '-' }}</td></tr>
                        @php $lastSub = $table->akun_subtipe; @endphp
                    @endif
                    @php
                        $subD += (float) $table->total_debit; $subK += (float) $table->total_kredit;
                        $tipeD += (float) $table->total_debit; $tipeK += (float) $table->total_kredit;
                    @endphp
                    <tr class="border-b">
                        <x-table-row-checkbox :model="$model" :value="$table->field_primary" />
                        <x-table-action :model="$model" :id="$table->field_primary" />
                        <td class="p-2 font-mono">{{ $table->akun_kode }}</td>
                        <td class="p-2 pl-12">{{ $table->akun_nama }}{{ $table->akun_no_rekening ? ' ('.$table->akun_no_rekening.')' : '' }}</td>
                        <td class="p-2 text-right">{{ formatAngka((int) round($table->total_debit)) }}</td>
                        <td class="p-2 text-right">{{ formatAngka((int) round($table->total_kredit)) }}</td>
                        <td class="p-2 text-right font-semibold">{{ formatAngka((int) round($saldo($table))) }}</td>
                    </tr>
                    @if($loop->last)
                        <tr class="bg-surface-container font-semibold"><td></td><td></td><td class="p-2 pl-12" colspan="2">Subtotal {{ $lastSub }}</td><td class="p-2 text-right">{{ formatAngka((int) round($subD)) }}</td><td class="p-2 text-right">{{ formatAngka((int) round($subK)) }}</td><td></td></tr>
                        <tr class="bg-surface-container-high font-bold"><td></td><td></td><td class="p-2" colspan="4">Total {{ $lastTipe }}: D {{ formatAngka((int) round($tipeD)) }} / K {{ formatAngka((int) round($tipeK)) }}</td><td></td></tr>
                    @endif
                @endforeach
            </x-slot:body>

            <x-slot:mobile>
                <x-table-mobile-select :model="$model" :total="$data"/>
                <div class="p-3 space-y-3" id="mBody">
                    @foreach($data as $table)
                    @php $s = $table->akun_saldo_normal === 'DEBIT' ? (float) $table->total_debit - (float) $table->total_kredit : (float) $table->total_kredit - (float) $table->total_debit; @endphp
                    <div class="border border-outline-variant rounded-xl p-4 bg-surface-container-lowest shadow-sm" data-id="{{ $table->field_primary }}">
                        <p class="text-sm font-bold text-on-surface truncate mb-1">{{ $table->akun_kode }} - {{ $table->akun_nama }}</p>
                        <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-3">{{ $table->akun_tipe }} · {{ $table->akun_subtipe }}</p>
                        <div class="grid grid-cols-3 gap-3 mb-3">
                            <div>
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Debit</p>
                                <p class="text-xs font-medium text-on-surface">{{ formatAngka((int) round($table->total_debit)) }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Kredit</p>
                                <p class="text-xs font-medium text-on-surface">{{ formatAngka((int) round($table->total_kredit)) }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Saldo</p>
                                <p class="text-xs font-bold text-primary">{{ formatAngka((int) round($s)) }}</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-outline-variant/50">
                            <span class="text-[9px] font-mono text-on-surface-variant bg-surface-container px-2 py-0.5 rounded">{{ $table->field_primary }}</span>
                            <div class="flex gap-1" onclick="event.stopPropagation()">
                                <x-table-action :model="$model" :id="$table->field_primary" />
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </x-slot:mobile>

        </x-table>

        <x-action :model="$model" :action="['create', 'delete']"/>

    </div>

    <input type="hidden" class="module" value="{{ Str::beforeLast(request()->route()->uri(), '/') }}">
    <script src="/js/table.js"></script>
    <script>initTable('', 'asc');</script>
</x-layouts::app>
