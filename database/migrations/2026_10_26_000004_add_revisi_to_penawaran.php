<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('penawaran', 'penawaran_revisi')) {
            Schema::table('penawaran', function (Blueprint $table) {
                $table->unsignedInteger('penawaran_revisi')->default(0)->after('penawaran_status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('penawaran', 'penawaran_revisi')) {
            Schema::table('penawaran', function (Blueprint $table) {
                $table->dropColumn('penawaran_revisi');
            });
        }
    }
};
