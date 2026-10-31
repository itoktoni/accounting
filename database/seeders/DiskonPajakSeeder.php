<?php

namespace Database\Seeders;

use App\Models\Diskon;
use App\Models\Pajak;
use Illuminate\Database\Seeder;

class DiskonPajakSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['D0', 'Tanpa Diskon', 0], ['D5', 'Diskon 5%', 5], ['D10', 'Diskon 10%', 10], ['D15', 'Diskon 15%', 15], ['D20', 'Diskon 20%', 20], ['D25', 'Diskon 25%', 25], ['D50', 'Diskon 50%', 50], ['D100', 'Diskon 100%', 100]] as [$kode, $nama, $p]) {
            Diskon::firstOrCreate(['diskon_kode' => $kode], ['diskon_nama' => $nama, 'diskon_persen' => $p, 'diskon_aktif' => true]);
        }

        foreach ([['NON', 'Tanpa Pajak', 0], ['PPN10', 'PPN 10%', 10], ['PPN11', 'PPN 11%', 11], ['PPN12', 'PPN 12%', 12]] as [$kode, $nama, $p]) {
            Pajak::firstOrCreate(['pajak_kode' => $kode], ['pajak_nama' => $nama, 'pajak_persen' => $p, 'pajak_aktif' => true]);
        }
    }
}
