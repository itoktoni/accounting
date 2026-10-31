<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(AkunSeeder::class);
        $this->call(AccountingDummySeeder::class);
        $this->call(TransaksiITSeeder::class);
        $this->call(TerminSeeder::class);
        $this->call(DiskonPajakSeeder::class);
        $this->call(MenuSeeder::class);
    }
}
