<?php

namespace Database\Seeders;

use App\Models\Akun;
use App\Models\Anggaran;
use App\Models\AsetTetap;
use App\Models\Customer;
use App\Models\DetailJurnal;
use App\Models\Hutang;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Jurnal;
use App\Models\Karyawan;
use App\Models\Payroll;
use App\Models\Piutang;
use App\Models\PoItem;
use App\Models\Produk;
use App\Models\Proyek;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Voucher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AccountingDummySeeder extends Seeder
{
    public function run(): void
    {
        // ---- Users (login pakai email + password 'password') ----
        $users = [
            ['email' => 'admin@akuntansi.test', 'name' => 'Administrator', 'role' => 'admin'],
            ['email' => 'akuntan@akuntansi.test', 'name' => 'Staff Akuntan', 'role' => 'editor'],
            ['email' => 'kasir@akuntansi.test', 'name' => 'Kasir Toko', 'role' => 'user'],
        ];
        foreach ($users as $u) {
            User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'role' => $u['role'],
                    'password' => Hash::make('password'),
                    'verified_at' => now(),
                    'email_verified_at' => now(),
                ]
            );
        }

        // ---- Produk: dikelola TransaksiITSeeder (perusahaan IT, bukan kelontong) ----

        // ---- Customer & Vendor ----
        foreach (['Toko Berkah Jaya', 'Kafe Aroma', 'PT Maju Bersama'] as $nama) {
            Customer::firstOrCreate(['customer_nama' => $nama]);
        }
        foreach (['Supplier Kopi Nusantara', 'Distributor Susu Segar', 'Toko Kemasan Indo'] as $nama) {
            Vendor::firstOrCreate(['vendor_nama' => $nama]);
        }

        // ---- Jurnal + Detail (balance) ----
        $akunId = fn (string $kode) => Akun::where('akun_kode', $kode)->value('akun_id');
        $jurnals = [
            [
                'nomor' => 'TX-202601-0001', 'tanggal' => '2026-01-05',
                'keterangan' => 'Setoran modal awal pemilik', 'tipe' => 'MODAL',
                'lines' => [['1100', 50000000, 0], ['3100', 0, 50000000]],
            ],
            [
                'nomor' => 'TX-202602-0001', 'tanggal' => '2026-02-10',
                'keterangan' => 'Penjualan tunai Kafe Aroma', 'tipe' => 'PENJUALAN', 'pihak' => 'Kafe Aroma',
                'lines' => [['1100', 3750000, 0], ['4100', 0, 3750000]],
            ],
            [
                'nomor' => 'TX-202603-0001', 'tanggal' => '2026-03-12',
                'keterangan' => 'Pembelian biji kopi Supplier Kopi Nusantara', 'tipe' => 'PEMBELIAN', 'pihak' => 'Supplier Kopi Nusantara',
                'lines' => [['1130', 6000000, 0], ['2100', 0, 6000000]],
            ],
            [
                'nomor' => 'TX-202604-0001', 'tanggal' => '2026-04-02',
                'keterangan' => 'Bayar sewa ruko April', 'tipe' => 'PENGELUARAN',
                'lines' => [['6110', 2500000, 0], ['1100', 0, 2500000]],
            ],
            [
                'nomor' => 'TX-202605-0001', 'tanggal' => '2026-05-15',
                'keterangan' => 'Penjualan kredit Toko Berkah Jaya', 'tipe' => 'PENJUALAN', 'pihak' => 'Toko Berkah Jaya',
                'lines' => [['1120', 5200000, 0], ['4100', 0, 5200000]],
            ],
        ];
        foreach ($jurnals as $j) {
            $jurnal = Jurnal::firstOrCreate(
                ['jurnal_nomor_tx' => $j['nomor']],
                [
                    'jurnal_tanggal' => $j['tanggal'],
                    'jurnal_keterangan' => $j['keterangan'],
                    'jurnal_kategori' => 'OPERASIONAL',
                    'jurnal_tipe_tx' => $j['tipe'],
                    'jurnal_pihak' => $j['pihak'] ?? '',
                ]
            );
            if ($jurnal->hasDetails()->count() === 0) {
                foreach ($j['lines'] as [$kode, $d, $k]) {
                    if ($id = $akunId($kode)) {
                        DetailJurnal::create([
                            'detail_jurnal_id' => $jurnal->jurnal_id,
                            'detail_akun_id' => $id,
                            'detail_debit' => $d,
                            'detail_kredit' => $k,
                        ]);
                    }
                }
            }
        }

        // ---- Piutang & Hutang ----
        Piutang::firstOrCreate(
            ['piutang_pelanggan' => 'Toko Berkah Jaya', 'piutang_jumlah' => 5200000],
            [
                'piutang_tanggal' => '2026-05-15',
                'piutang_jatuh_tempo' => '2026-06-15',
                'piutang_keterangan' => 'Faktur penjualan Mei',
                'piutang_terbayar' => 2000000,
                'piutang_status' => 'BELUM LUNAS',
            ]
        );
        Piutang::firstOrCreate(
            ['piutang_pelanggan' => 'PT Maju Bersama', 'piutang_jumlah' => 3100000],
            [
                'piutang_tanggal' => '2026-06-01',
                'piutang_jatuh_tempo' => '2026-07-01',
                'piutang_keterangan' => 'Jasa + barang Juni',
                'piutang_terbayar' => 0,
                'piutang_status' => 'BELUM LUNAS',
            ]
        );
        Hutang::firstOrCreate(
            ['hutang_pemasok' => 'Supplier Kopi Nusantara', 'hutang_jumlah' => 6000000],
            [
                'hutang_tanggal' => '2026-03-12',
                'hutang_jatuh_tempo' => '2026-04-12',
                'hutang_keterangan' => 'PO biji kopi Maret',
                'hutang_terbayar' => 3000000,
                'hutang_status' => 'BELUM LUNAS',
                'hutang_akun_kode' => '2100',
            ]
        );
        Hutang::firstOrCreate(
            ['hutang_pemasok' => 'Distributor Susu Segar', 'hutang_jumlah' => 1800000],
            [
                'hutang_tanggal' => '2026-06-05',
                'hutang_jatuh_tempo' => '2026-07-05',
                'hutang_keterangan' => 'Susu UHT Juni',
                'hutang_terbayar' => 0,
                'hutang_status' => 'BELUM LUNAS',
                'hutang_akun_kode' => '2100',
            ]
        );

        // ---- Invoice + items ----
        $inv = Invoice::firstOrCreate(
            ['invoice_nomor' => 'INV-2026-0001'],
            [
                'invoice_tanggal' => '2026-06-10',
                'invoice_jatuh_tempo' => '2026-07-10',
                'invoice_pelanggan' => 'Toko Berkah Jaya',
                'invoice_status' => 'POSTED',
            ]
        );
        if ($inv->hasItems()->count() === 0) {
            InvoiceItem::create(['item_invoice_id' => $inv->invoice_id, 'item_deskripsi' => 'Kopi Arabika 250g x20', 'item_qty' => 20, 'item_satuan' => 'pcs', 'item_harga_satuan' => 75000, 'item_subtotal' => 1500000]);
            InvoiceItem::create(['item_invoice_id' => $inv->invoice_id, 'item_deskripsi' => 'House Blend 1kg x5', 'item_qty' => 5, 'item_satuan' => 'pcs', 'item_harga_satuan' => 180000, 'item_subtotal' => 900000]);
        }

        // ---- PO + items ----
        $po = PurchaseOrder::firstOrCreate(
            ['po_nomor' => 'PO-2026-0001'],
            [
                'po_tanggal' => '2026-06-12',
                'po_vendor' => 'Supplier Kopi Nusantara',
                'po_status' => 'DRAFT',
            ]
        );
        if ($po->hasItems()->count() === 0) {
            PoItem::create(['poi_po_id' => $po->po_id, 'poi_deskripsi' => 'Biji Arabika 50kg', 'poi_qty' => 50, 'poi_satuan' => 'kg', 'poi_harga_satuan' => 120000, 'poi_subtotal' => 6000000]);
        }

        // ---- Karyawan + Payroll bulan berjalan ----
        $kar = Karyawan::firstOrCreate(
            ['karyawan_nama' => 'Budi Santoso'],
            ['karyawan_no' => 'K001', 'karyawan_jabatan' => 'Barista', 'karyawan_gaji_pokok' => 3500000, 'karyawan_tunjangan' => 500000, 'karyawan_aktif' => 1]
        );
        Karyawan::firstOrCreate(
            ['karyawan_nama' => 'Siti Rahayu'],
            ['karyawan_no' => 'K002', 'karyawan_jabatan' => 'Akuntan', 'karyawan_gaji_pokok' => 5000000, 'karyawan_tunjangan' => 750000, 'karyawan_aktif' => 1]
        );
        Payroll::firstOrCreate(
            ['payroll_karyawan_id' => $kar->karyawan_id, 'payroll_bulan' => (int) date('m'), 'payroll_tahun' => (int) date('Y')],
            ['payroll_gaji_pokok' => 3500000, 'payroll_tunjangan' => 500000, 'payroll_gaji_bersih' => 4000000]
        );

        // ---- Aset, Proyek, Voucher, Anggaran ----
        AsetTetap::firstOrCreate(
            ['aset_nama' => 'Mesin Espresso'],
            ['aset_kategori' => 'Peralatan', 'aset_harga_beli' => 25000000, 'aset_tanggal_beli' => '2025-01-10', 'aset_masa_pakai' => 60, 'aset_aktif' => 1]
        );
        Proyek::firstOrCreate(
            ['proyek_kode' => 'PRJ-001'],
            ['proyek_nama' => 'Pembukaan Cabang Kedua', 'proyek_pelanggan' => '', 'proyek_nilai_kontrak' => 100000000, 'proyek_status' => 'AKTIF']
        );
        Voucher::firstOrCreate(
            ['voucher_kode' => 'HEMAT10K'],
            ['voucher_tipe' => 'NOMINAL', 'voucher_nilai' => 10000, 'voucher_min_belanja' => 50000, 'voucher_kuota' => 100, 'voucher_aktif' => 1, 'voucher_catatan' => 'Promo pembuka']
        );
        $bln = (int) date('m');
        $thn = (int) date('Y');
        Anggaran::firstOrCreate(
            ['anggaran_bulan' => $bln, 'anggaran_tahun' => $thn, 'anggaran_akun_kode' => '6110'],
            ['anggaran_nominal' => 3000000, 'anggaran_catatan' => 'Sewa bulanan']
        );
    }
}
