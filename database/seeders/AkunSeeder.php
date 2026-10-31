<?php

namespace Database\Seeders;

use App\Models\Akun;
use Illuminate\Database\Seeder;

class AkunSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['1100', 'Kas', 'ASET', 'Aset Lancar', 'DEBIT', '', true],
            ['1110', 'Bank', 'ASET', 'Aset Lancar', 'DEBIT', '', true],
            ['1120', 'Piutang Usaha', 'ASET', 'Aset Lancar', 'DEBIT', '', false],
            ['1130', 'Persediaan Barang', 'ASET', 'Aset Lancar', 'DEBIT', '', false],
            ['1140', 'Biaya Dibayar Dimuka', 'ASET', 'Aset Lancar', 'DEBIT', '', false],
            ['1150', 'Cadangan Kerugian Piutang', 'ASET', 'Aset Lancar', 'KREDIT', '', false],
            ['1160', 'Piutang Karyawan', 'ASET', 'Aset Lancar', 'DEBIT', '', false],
            ['1180', 'PPN Masukan (Dapat Dikreditkan)', 'ASET', 'Aset Lancar', 'DEBIT', '', false],
            ['1181', 'PPh 23 Dibayar Dimuka', 'ASET', 'Aset Lancar', 'DEBIT', '', false],
            ['1182', 'Angsuran PPh Pasal 25', 'ASET', 'Aset Lancar', 'DEBIT', '', false],
            ['1183', 'PPh 22 Dibayar Dimuka', 'ASET', 'Aset Lancar', 'DEBIT', '', false],
            ['1200', 'Peralatan', 'ASET', 'Aset Tetap', 'DEBIT', '', false],
            ['1210', 'Kendaraan', 'ASET', 'Aset Tetap', 'DEBIT', '', false],
            ['1220', 'Gedung & Bangunan', 'ASET', 'Aset Tetap', 'DEBIT', '', false],
            ['1290', 'Akumulasi Penyusutan', 'ASET', 'Aset Tetap', 'KREDIT', '', false],
            ['2100', 'Hutang Usaha', 'LIABILITAS', 'Liabilitas Lancar', 'KREDIT', '', false],
            ['2110', 'Hutang Pajak', 'LIABILITAS', 'Liabilitas Lancar', 'KREDIT', '', false],
            ['2111', 'Hutang PPN', 'LIABILITAS', 'Liabilitas Lancar', 'KREDIT', '', false],
            ['2112', 'Hutang PPh Pasal 23', 'LIABILITAS', 'Liabilitas Lancar', 'KREDIT', '', false],
            ['2115', 'Hutang PPh 21', 'LIABILITAS', 'Liabilitas Lancar', 'KREDIT', '', false],
            ['2116', 'Hutang BPJS', 'LIABILITAS', 'Liabilitas Lancar', 'KREDIT', '', false],
            ['2120', 'Hutang Gaji', 'LIABILITAS', 'Liabilitas Lancar', 'KREDIT', '', false],
            ['2130', 'Pendapatan Diterima Dimuka', 'LIABILITAS', 'Liabilitas Lancar', 'KREDIT', '', false],
            ['2200', 'Hutang Bank', 'LIABILITAS', 'Liabilitas Jangka Panjang', 'KREDIT', '', false],
            ['3100', 'Modal Pemilik', 'EKUITAS', 'Ekuitas', 'KREDIT', '', false],
            ['3200', 'Laba Ditahan', 'EKUITAS', 'Ekuitas', 'KREDIT', '', false],
            ['3300', 'Prive / Penarikan Owner', 'EKUITAS', 'Ekuitas', 'DEBIT', '', false],
            ['4100', 'Pendapatan Penjualan', 'PENDAPATAN', 'Pendapatan Usaha', 'KREDIT', '', false],
            ['4150', 'Retur Penjualan', 'PENDAPATAN', 'Pendapatan Usaha', 'DEBIT', '', false],
            ['4200', 'Pendapatan Jasa', 'PENDAPATAN', 'Pendapatan Usaha', 'KREDIT', '', false],
            ['4300', 'Pendapatan Lain-lain', 'PENDAPATAN', 'Pendapatan Lain', 'KREDIT', '', false],
            ['5100', 'Harga Pokok Penjualan', 'BEBAN', 'HPP', 'DEBIT', '', false],
            ['5150', 'Retur Pembelian', 'BEBAN', 'HPP', 'KREDIT', '', false],
            ['5190', 'Koreksi Persediaan', 'BEBAN', 'HPP', 'DEBIT', '', false],
            ['6100', 'Beban Gaji', 'BEBAN', 'Beban Operasional', 'DEBIT', '', false],
            ['6101', 'Beban BPJS', 'BEBAN', 'Beban Operasional', 'DEBIT', '', false],
            ['6110', 'Beban Sewa', 'BEBAN', 'Beban Operasional', 'DEBIT', '', false],
            ['6120', 'Beban Listrik & Air', 'BEBAN', 'Beban Operasional', 'DEBIT', '', false],
            ['6130', 'Beban Penyusutan', 'BEBAN', 'Beban Penyusutan', 'DEBIT', 'PENYUSUTAN', false],
            ['6140', 'Beban Pemasaran', 'BEBAN', 'Beban Operasional', 'DEBIT', '', false],
            ['6150', 'Beban Administrasi', 'BEBAN', 'Beban Operasional', 'DEBIT', '', false],
            ['6160', 'Beban Bunga', 'BEBAN', 'Beban Bunga', 'DEBIT', 'BUNGA', false],
            ['6170', 'Beban Pajak', 'BEBAN', 'Beban Pajak', 'DEBIT', 'PAJAK', false],
            ['6180', 'Beban Lainnya', 'BEBAN', 'Beban Operasional', 'DEBIT', '', false],
            ['6190', 'Beban Kerugian Piutang', 'BEBAN', 'Beban Operasional', 'DEBIT', '', false],
        ];

        foreach ($rows as [$kode, $nama, $tipe, $subtipe, $normal, $peran, $rekening]) {
            Akun::firstOrCreate(
                ['akun_kode' => $kode],
                [
                    'akun_nama' => $nama,
                    'akun_tipe' => $tipe,
                    'akun_subtipe' => $subtipe,
                    'akun_saldo_normal' => $normal,
                    'akun_peran' => $peran === '' ? null : $peran,
                    'akun_is_rekening' => $rekening,
                ]
            );
        }
    }
}
