<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('termin')) {
            Schema::create('termin', function (Blueprint $table) {
                $table->increments('termin_id');
                $table->string('termin_kode', 20)->unique();
                $table->string('termin_nama');
                // jumlah hari jatuh tempo terhitung sejak tanggal dokumen; 0 = tunai
                $table->unsignedInteger('termin_hari')->default(0);
                $table->string('termin_keterangan')->nullable();
                $table->boolean('termin_aktif')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('penawaran', 'penawaran_termin_kode')) {
            Schema::table('penawaran', function (Blueprint $table) {
                $table->string('penawaran_termin_kode', 20)->nullable()->after('penawaran_berlaku_sampai');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('penawaran', 'penawaran_termin_kode')) {
            Schema::table('penawaran', function (Blueprint $table) {
                $table->dropColumn('penawaran_termin_kode');
            });
        }
        Schema::dropIfExists('termin');
    }
};
