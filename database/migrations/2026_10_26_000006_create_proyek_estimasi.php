<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('proyek_estimasi')) {
            Schema::create('proyek_estimasi', function (Blueprint $table) {
                $table->id('estimasi_id');
                $table->unsignedBigInteger('estimasi_proyek_id');
                $table->string('estimasi_deskripsi');
                $table->decimal('estimasi_qty', 18, 2)->default(1);
                $table->string('estimasi_satuan', 20)->default('paket');
                $table->decimal('estimasi_harga', 18, 2)->default(0);
                $table->decimal('estimasi_subtotal', 18, 2)->default(0);
                $table->timestamps();

                $table->foreign('estimasi_proyek_id')->references('proyek_id')->on('proyek')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('proyek_estimasi');
    }
};
