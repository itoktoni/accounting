<?php

namespace Database\Seeders;

use App\Models\Termin;
use Illuminate\Database\Seeder;

class TerminSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['CASH', 'Cash / Tunai', 0, 'Bayar langsung saat transaksi'],
            ['NET7', 'NET 7 Hari', 7, 'Jatuh tempo 7 hari setelah tanggal dokumen'],
            ['NET14', 'NET 14 Hari', 14, 'Jatuh tempo 14 hari setelah tanggal dokumen'],
            ['NET30', 'NET 30 Hari / 1 Bulan', 30, 'Jatuh tempo 30 hari setelah tanggal dokumen'],
            ['NET45', 'NET 45 Hari', 45, 'Jatuh tempo 45 hari setelah tanggal dokumen'],
            ['NET60', 'NET 60 Hari / 2 Bulan', 60, 'Jatuh tempo 60 hari setelah tanggal dokumen'],
        ];

        foreach ($rows as [$kode, $nama, $hari, $ket]) {
            Termin::firstOrCreate(
                ['termin_kode' => $kode],
                ['termin_nama' => $nama, 'termin_hari' => $hari, 'termin_keterangan' => $ket, 'termin_aktif' => true]
            );
        }
    }
}
