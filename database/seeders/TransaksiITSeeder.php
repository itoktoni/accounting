<?php

namespace Database\Seeders;

use App\Models\Akun;
use App\Models\BayarHutang;
use App\Models\BayarPiutang;
use App\Models\Customer;
use App\Models\DetailJurnal;
use App\Models\Hutang;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Jurnal;
use App\Models\Piutang;
use App\Models\PoItem;
use App\Models\Produk;
use App\Models\Proyek;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

// Dummy 12 bulan (Nov 2025 – Okt 2026) untuk perusahaan IT:
// beli device, jual device, jual software, subscription, + project.
// Idempoten via firstOrCreate pada nomor dokumen.
class TransaksiITSeeder extends Seeder
{
    private int $seq = 0;

    private function nomor(string $prefix, string $ym): string
    {
        $this->seq++;

        return sprintf('%s-%s-%04d', $prefix, $ym, $this->seq);
    }

    private function akunId(string $kode): ?int
    {
        return Akun::where('akun_kode', $kode)->value('akun_id');
    }

    private function jurnal(string $nomor, string $tgl, string $ket, string $tipe, string $pihak, array $lines, ?int $proyekId = null): Jurnal
    {
        $j = Jurnal::firstOrCreate(
            ['jurnal_nomor_tx' => $nomor],
            [
                'jurnal_tanggal' => $tgl,
                'jurnal_keterangan' => $ket,
                'jurnal_kategori' => 'OPERASIONAL',
                'jurnal_tipe_tx' => $tipe,
                'jurnal_pihak' => $pihak,
                'jurnal_proyek_id' => $proyekId,
            ]
        );
        if ($j->hasDetails()->count() === 0) {
            foreach ($lines as [$kode, $d, $k]) {
                if ($id = $this->akunId($kode)) {
                    DetailJurnal::create([
                        'detail_jurnal_id' => $j->jurnal_id,
                        'detail_akun_id' => $id,
                        'detail_debit' => $d,
                        'detail_kredit' => $k,
                    ]);
                }
            }
        }

        return $j;
    }

    private function lunaskanPiutang(Piutang $p, string $tgl, ?int $jurnalKasId = null): void
    {
        if ($p->piutang_terbayar >= $p->piutang_jumlah) {
            return;
        }
        BayarPiutang::create([
            'bayar_piutang_id' => $p->piutang_id,
            'bayar_tanggal' => $tgl,
            'bayar_jumlah' => $p->piutang_jumlah - $p->piutang_terbayar,
            'bayar_catatan' => 'Pelunasan dummy',
            'bayar_jurnal_id' => $jurnalKasId,
        ]);
        $p->update(['piutang_terbayar' => $p->piutang_jumlah, 'piutang_status' => 'LUNAS']);
    }

    private function lunaskanHutang(Hutang $h, string $tgl, ?int $jurnalKasId = null): void
    {
        if ($h->hutang_terbayar >= $h->hutang_jumlah) {
            return;
        }
        BayarHutang::create([
            'bh_hutang_id' => $h->hutang_id,
            'bh_tanggal' => $tgl,
            'bh_jumlah' => $h->hutang_jumlah - $h->hutang_terbayar,
            'bh_catatan' => 'Pelunasan dummy',
            'bh_jurnal_id' => $jurnalKasId,
        ]);
        $h->update(['hutang_terbayar' => $h->hutang_jumlah, 'hutang_status' => 'LUNAS']);
    }

