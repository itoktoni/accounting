<?php /** @var App\Models\Proyek $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => $model->proyek_kode]]" />

    <div class="mt-4">
        <a href="{{ moduleRoute('getCetak', ['id' => $model->field_primary]) }}" target="_blank" class="inline-flex items-center gap-2 h-9 px-4 text-sm font-semibold rounded-lg border border-outline-variant hover:bg-surface-container transition-all">
            <span class="material-symbols-outlined text-lg">print</span> Cetak PDF Proyek
        </a>
    </div>

    <x-card :label="$model->proyek_kode.' - '.$model->proyek_nama">
        <div class="col-span-12 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
            <div><p class="text-[11px] text-on-surface-variant uppercase">Pelanggan</p><p class="font-semibold">{{ $model->proyek_pelanggan ?: '-' }}</p></div>
            <div><p class="text-[11px] text-on-surface-variant uppercase">Status</p><p class="font-semibold">{{ $model->proyek_status }}</p></div>
            <div><p class="text-[11px] text-on-surface-variant uppercase">Mulai</p><p class="font-semibold">{{ $model->proyek_tanggal_mulai ? formatDate($model->proyek_tanggal_mulai) : '-' }}</p></div>
            <div><p class="text-[11px] text-on-surface-variant uppercase">Target Selesai</p><p class="font-semibold">{{ $model->proyek_target_selesai ? formatDate($model->proyek_target_selesai) : '-' }}</p></div>
            <div><p class="text-[11px] text-on-surface-variant uppercase">Nilai Kontrak</p><p class="font-semibold">{{ formatAngka((int) $model->proyek_nilai_kontrak) }}</p></div>
            <div><p class="text-[11px] text-on-surface-variant uppercase">Anggaran Biaya (Plafon)</p><p class="font-semibold">{{ formatAngka((int) $model->proyek_anggaran_biaya) }}</p></div>
            <div><p class="text-[11px] text-on-surface-variant uppercase">Realisasi (Debit)</p><p class="font-semibold">{{ formatAngka((int) round($totalDebit)) }}</p></div>
            <div><p class="text-[11px] text-on-surface-variant uppercase">Sisa Plafon</p><p class="font-semibold">{{ formatAngka((int) round($model->proyek_anggaran_biaya - $totalDebit)) }}</p></div>
        </div>
        @if($model->proyek_catatan)
        <div class="col-span-12 text-sm mt-2"><p class="text-[11px] text-on-surface-variant uppercase">Catatan</p><p>{{ $model->proyek_catatan }}</p></div>
        @endif
    </x-card>

    <x-card label="Catat Transaksi Proyek" id="transaksi">
        <div class="col-span-12 md:col-span-6">
            <h4 class="font-bold text-sm mb-2">Pengeluaran (D Beban / K Kas)</h4>
            <form method="POST" action="{{ moduleRoute('postPengeluaran', ['id' => $model->field_primary]) }}" class="grid grid-cols-2 gap-2">
                @csrf
                <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" class="border rounded-lg px-3 py-2 text-sm" required />
                <input type="number" step="0.01" min="1" name="nominal" placeholder="Nominal" class="border rounded-lg px-3 py-2 text-sm text-right" required />
                <select name="akun_lawan" class="border rounded-lg px-2 py-2 text-sm col-span-1" required>
                    <option value="">— Akun Beban —</option>
                    @foreach($bebanOptions as $key => $text)
                    <option value="{{ $key }}">{{ $text }}</option>
                    @endforeach
                </select>
                <select name="akun_kas" class="border rounded-lg px-2 py-2 text-sm col-span-1" required>
                    <option value="">— Kas / Bank —</option>
                    @foreach($kasOptions as $key => $text)
                    <option value="{{ $key }}">{{ $text }}</option>
                    @endforeach
                </select>
                <input name="keterangan" placeholder="Keterangan pengeluaran" class="border rounded-lg px-3 py-2 text-sm col-span-2" required />
                <button type="submit" class="col-span-2 inline-flex justify-center items-center gap-1 h-9 px-4 text-sm font-semibold rounded-lg bg-primary text-on-primary">Simpan Pengeluaran</button>
            </form>
        </div>
        <div class="col-span-12 md:col-span-6">
            <h4 class="font-bold text-sm mb-2">Pemasukan (D Kas / K Pendapatan)</h4>
            <form method="POST" action="{{ moduleRoute('postPemasukan', ['id' => $model->field_primary]) }}" class="grid grid-cols-2 gap-2">
                @csrf
                <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" class="border rounded-lg px-3 py-2 text-sm" required />
                <input type="number" step="0.01" min="1" name="nominal" placeholder="Nominal" class="border rounded-lg px-3 py-2 text-sm text-right" required />
                <select name="akun_kas" class="border rounded-lg px-2 py-2 text-sm col-span-1" required>
                    <option value="">— Kas / Bank —</option>
                    @foreach($kasOptions as $key => $text)
                    <option value="{{ $key }}">{{ $text }}</option>
                    @endforeach
                </select>
                <select name="akun_lawan" class="border rounded-lg px-2 py-2 text-sm col-span-1" required>
                    <option value="">— Akun Pendapatan —</option>
                    @foreach($pendapatanOptions as $key => $text)
                    <option value="{{ $key }}">{{ $text }}</option>
                    @endforeach
                </select>
                <input name="keterangan" placeholder="Keterangan pemasukan" class="border rounded-lg px-3 py-2 text-sm col-span-2" required />
                <button type="submit" class="col-span-2 inline-flex justify-center items-center gap-1 h-9 px-4 text-sm font-semibold rounded-lg bg-primary text-on-primary">Simpan Pemasukan</button>
            </form>
        </div>
    </x-card>

    <x-card label="Estimasi Pengeluaran Kasar">
        <div class="col-span-12 grid grid-cols-2 md:grid-cols-4 gap-3 text-sm mb-3">
            <div><p class="text-[11px] text-on-surface-variant uppercase">Total Estimasi</p><p class="font-semibold">{{ formatAngka((int) round($totalEstimasi)) }}</p></div>
            <div><p class="text-[11px] text-on-surface-variant uppercase">Anggaran (Plafon)</p><p class="font-semibold">{{ formatAngka((int) $model->proyek_anggaran_biaya) }}</p></div>
            <div><p class="text-[11px] text-on-surface-variant uppercase">Realisasi</p><p class="font-semibold">{{ formatAngka((int) round($totalDebit)) }}</p></div>
            <div><p class="text-[11px] text-on-surface-variant uppercase">Estimasi vs Realisasi</p><p class="font-semibold">{{ $totalEstimasi > 0 ? round($totalDebit / $totalEstimasi * 100, 1).'%' : '-' }}</p></div>
        </div>
        <div class="col-span-12 overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b font-bold"><th class="p-2 text-left">Deskripsi</th><th class="p-2 text-right">Qty</th><th class="p-2 text-left">Satuan</th><th class="p-2 text-right">Harga</th><th class="p-2 text-right">Subtotal</th><th class="p-2 w-10"></th></tr></thead>
                <tbody>
                    @forelse($estimasis as $e)
                    <tr class="border-b">
                        <td class="p-2">{{ $e->estimasi_deskripsi }}</td>
                        <td class="p-2 text-right">{{ rtrim(rtrim(number_format($e->estimasi_qty, 2), '0'), '.') }}</td>
                        <td class="p-2">{{ $e->estimasi_satuan }}</td>
                        <td class="p-2 text-right">{{ formatAngka((int) round($e->estimasi_harga)) }}</td>
                        <td class="p-2 text-right">{{ formatAngka((int) round($e->estimasi_subtotal)) }}</td>
                        <td class="p-2 text-center">
                            <form method="POST" action="{{ moduleRoute('postHapusEstimasi', ['id' => $model->field_primary, 'estimasiId' => $e->estimasi_id]) }}" onsubmit="return confirm('Hapus estimasi ini?')">
                                @csrf
                                <button type="submit" class="text-red-600 font-bold text-lg leading-none">×</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td class="p-2 text-center" colspan="6">Belum ada estimasi.</td></tr>
                    @endforelse
                    <tr class="font-bold"><td class="p-2" colspan="4">Total Estimasi</td><td class="p-2 text-right">{{ formatAngka((int) round($totalEstimasi)) }}</td><td></td></tr>
                </tbody>
            </table>
        </div>
        <form method="POST" action="{{ moduleRoute('postTambahEstimasi', ['id' => $model->field_primary]) }}" class="col-span-12 grid grid-cols-2 md:grid-cols-12 gap-2 mt-3">
            @csrf
            <input name="estimasi_deskripsi" placeholder="Deskripsi (mis. Belanja server)" class="border rounded-lg px-3 py-2 text-sm col-span-2 md:col-span-5" required />
            <input name="estimasi_qty" type="number" step="0.01" min="0" value="1" class="border rounded-lg px-3 py-2 text-sm text-right md:col-span-2" required />
            <input name="estimasi_satuan" placeholder="Satuan" value="paket" class="border rounded-lg px-3 py-2 text-sm md:col-span-1" />
            <input name="estimasi_harga" type="number" step="0.01" min="0" value="0" placeholder="Harga" class="border rounded-lg px-3 py-2 text-sm text-right md:col-span-2" required />
            <button type="submit" class="md:col-span-2 inline-flex justify-center items-center h-9 px-4 text-sm font-semibold rounded-lg bg-primary text-on-primary">+ Tambah</button>
        </form>
    </x-card>

    <x-card label="Estimasi Pendapatan">
        <div class="col-span-12 grid grid-cols-2 md:grid-cols-3 gap-3 text-sm mb-3">
            <div><p class="text-[11px] text-on-surface-variant uppercase">Total Estimasi Pendapatan</p><p class="font-semibold">{{ formatAngka((int) round($totalEstimasiMasuk)) }}</p></div>
            <div><p class="text-[11px] text-on-surface-variant uppercase">Total Estimasi Pengeluaran</p><p class="font-semibold">{{ formatAngka((int) round($totalEstimasi)) }}</p></div>
            <div><p class="text-[11px] text-on-surface-variant uppercase">Estimasi Laba (Untung)</p><p class="font-semibold">Rp {{ formatAngka((int) round($totalEstimasiMasuk - $totalEstimasi)) }}{{ $totalEstimasiMasuk > 0 ? ' ('.round(($totalEstimasiMasuk - $totalEstimasi) / $totalEstimasiMasuk * 100, 1).'%)' : '' }}</p></div>
        </div>
        <div class="col-span-12 overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b font-bold"><th class="p-2 text-left">Deskripsi</th><th class="p-2 text-right">Qty</th><th class="p-2 text-left">Satuan</th><th class="p-2 text-right">Harga</th><th class="p-2 text-right">Subtotal</th><th class="p-2 w-10"></th></tr></thead>
                <tbody>
                    @forelse($estimasiMasuks as $e)
                    <tr class="border-b">
                        <td class="p-2">{{ $e->estimasi_masuk_deskripsi }}</td>
                        <td class="p-2 text-right">{{ rtrim(rtrim(number_format($e->estimasi_masuk_qty, 2), '0'), '.') }}</td>
                        <td class="p-2">{{ $e->estimasi_masuk_satuan }}</td>
                        <td class="p-2 text-right">{{ formatAngka((int) round($e->estimasi_masuk_harga)) }}</td>
                        <td class="p-2 text-right">{{ formatAngka((int) round($e->estimasi_masuk_subtotal)) }}</td>
                        <td class="p-2 text-center">
                            <form method="POST" action="{{ moduleRoute('postHapusEstimasiMasuk', ['id' => $model->field_primary, 'estimasiId' => $e->estimasi_masuk_id]) }}" onsubmit="return confirm('Hapus estimasi ini?')">
                                @csrf
                                <button type="submit" class="text-red-600 font-bold text-lg leading-none">×</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td class="p-2 text-center" colspan="6">Belum ada estimasi pendapatan.</td></tr>
                    @endforelse
                    <tr class="font-bold"><td class="p-2" colspan="4">Total Estimasi Pendapatan</td><td class="p-2 text-right">{{ formatAngka((int) round($totalEstimasiMasuk)) }}</td><td></td></tr>
                </tbody>
            </table>
        </div>
        <form method="POST" action="{{ moduleRoute('postTambahEstimasiMasuk', ['id' => $model->field_primary]) }}" class="col-span-12 grid grid-cols-2 md:grid-cols-12 gap-2 mt-3">
            @csrf
            <input name="estimasi_masuk_deskripsi" placeholder="Deskripsi (mis. Termin I)" class="border rounded-lg px-3 py-2 text-sm col-span-2 md:col-span-5" required />
            <input name="estimasi_masuk_qty" type="number" step="0.01" min="0" value="1" class="border rounded-lg px-3 py-2 text-sm text-right md:col-span-2" required />
            <input name="estimasi_masuk_satuan" placeholder="Satuan" value="paket" class="border rounded-lg px-3 py-2 text-sm md:col-span-1" />
            <input name="estimasi_masuk_harga" type="number" step="0.01" min="0" value="0" placeholder="Harga" class="border rounded-lg px-3 py-2 text-sm text-right md:col-span-2" required />
            <button type="submit" class="md:col-span-2 inline-flex justify-center items-center h-9 px-4 text-sm font-semibold rounded-lg bg-primary text-on-primary">+ Tambah</button>
        </form>
    </x-card>

    <x-card label="Rincian Biaya per Akun">
        <div class="col-span-12 overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b font-bold"><th class="p-2 text-left">Akun</th><th class="p-2 text-right">Debit</th><th class="p-2 text-right">Kredit</th></tr></thead>
                <tbody>
                    @forelse($rincian as $r)
                    <tr class="border-b"><td class="p-2">{{ $r->akun_kode }} - {{ $r->akun_nama }}</td><td class="p-2 text-right">{{ formatAngka((int) round($r->debit)) }}</td><td class="p-2 text-right">{{ formatAngka((int) round($r->kredit)) }}</td></tr>
                    @empty
                    <tr><td class="p-2 text-center" colspan="3">Belum ada transaksi proyek.</td></tr>
                    @endforelse
                    <tr class="font-bold"><td class="p-2">Total</td><td class="p-2 text-right">{{ formatAngka((int) round($totalDebit)) }}</td><td class="p-2 text-right">{{ formatAngka((int) round($totalKredit)) }}</td></tr>
                </tbody>
            </table>
        </div>
    </x-card>

    <x-card label="List Pengeluaran & Transaksi Proyek">
        <div class="col-span-12 overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b font-bold"><th class="p-2 text-left">Tanggal</th><th class="p-2 text-left">Nomor</th><th class="p-2 text-left">Keterangan</th><th class="p-2 text-right">Debit</th><th class="p-2 text-right">Kredit</th></tr></thead>
                <tbody>
                    @forelse($jurnals as $j)
                    <tr class="border-b">
                        <td class="p-2 whitespace-nowrap">{{ formatDate($j->jurnal_tanggal) }}</td>
                        <td class="p-2">{{ $j->jurnal_nomor_tx }}</td>
                        <td class="p-2">{{ $j->jurnal_keterangan }}</td>
                        <td class="p-2 text-right">{{ formatAngka((int) round($j->total_debit)) }}</td>
                        <td class="p-2 text-right">{{ formatAngka((int) round($j->total_kredit)) }}</td>
                    </tr>
                    @empty
                    <tr><td class="p-2 text-center" colspan="5">Belum ada transaksi proyek.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>

    <x-card label="Dokumen Terkait (Pelanggan Sama)">
        <div class="col-span-12 overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="border-b font-bold"><th class="p-2 text-left">Jenis</th><th class="p-2 text-left">Nomor</th><th class="p-2 text-left">Tanggal</th><th class="p-2 text-left">Status</th></tr></thead>
                <tbody>
                    @foreach($penawarans as $p)
                    <tr class="border-b"><td class="p-2">Penawaran</td><td class="p-2"><a class="text-primary" href="{{ route('penawaran.getUpdate', ['id' => $p->penawaran_id]) }}">{{ $p->penawaran_nomor }}</a></td><td class="p-2">{{ formatDate($p->penawaran_tanggal) }}</td><td class="p-2">{{ $p->penawaran_status }}</td></tr>
                    @endforeach
                    @foreach($invoices as $inv)
                    <tr class="border-b"><td class="p-2">Invoice</td><td class="p-2"><a class="text-primary" href="{{ route('invoice.getUpdate', ['id' => $inv->invoice_id]) }}">{{ $inv->invoice_nomor }}</a></td><td class="p-2">{{ formatDate($inv->invoice_tanggal) }}</td><td class="p-2">{{ $inv->invoice_status }}</td></tr>
                    @endforeach
                    @if($penawarans->isEmpty() && $invoices->isEmpty())
                    <tr><td class="p-2 text-center" colspan="4">Tidak ada dokumen terkait.</td></tr>
                    @endif
                </tbody>
            </table>
        </div>
    </x-card>
</x-layouts::app>
