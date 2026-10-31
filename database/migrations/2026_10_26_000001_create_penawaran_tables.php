<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('penawaran')) {
            Schema::create('penawaran', function (Blueprint $table) {
                $table->increments('penawaran_id');
                $table->string('penawaran_nomor')->unique();
                $table->date('penawaran_tanggal');
                $table->date('penawaran_berlaku_sampai')->nullable();
                $table->string('penawaran_pelanggan');
                $table->string('penawaran_alamat')->nullable();
                $table->string('penawaran_telepon')->nullable();
                // PER_ITEM = pajak dihitung per baris; GLOBAL = pajak_persen atas DPP
                $table->string('penawaran_pajak_mode', 20)->default('GLOBAL');
                $table->decimal('penawaran_pajak_persen', 8, 2)->default(0);
                $table->decimal('penawaran_diskon_global', 18, 2)->default(0);
                $table->decimal('penawaran_ongkir', 18, 2)->default(0);
                $table->decimal('penawaran_biaya_lain', 18, 2)->default(0);
                $table->text('penawaran_catatan')->nullable();
                $table->string('penawaran_status', 20)->default('DRAFT');
                $table->unsignedInteger('penawaran_invoice_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('penawaran_item')) {
            Schema::create('penawaran_item', function (Blueprint $table) {
                $table->increments('item_id');
                $table->unsignedInteger('item_penawaran_id');
                $table->string('item_deskripsi');
                $table->decimal('item_qty', 18, 2)->default(1);
                $table->string('item_satuan', 20)->default('pcs');
                $table->decimal('item_harga_satuan', 18, 2)->default(0);
                $table->decimal('item_diskon_persen', 8, 2)->default(0);
                $table->decimal('item_pajak_persen', 8, 2)->default(0);
                $table->decimal('item_subtotal', 18, 2)->default(0);
                $table->timestamps();

                $table->foreign('item_penawaran_id')->references('penawaran_id')->on('penawaran')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('penawaran_item');
        Schema::dropIfExists('penawaran');
    }
};
