<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('diskon')) {
            Schema::create('diskon', function (Blueprint $table) {
                $table->increments('diskon_id');
                $table->string('diskon_kode', 20)->unique();
                $table->string('diskon_nama');
                $table->decimal('diskon_persen', 8, 2)->default(0);
                $table->boolean('diskon_aktif')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pajak')) {
            Schema::create('pajak', function (Blueprint $table) {
                $table->increments('pajak_id');
                $table->string('pajak_kode', 20)->unique();
                $table->string('pajak_nama');
                $table->decimal('pajak_persen', 8, 2)->default(0);
                $table->boolean('pajak_aktif')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pajak');
        Schema::dropIfExists('diskon');
    }
};
