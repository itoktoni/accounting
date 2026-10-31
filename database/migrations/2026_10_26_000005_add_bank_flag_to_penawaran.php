<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('penawaran', 'penawaran_tampil_bank')) {
            Schema::table('penawaran', function (Blueprint $table) {
                $table->boolean('penawaran_tampil_bank')->default(true)->after('penawaran_revisi');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('penawaran', 'penawaran_tampil_bank')) {
            Schema::table('penawaran', function (Blueprint $table) {
                $table->dropColumn('penawaran_tampil_bank');
            });
        }
    }
};
