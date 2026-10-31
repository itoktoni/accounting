<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('invoice')) {
            Schema::create('invoice', function (Blueprint $table) {
                $table->id('invoice_id');
                $table->string('invoice_nomor')->unique();
                $table->date('invoice_tanggal');
                $table->date('invoice_jatuh_tempo')->nullable();
                $table->integer('invoice_top_hari')->nullable();
                $table->string('invoice_pelanggan');
                $table->text('invoice_alamat')->nullable();
                $table->string('invoice_telepon')->nullable();
                $table->decimal('invoice_diskon', 15, 2)->default(0);
                $table->decimal('invoice_ongkir', 15, 2)->default(0);
                $table->decimal('invoice_biaya_lain', 15, 2)->default(0);
                $table->decimal('invoice_pajak_persen', 5, 2)->default(0);
                $table->decimal('invoice_pajak_nominal', 15, 2)->default(0);
                $table->text('invoice_catatan')->nullable();
                $table->string('invoice_status')->default('DRAFT');
                $table->unsignedBigInteger('invoice_jurnal_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('invoice_item')) {
            Schema::create('invoice_item', function (Blueprint $table) {
                $table->id('item_id');
                $table->unsignedBigInteger('item_invoice_id');
                $table->string('item_deskripsi');
                $table->decimal('item_qty', 15, 2)->default(1);
                $table->string('item_satuan')->default('pcs');
                $table->decimal('item_harga_satuan', 15, 2)->default(0);
                $table->decimal('item_diskon', 15, 2)->default(0);
                $table->decimal('item_subtotal', 15, 2)->default(0);
                $table->timestamps();
                $table->foreign('item_invoice_id')->references('invoice_id')->on('invoice')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('purchase_order')) {
            Schema::create('purchase_order', function (Blueprint $table) {
                $table->id('po_id');
                $table->string('po_nomor')->unique();
                $table->date('po_tanggal');
                $table->date('po_expected_date')->nullable();
                $table->string('po_vendor');
                $table->text('po_alamat')->nullable();
                $table->string('po_telepon')->nullable();
                $table->decimal('po_diskon', 15, 2)->default(0);
                $table->decimal('po_ongkir', 15, 2)->default(0);
                $table->decimal('po_biaya_lain', 15, 2)->default(0);
                $table->text('po_catatan')->nullable();
                $table->string('po_status')->default('DRAFT');
                $table->unsignedBigInteger('po_hutang_id')->nullable();
                $table->unsignedBigInteger('po_jurnal_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('po_item')) {
            Schema::create('po_item', function (Blueprint $table) {
                $table->id('poi_id');
                $table->unsignedBigInteger('poi_po_id');
                $table->unsignedBigInteger('poi_produk_id')->nullable();
                $table->string('poi_deskripsi');
                $table->decimal('poi_qty', 15, 2)->default(1);
                $table->string('poi_satuan')->default('pcs');
                $table->decimal('poi_harga_satuan', 15, 2)->default(0);
                $table->decimal('poi_diskon', 15, 2)->default(0);
                $table->decimal('poi_subtotal', 15, 2)->default(0);
                $table->timestamps();
                $table->foreign('poi_po_id')->references('po_id')->on('purchase_order')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('proyek')) {
            Schema::create('proyek', function (Blueprint $table) {
                $table->id('proyek_id');
                $table->string('proyek_kode')->unique();
                $table->string('proyek_nama');
                $table->string('proyek_pelanggan')->default('');
                $table->decimal('proyek_nilai_kontrak', 15, 2)->default(0);
                $table->decimal('proyek_anggaran_biaya', 15, 2)->default(0);
                $table->date('proyek_tanggal_mulai')->nullable();
                $table->date('proyek_target_selesai')->nullable();
                $table->string('proyek_status')->default('AKTIF');
                $table->text('proyek_catatan')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('anggaran')) {
            Schema::create('anggaran', function (Blueprint $table) {
                $table->id('anggaran_id');
                $table->integer('anggaran_bulan');
                $table->integer('anggaran_tahun');
                $table->string('anggaran_akun_kode');
                $table->decimal('anggaran_nominal', 15, 2)->default(0);
                $table->text('anggaran_catatan')->nullable();
                $table->timestamps();
                $table->unique(['anggaran_bulan', 'anggaran_tahun', 'anggaran_akun_kode'], 'anggaran_bulan_tahun_akun_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('po_item');
        Schema::dropIfExists('purchase_order');
        Schema::dropIfExists('invoice_item');
        Schema::dropIfExists('invoice');
        Schema::dropIfExists('proyek');
        Schema::dropIfExists('anggaran');
    }
};
