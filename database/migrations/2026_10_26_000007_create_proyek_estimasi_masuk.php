<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('proyek_estimasi_pendapatan')) {
            Schema::create('proyek_estimasi_pendapatan', function (Blueprint $table) {
                $table->id('estimasi_masuk_id');
                $table->unsignedBigInteger('estimasi_masuk_proyek_id');
                $table->string('estimasi_masuk_deskripsi');
                $table->decimal('estimasi_masuk_qty', 18, 2)->default(1);
                $table->string('estimasi_masuk_satuan', 20)->default('paket');
                $table->decimal('estimasi_masuk_harga', 18, 2)->default(0);
                $table->decimal('estimasi_masuk_subtotal', 18, 2)->default(0);
                $table->timestamps();

                $table->foreign('estimasi_masuk_proyek_id')->references('proyek_id')->on('proyek')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('proyek_estimasi_pendapatan');
    }
};
