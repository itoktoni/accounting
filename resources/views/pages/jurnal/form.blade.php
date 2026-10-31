<?php /** @var App\Models\Jurnal $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => isset($model) && $model->exists ? 'Update' : 'Create']]" />

    <x-form :model="$model">
        <x-card :label="moduleLabel()">
            @bind($model ?? null)

                <x-input col="4" type="date" name="jurnal_tanggal" />
                <x-input col="4" name="jurnal_nomor_tx" helper="Kosongkan untuk nomor otomatis" />
                <x-input col="4" name="jurnal_kategori" />
                <x-input col="12" name="jurnal_keterangan" />
                <x-input col="4" name="jurnal_referensi" />
                <x-input col="4" name="jurnal_tipe_tx" />
                <x-input col="4" name="jurnal_pihak" />

            @endbind
        </x-card>

        <x-card label="Detail Jurnal">
            <div class="col-span-12 overflow-x-auto">
                <table class="w-full text-sm" id="linesTable">
                    <thead>
                        <tr class="text-left">
                            <th class="p-2">Akun</th>
                            <th class="p-2 w-40">Debit</th>
                            <th class="p-2 w-40">Kredit</th>
                            <th class="p-2 w-16"></th>
                        </tr>
                    </thead>
                    <tbody id="linesBody">
                        @php $lines = isset($model) && $model->exists ? $model->hasDetails : collect(); @endphp
                        @forelse($lines as $i => $line)
                        <tr class="line-row">
                            <td class="p-1">
                                <select name="lines[{{ $i }}][detail_akun_id]" class="w-full h-12 px-4 bg-white border border-outline-variant rounded-lg">
                                    <option value="">-- Silahkan Pilih --</option>
                                    @foreach($akunOptions as $id => $label)
                                    <option value="{{ $id }}" {{ (string) old("lines.$i.detail_akun_id", $line->detail_akun_id) === (string) $id ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="p-1"><input type="number" step="0.01" min="0" name="lines[{{ $i }}][detail_debit]" value="{{ old("lines.$i.detail_debit", $line->detail_debit) }}" class="w-full h-12 px-4 bg-white border border-outline-variant rounded-lg" /></td>
                            <td class="p-1"><input type="number" step="0.01" min="0" name="lines[{{ $i }}][detail_kredit]" value="{{ old("lines.$i.detail_kredit", $line->detail_kredit) }}" class="w-full h-12 px-4 bg-white border border-outline-variant rounded-lg" /></td>
                            <td class="p-1 text-center"><button type="button" onclick="removeLine(this)" class="text-error font-bold text-lg">&times;</button></td>
                        </tr>
                        @empty
                        @for($i = 0; $i < 2; $i++)
                        <tr class="line-row">
                            <td class="p-1">
                                <select name="lines[{{ $i }}][detail_akun_id]" class="w-full h-12 px-4 bg-white border border-outline-variant rounded-lg">
                                    <option value="">-- Silahkan Pilih --</option>
                                    @foreach($akunOptions as $id => $label)
                                    <option value="{{ $id }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td class="p-1"><input type="number" step="0.01" min="0" name="lines[{{ $i }}][detail_debit]" value="0" class="w-full h-12 px-4 bg-white border border-outline-variant rounded-lg" /></td>
                            <td class="p-1"><input type="number" step="0.01" min="0" name="lines[{{ $i }}][detail_kredit]" value="0" class="w-full h-12 px-4 bg-white border border-outline-variant rounded-lg" /></td>
                            <td class="p-1 text-center"><button type="button" onclick="removeLine(this)" class="text-error font-bold text-lg">&times;</button></td>
                        </tr>
                        @endfor
                        @endforelse
                    </tbody>
                </table>
                <button type="button" onclick="addLine()" class="mt-2 px-4 h-10 rounded-lg border border-outline-variant font-bold">+ Tambah Baris</button>
            </div>
        </x-card>

        <x-action :model="$model" :action="['save']"/>
    </x-form>

    <script>
        const akunOptions = @json($akunOptions);
        function addLine() {
            const body = document.getElementById('linesBody');
            const idx = body.querySelectorAll('tr.line-row').length;
            let opts = '<option value="">-- Silahkan Pilih --</option>';
            for (const [id, label] of Object.entries(akunOptions)) {
                opts += `<option value="${id}">${label}</option>`;
            }
            const tr = document.createElement('tr');
            tr.className = 'line-row';
            tr.innerHTML = `<td class="p-1"><select name="lines[${idx}][detail_akun_id]" class="w-full h-12 px-4 bg-white border border-outline-variant rounded-lg">${opts}</select></td>`
                + `<td class="p-1"><input type="number" step="0.01" min="0" name="lines[${idx}][detail_debit]" value="0" class="w-full h-12 px-4 bg-white border border-outline-variant rounded-lg" /></td>`
                + `<td class="p-1"><input type="number" step="0.01" min="0" name="lines[${idx}][detail_kredit]" value="0" class="w-full h-12 px-4 bg-white border border-outline-variant rounded-lg" /></td>`
                + `<td class="p-1 text-center"><button type="button" onclick="removeLine(this)" class="text-error font-bold text-lg">&times;</button></td>`;
            body.appendChild(tr);
        }
        function removeLine(btn) {
            const body = document.getElementById('linesBody');
            if (body.querySelectorAll('tr.line-row').length <= 2) return;
            btn.closest('tr').remove();
        }
    </script>
</x-layouts::app>
