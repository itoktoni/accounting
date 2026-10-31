<?php /** @var App\Models\Proyek $table */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => '/dashboard', 'label' => 'Home'], ['url' => '', 'label' => moduleLabel()]]" />
    <div class="content mt-4 lg:mt-0">
        <x-filter :per-page="25" :fields="$fields">
            <x-slot:advanced>
                @foreach ($fields as $key => $advance)
                <x-filter-item :label="$advance" :name="$key"/>
                @endforeach

                <x-button variant="primary" class="btn-block" onclick="applyAdvanced()">Apply</x-button>
                <x-button variant="soft" class="btn-block" onclick="resetAdvanced()">Reset</x-button>
            </x-slot:advanced>
        </x-filter>

        @php
            $currentSort = request('sort.0', '');
            $sortField = str_replace(':desc','',str_replace(':asc','',$currentSort));
            $sortDir = str_contains($currentSort, ':desc') ? 'desc' : 'asc';
        @endphp

        <x-table>
            <x-slot:head>
                <x-table-checkbox :model="$model" onchange="toggleAll(this)" />
                <th>Actions</th>
                <x-table-sort field="proyek_kode" label="Kode" :sortField="$sortField" :sortDir="$sortDir" />
                <x-table-sort field="proyek_nama" label="Nama Proyek" :sortField="$sortField" :sortDir="$sortDir" />
                <x-table-sort field="proyek_pelanggan" label="Pelanggan" :sortField="$sortField" :sortDir="$sortDir" />
                <th class="text-right">Nilai Kontrak</th>
                <th class="text-right">Anggaran</th>
                <th>Mulai</th>
                <x-table-sort field="proyek_status" label="Status" :sortField="$sortField" :sortDir="$sortDir" />
            </x-slot:head>

            <x-slot:body>
                @foreach($data as $table)
                <tr>
                    <x-table-row-checkbox :model="$model" :value="$table->field_primary" />
                    <td class="whitespace-nowrap">
                        <div class="grid grid-cols-3 gap-1 w-28">
                            @can('update', $model)
                            <a href="{{ moduleRoute('getUpdate', ['id' => $table->field_primary]) }}" wire:navigate title="Edit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary/10 text-primary hover:bg-primary/20 transition-colors">
                                <span class="material-symbols-outlined text-lg">edit</span>
                            </a>
                            @endcan
                            @can('delete', $model)
                            <a onclick="return confirm('Are you sure you want to delete?')" href="{{ moduleRoute('getDelete', ['id' => $table->field_primary]) }}" title="Hapus" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-error/10 text-error hover:bg-error/20 transition-colors">
                                <span class="material-symbols-outlined text-lg">delete</span>
                            </a>
                            @endcan
                            <a href="{{ moduleRoute('getDetail', ['id' => $table->field_primary]) }}" title="Detail Proyek" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-surface-container-high text-on-surface-variant hover:bg-surface-container transition-colors">
                                <span class="material-symbols-outlined text-lg">visibility</span>
                            </a>
                            <a href="{{ moduleRoute('getDetail', ['id' => $table->field_primary]) }}#transaksi" title="Pengeluaran" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-error/10 text-error hover:bg-error/20 transition-colors">
                                <span class="material-symbols-outlined text-lg">arrow_downward</span>
                            </a>
                            <a href="{{ moduleRoute('getDetail', ['id' => $table->field_primary]) }}#transaksi" title="Pemasukan" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary/10 text-primary hover:bg-primary/20 transition-colors">
                                <span class="material-symbols-outlined text-lg">arrow_upward</span>
                            </a>
                            <a href="{{ moduleRoute('getCetak', ['id' => $table->field_primary]) }}" target="_blank" title="Cetak PDF" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-surface-container-high text-on-surface-variant hover:bg-surface-container transition-colors">
                                <span class="material-symbols-outlined text-lg">print</span>
                            </a>
                        </div>
                    </td>
                    <td>{{ $table->proyek_kode }}</td>
                    <td>{{ $table->proyek_nama }}</td>
                    <td>{{ $table->proyek_pelanggan ?: '-' }}</td>
                    <td class="text-right">{{ formatAngka((int) $table->proyek_nilai_kontrak) }}</td>
                    <td class="text-right">{{ formatAngka((int) $table->proyek_anggaran_biaya) }}</td>
                    <td class="whitespace-nowrap">{{ $table->proyek_tanggal_mulai ? formatDate($table->proyek_tanggal_mulai) : '-' }}</td>
                    <td>{{ $table->proyek_status }}</td>
                </tr>
                @endforeach
            </x-slot:body>

            <x-slot:mobile>
                <x-table-mobile-select :model="$model" :total="$data"/>
                <div class="p-3 space-y-3" id="mBody">
                    @foreach($data as $table)
                    <div class="border border-outline-variant rounded-xl p-4 bg-surface-container-lowest shadow-sm" data-id="{{ $table->field_primary }}">
                        <p class="text-sm font-bold text-on-surface truncate mb-3">{{ $table->proyek_nama }}</p>
                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Kode</p>
                                <p class="text-xs font-medium text-primary truncate">{{ $table->proyek_kode }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Pelanggan</p>
                                <p class="text-xs font-medium text-on-surface truncate">{{ $table->proyek_pelanggan ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Nilai Kontrak</p>
                                <p class="text-xs font-medium text-on-surface">{{ formatAngka((int) $table->proyek_nilai_kontrak) }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">Status</p>
                                <p class="text-xs font-medium text-on-surface">{{ $table->proyek_status }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-on-surface-variant uppercase tracking-wide mb-0.5">ID</p>
                                <p class="text-xs font-medium text-on-surface">{{ $table->field_primary }}</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between pt-2 border-t border-outline-variant/50">
                            <span class="text-[9px] font-mono text-on-surface-variant bg-surface-container px-2 py-0.5 rounded">{{ $table->field_primary }}</span>
                            <div class="flex gap-1" onclick="event.stopPropagation()">
                                <div class="grid grid-cols-3 gap-1 w-28">
                                    @can('update', $model)
                                    <a href="{{ moduleRoute('getUpdate', ['id' => $table->field_primary]) }}" wire:navigate title="Edit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary/10 text-primary hover:bg-primary/20 transition-colors">
                                        <span class="material-symbols-outlined text-lg">edit</span>
                                    </a>
                                    @endcan
                                    @can('delete', $model)
                                    <a onclick="return confirm('Are you sure you want to delete?')" href="{{ moduleRoute('getDelete', ['id' => $table->field_primary]) }}" title="Hapus" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-error/10 text-error hover:bg-error/20 transition-colors">
                                        <span class="material-symbols-outlined text-lg">delete</span>
                                    </a>
                                    @endcan
                                    <a href="{{ moduleRoute('getDetail', ['id' => $table->field_primary]) }}" title="Detail Proyek" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-surface-container-high text-on-surface-variant hover:bg-surface-container transition-colors">
                                        <span class="material-symbols-outlined text-lg">visibility</span>
                                    </a>
                                    <a href="{{ moduleRoute('getDetail', ['id' => $table->field_primary]) }}#transaksi" title="Pengeluaran" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-error/10 text-error hover:bg-error/20 transition-colors">
                                        <span class="material-symbols-outlined text-lg">arrow_downward</span>
                                    </a>
                                    <a href="{{ moduleRoute('getDetail', ['id' => $table->field_primary]) }}#transaksi" title="Pemasukan" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-primary/10 text-primary hover:bg-primary/20 transition-colors">
                                        <span class="material-symbols-outlined text-lg">arrow_upward</span>
                                    </a>
                                    <a href="{{ moduleRoute('getCetak', ['id' => $table->field_primary]) }}" target="_blank" title="Cetak PDF" class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-surface-container-high text-on-surface-variant hover:bg-surface-container transition-colors">
                                        <span class="material-symbols-outlined text-lg">print</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </x-slot:mobile>

        </x-table>

        <x-pagination :paginator="$data" />
        <x-action :model="$model" :action="['create', 'delete']"/>

    </div>

    <input type="hidden" class="module" value="{{ Str::beforeLast(request()->route()->uri(), '/') }}">
    <script src="/js/table.js"></script>
    <script>initTable('{{ $sortField }}', '{{ $sortDir }}');</script>
</x-layouts::app>