    public function run(): void
    {
        foreach (['Distributor Hardware Megatech', 'Importir Device Global', 'Lisensi Software Indo'] as $v) {
            Vendor::firstOrCreate(['vendor_nama' => $v]);
        }
        $pelanggan = ['PT Maju Bersama', 'PT Cahaya Digital', 'CV Berkah Abadi', 'PT Solusi Prima', 'Dinas Kominfo Kota'];
        foreach ($pelanggan as $c) {
            Customer::firstOrCreate(['customer_nama' => $c]);
        }

        // Produk IT: [kode, nama, beli, jual, stok awal]
        $devices = [
            ['NB-X1', 'Laptop ThinkPad X1', 14000000, 17500000, 12],
            ['SRV-2U', 'Server Rack 2U', 45000000, 58000000, 3],
            ['SSD-1T', 'SSD NVMe 1TB', 1100000, 1500000, 60],
            ['SW-24P', 'Switch Managed 24-port', 3200000, 4200000, 15],
            ['MON-27', 'Monitor 27 inch IPS', 2800000, 3600000, 20],
            ['LIC-AV', 'Lisensi Antivirus Corporate /seat', 350000, 500000, 200],
            ['JSA-INS', 'Jasa Instalasi & Setting', 0, 750000, 0],
            ['JSA-MNT', 'Jasa Maintenance /kunjungan', 0, 500000, 0],
            ['JSA-DEV', 'Jasa Pengembangan Software /hari', 0, 1500000, 0],
        ];
        foreach ($devices as [$kode, $nama, $beli, $jual, $stok]) {
            Produk::firstOrCreate(
                ['produk_kode' => $kode],
                [
                    'produk_nama' => $nama, 'produk_satuan' => str_starts_with($kode, 'JSA') ? 'paket' : 'unit',
                    'produk_kategori' => str_starts_with($kode, 'LIC') ? 'Software' : (str_starts_with($kode, 'JSA') ? 'Jasa' : 'Device'),
                    'produk_harga_beli' => $beli, 'produk_harga_jual' => $jual,
                    'produk_stok' => $stok, 'produk_min_stok' => 2,
                    'produk_aktif' => 1, 'produk_pos_tampil' => 1,
                ]
            );
        }

        $proyek = [
            ['PRJ-IT-01', 'Implementasi ERP PT Maju Bersama', 'PT Maju Bersama', 450000000, 300000000, '2025-11-01', '2026-07-31', 'SELESAI'],
            ['PRJ-IT-02', 'Sistem Absensi Cloud CV Berkah Abadi', 'CV Berkah Abadi', 180000000, 110000000, '2026-01-15', '2026-05-31', 'SELESAI'],
            ['PRJ-IT-03', 'Maintenance Server Tahunan PT Solusi Prima', 'PT Solusi Prima', 120000000, 60000000, '2026-01-01', '2026-12-31', 'AKTIF'],
            ['PRJ-IT-04', 'Aplikasi E-Office Dinas Kominfo', 'Dinas Kominfo Kota', 600000000, 420000000, '2026-02-01', '2026-10-31', 'AKTIF'],
        ];
        $proyekIds = [];
        foreach ($proyek as [$kode, $nama, $cust, $nilai, $anggaran, $mulai, $selesai, $status]) {
            $p = Proyek::firstOrCreate(
                ['proyek_kode' => $kode],
                [
                    'proyek_nama' => $nama, 'proyek_pelanggan' => $cust,
                    'proyek_nilai_kontrak' => $nilai, 'proyek_anggaran_biaya' => $anggaran,
                    'proyek_tanggal_mulai' => $mulai, 'proyek_target_selesai' => $selesai,
                    'proyek_status' => $status,
                ]
            );
            $proyekIds[$kode] = $p->proyek_id;
        }

        // Pola bulanan deterministik (index 0 = Nov 2025 ... 11 = Okt 2026)
        $vendors = ['Distributor Hardware Megatech', 'Importir Device Global', 'Lisensi Software Indo'];
        $beliDevice = [ // [kode produk, qty base] per bulan
            ['NB-X1', 4], ['SRV-2U', 1], ['SSD-1T', 20], ['SW-24P', 4], ['MON-27', 6], ['NB-X1', 5],
            ['SSD-1T', 25], ['SRV-2U', 1], ['MON-27', 8], ['SW-24P', 5], ['NB-X1', 6], ['SSD-1T', 30],
        ];
        $jualDevice = [ // 2 invoice device per bulan: [pelanggan idx, kode, qty]
            [[0, 'NB-X1', 3], [1, 'MON-27', 5]], [[2, 'SW-24P', 2], [0, 'SSD-1T', 15]],
            [[3, 'SRV-2U', 1], [1, 'NB-X1', 2]], [[4, 'MON-27', 10], [0, 'SW-24P', 3]],
            [[1, 'SSD-1T', 20], [2, 'NB-X1', 4]], [[0, 'SRV-2U', 1], [3, 'MON-27', 6]],
            [[4, 'NB-X1', 5], [1, 'SW-24P', 4]], [[2, 'SSD-1T', 25], [0, 'MON-27', 4]],
            [[3, 'NB-X1', 3], [1, 'SRV-2U', 1]], [[0, 'SW-24P', 6], [4, 'SSD-1T', 30]],
            [[1, 'MON-27', 8], [2, 'NB-X1', 2]], [[3, 'SSD-1T', 20], [4, 'SW-24P', 3]],
        ];
        $jualSoftware = [ // [pelanggan idx, qty seat] per bulan
            [0, 25], [1, 40], [2, 15], [3, 60], [0, 30], [4, 100],
            [1, 35], [2, 20], [3, 50], [0, 45], [4, 80], [1, 55],
        ];
        $subs = [ // subscription bulanan: [pelanggan, nominal]
            ['PT Solusi Prima', 8500000], ['CV Berkah Abadi', 4000000],
        ];
        $milestone = [ // [bulan idx, proyek, pelanggan, nominal, keterangan]
            [0, 'PRJ-IT-01', 'PT Maju Bersama', 150000000, 'Termin I Implementasi ERP'],
            [2, 'PRJ-IT-02', 'CV Berkah Abadi', 90000000, 'Termin I Absensi Cloud'],
            [3, 'PRJ-IT-01', 'PT Maju Bersama', 150000000, 'Termin II Implementasi ERP'],
            [4, 'PRJ-IT-02', 'CV Berkah Abadi', 90000000, 'Pelunasan Absensi Cloud'],
            [4, 'PRJ-IT-04', 'Dinas Kominfo Kota', 200000000, 'Termin I E-Office'],
            [6, 'PRJ-IT-01', 'PT Maju Bersama', 150000000, 'Pelunasan Implementasi ERP'],
            [7, 'PRJ-IT-04', 'Dinas Kominfo Kota', 200000000, 'Termin II E-Office'],
            [9, 'PRJ-IT-04', 'Dinas Kominfo Kota', 200000000, 'Pelunasan E-Office'],
        ];

        for ($m = 0; $m < 12; $m++) {
            // Nov 2025 (m=0) ... Okt 2026 (m=11)
            $ts = mktime(0, 0, 0, 11 + $m, 15, 2025);
            $tgl15 = date('Y-m-d', $ts);
            $tgl05 = date('Y-m-05', $ts);
            $tgl20 = date('Y-m-20', $ts);
            $tgl28 = date('Y-m-28', $ts);
            $ym = date('Ym', $ts);
            $tempo = date('Y-m-d', strtotime($tgl15.' +30 days'));

            // 1. Pembelian device -> PO + Hutang + Jurnal D1130/K2100
            [$kodeBeli, $qtyBeli] = $beliDevice[$m];
            $prod = Produk::where('produk_kode', $kodeBeli)->first();
            $totalBeli = $prod->produk_harga_beli * $qtyBeli;
            $poNomor = $this->nomor('PO-IT', $ym);
            $po = PurchaseOrder::firstOrCreate(
                ['po_nomor' => $poNomor],
                ['po_tanggal' => $tgl05, 'po_vendor' => $vendors[$m % 3], 'po_status' => 'DITERIMA']
            );
            if ($po->hasItems()->count() === 0) {
                PoItem::create([
                    'poi_po_id' => $po->po_id, 'poi_produk_id' => $prod->produk_id,
                    'poi_deskripsi' => $prod->produk_nama, 'poi_qty' => $qtyBeli,
                    'poi_satuan' => 'unit', 'poi_harga_satuan' => $prod->produk_harga_beli,
                    'poi_subtotal' => $totalBeli,
                ]);
                $prod->increment('produk_stok', $qtyBeli);
            }
            $jBeli = $this->jurnal($this->nomor('TX-IT', $ym), $tgl05, "Pembelian {$prod->produk_nama} x{$qtyBeli}", 'PEMBELIAN', $vendors[$m % 3], [['1130', $totalBeli, 0], ['2100', 0, $totalBeli]]);
            $hut = Hutang::firstOrCreate(
                ['hutang_pemasok' => $vendors[$m % 3], 'hutang_jumlah' => $totalBeli, 'hutang_tanggal' => $tgl05],
                ['hutang_jatuh_tempo' => $tempo, 'hutang_keterangan' => $poNomor, 'hutang_terbayar' => 0, 'hutang_status' => 'BELUM LUNAS', 'hutang_jurnal_id' => $jBeli->jurnal_id, 'hutang_akun_kode' => '2100']
            );
            $po->update(['po_hutang_id' => $hut->hutang_id, 'po_jurnal_id' => $jBeli->jurnal_id]);
            if ($m < 9) { // hutang lama dilunasi
                $jLunas = $this->jurnal($this->nomor('TX-IT', $ym), $tgl28, "Bayar hutang {$vendors[$m % 3]}", 'PENGELUARAN', $vendors[$m % 3], [['2100', $totalBeli, 0], ['1100', 0, $totalBeli]]);
                $this->lunaskanHutang($hut, $tgl28, $jLunas->jurnal_id);
            }

            // 2. Penjualan device (2 invoice)
            foreach ($jualDevice[$m] as $k => [$ci, $kodeJual, $qtyJual]) {
                $this->jualBarang($pelanggan[$ci], $kodeJual, $qtyJual, $tgl15, $tempo, $ym.'-'.($k + 1), $m < 9, null);
            }

            // 3. Penjualan software (lisensi)
            [$ciS, $seat] = $jualSoftware[$m];
            $this->jualBarang($pelanggan[$ciS], 'LIC-AV', $seat, $tgl20, $tempo, $ym.'-S', $m < 9, null);

            // 4. Subscription bulanan
            foreach ($subs as [$subCust, $nominal]) {
                $invNomor = sprintf('SUB-%s-%s', $ym, strtoupper(substr(preg_replace('/[^A-Z]/', '', $subCust), 0, 3)));
                $inv = Invoice::firstOrCreate(
                    ['invoice_nomor' => $invNomor],
                    ['invoice_tanggal' => $tgl15, 'invoice_jatuh_tempo' => $tempo, 'invoice_pelanggan' => $subCust, 'invoice_status' => 'POSTED']
                );
                if ($inv->hasItems()->count() === 0) {
                    InvoiceItem::create(['item_invoice_id' => $inv->invoice_id, 'item_deskripsi' => "Subscription system {$ym}", 'item_qty' => 1, 'item_satuan' => 'paket', 'item_harga_satuan' => $nominal, 'item_subtotal' => $nominal]);
                }
                $jSub = $this->jurnal($this->nomor('TX-IT', $ym), $tgl15, "Subscription {$ym} {$subCust}", 'PENJUALAN', $subCust, [['1120', $nominal, 0], ['4200', 0, $nominal]]);
                $piu = Piutang::firstOrCreate(
                    ['piutang_pelanggan' => $subCust, 'piutang_jumlah' => $nominal, 'piutang_tanggal' => $tgl15],
                    ['piutang_jatuh_tempo' => $tempo, 'piutang_keterangan' => $invNomor, 'piutang_terbayar' => 0, 'piutang_status' => 'BELUM LUNAS', 'piutang_jurnal_id' => $jSub->jurnal_id, 'piutang_invoice_id' => $inv->invoice_id]
                );
                $inv->update(['invoice_jurnal_id' => $jSub->jurnal_id]);
                if ($m < 10) {
                    $jKas = $this->jurnal($this->nomor('TX-IT', $ym), $tgl28, "Terima subscription {$subCust}", 'PENERIMAAN', $subCust, [['1100', $nominal, 0], ['1120', 0, $nominal]]);
                    $this->lunaskanPiutang($piu, $tgl28, $jKas->jurnal_id);
                }
            }

            // 5. Beban operasional bulanan (tunai)
            $sewa = 12000000;
            $this->jurnal($this->nomor('TX-IT', $ym), $tgl05, 'Sewa kantor bulan berjalan', 'PENGELUARAN', '', [['6110', $sewa, 0], ['1100', 0, $sewa]]);
            $gaji = 28000000 + ($m % 3) * 1000000;
            $this->jurnal($this->nomor('TX-IT', $ym), $tgl28, 'Gaji tim IT bulan berjalan', 'PENGGAJIAN', '', [['6100', $gaji, 0], ['1100', 0, $gaji]]);
            $util = 3500000;
            $this->jurnal($this->nomor('TX-IT', $ym), $tgl20, 'Internet & listrik', 'PENGELUARAN', '', [['6120', $util, 0], ['1100', 0, $util]]);
        }

        // 6. Termin project
        foreach ($milestone as [$bm, $kodePrj, $cust, $nominal, $ket]) {
            $ts = mktime(0, 0, 0, 11 + $bm, 10, 2025);
            $tgl = date('Y-m-d', $ts);
            $ymP = date('Ym', $ts);
            $tempoP = date('Y-m-d', strtotime($tgl.' +30 days'));
            $invNomor = sprintf('PRJ-%s-%02d', $ymP, $bm + 1);
            $inv = Invoice::firstOrCreate(
                ['invoice_nomor' => $invNomor],
                ['invoice_tanggal' => $tgl, 'invoice_jatuh_tempo' => $tempoP, 'invoice_pelanggan' => $cust, 'invoice_status' => 'POSTED']
            );
            if ($inv->hasItems()->count() === 0) {
                InvoiceItem::create(['item_invoice_id' => $inv->invoice_id, 'item_deskripsi' => $ket, 'item_qty' => 1, 'item_satuan' => 'termin', 'item_harga_satuan' => $nominal, 'item_subtotal' => $nominal]);
            }
            $jPrj = $this->jurnal($this->nomor('TX-IT', $ymP), $tgl, $ket, 'PENJUALAN', $cust, [['1120', $nominal, 0], ['4200', 0, $nominal]], $proyekIds[$kodePrj]);
            $piu = Piutang::firstOrCreate(
                ['piutang_pelanggan' => $cust, 'piutang_jumlah' => $nominal, 'piutang_tanggal' => $tgl],
                ['piutang_jatuh_tempo' => $tempoP, 'piutang_keterangan' => $invNomor, 'piutang_terbayar' => 0, 'piutang_status' => 'BELUM LUNAS', 'piutang_jurnal_id' => $jPrj->jurnal_id, 'piutang_invoice_id' => $inv->invoice_id]
            );
            $inv->update(['invoice_jurnal_id' => $jPrj->jurnal_id]);
            if ($bm < 7) {
                $jKas = $this->jurnal($this->nomor('TX-IT', $ymP), date('Y-m-28', $ts), "Terima {$ket}", 'PENERIMAAN', $cust, [['1100', $nominal, 0], ['1120', 0, $nominal]], $proyekIds[$kodePrj]);
                $this->lunaskanPiutang($piu, date('Y-m-28', $ts), $jKas->jurnal_id);
            }
        }
    }

