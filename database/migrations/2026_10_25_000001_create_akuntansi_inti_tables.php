<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('akun')) {
            Schema::create('akun', function (Blueprint $table) {
                $table->increments('akun_id');
                $table->string('akun_kode', 20)->unique();
                $table->string('akun_nama');
                $table->string('akun_tipe', 20);
                $table->string('akun_subtipe', 100)->nullable();
                $table->string('akun_saldo_normal', 10)->default('DEBIT');
                $table->string('akun_peran', 50)->nullable();
                $table->boolean('akun_is_rekening')->default(false);
                $table->string('akun_no_rekening', 100)->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('jurnal')) {
            Schema::create('jurnal', function (Blueprint $table) {
                $table->increments('jurnal_id');
                $table->date('jurnal_tanggal');
                $table->text('jurnal_keterangan');
                $table->string('jurnal_referensi')->nullable();
                $table->string('jurnal_kategori', 100)->default('OPERASIONAL');
                $table->string('jurnal_tipe_tx', 50)->default('JURNAL');
                $table->string('jurnal_nomor_tx', 100)->nullable()->unique();
                $table->string('jurnal_pihak')->default('');
                $table->integer('jurnal_proyek_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('detail_jurnal')) {
            Schema::create('detail_jurnal', function (Blueprint $table) {
                $table->increments('detail_id');
                $table->unsignedInteger('detail_jurnal_id');
                $table->unsignedInteger('detail_akun_id');
                $table->decimal('detail_debit', 18, 2)->default(0);
                $table->decimal('detail_kredit', 18, 2)->default(0);
                $table->timestamps();

                $table->foreign('detail_jurnal_id')->references('jurnal_id')->on('jurnal')->cascadeOnDelete();
                $table->foreign('detail_akun_id')->references('akun_id')->on('akun')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_jurnal');
        Schema::dropIfExists('jurnal');
        Schema::dropIfExists('akun');
    }
};