    private function jualBarang(string $cust, string $kode, int $qty, string $tgl, string $tempo, string $suffix, bool $lunas, ?int $proyekId): void
    {
        $prod = Produk::where('produk_kode', $kode)->first();
        $total = $prod->produk_harga_jual * $qty;
        $invNomor = sprintf('INV-IT-%s-%s', date('Ym', strtotime($tgl)), $suffix);
        $inv = Invoice::firstOrCreate(
            ['invoice_nomor' => $invNomor],
            ['invoice_tanggal' => $tgl, 'invoice_jatuh_tempo' => $tempo, 'invoice_pelanggan' => $cust, 'invoice_status' => 'POSTED']
        );
        if ($inv->hasItems()->count() === 0) {
            InvoiceItem::create(['item_invoice_id' => $inv->invoice_id, 'item_deskripsi' => $prod->produk_nama, 'item_qty' => $qty, 'item_satuan' => 'unit', 'item_harga_satuan' => $prod->produk_harga_jual, 'item_subtotal' => $total]);
            $prod->decrement('produk_stok', min($qty, (int) $prod->produk_stok));
            // HPP ikut dijurnal: D5100/K1130 sebesar harga beli
            $hpp = $prod->produk_harga_beli * $qty;
            $ym = date('Ym', strtotime($tgl));
            $jual = $this->jurnal($this->nomor('TX-IT', $ym), $tgl, "Jual {$prod->produk_nama} x{$qty} ke {$cust}", 'PENJUALAN', $cust, [['1120', $total, 0], ['4100', 0, $total]], $proyekId);
            $this->jurnal($this->nomor('TX-IT', $ym), $tgl, "HPP {$prod->produk_nama} x{$qty}", 'HPP', $cust, [['5100', $hpp, 0], ['1130', 0, $hpp]], $proyekId);
            $piu = Piutang::firstOrCreate(
                ['piutang_pelanggan' => $cust, 'piutang_jumlah' => $total, 'piutang_tanggal' => $tgl],
                ['piutang_jatuh_tempo' => $tempo, 'piutang_keterangan' => $invNomor, 'piutang_terbayar' => 0, 'piutang_status' => 'BELUM LUNAS', 'piutang_jurnal_id' => $jual->jurnal_id, 'piutang_invoice_id' => $inv->invoice_id]
            );
            $inv->update(['invoice_jurnal_id' => $jual->jurnal_id]);
            if ($lunas) {
                $tglBayar = date('Y-m-28', strtotime($tgl));
                $jKas = $this->jurnal($this->nomor('TX-IT', $ym), $tglBayar, "Terima {$invNomor} {$cust}", 'PENERIMAAN', $cust, [['1100', $total, 0], ['1120', 0, $total]], $proyekId);
                $this->lunaskanPiutang($piu, $tglBayar, $jKas->jurnal_id);
            }
        }
    }
}
