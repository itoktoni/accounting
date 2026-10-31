<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('produk')) {
            Schema::create('produk', function (Blueprint $table) {
                $table->increments('produk_id');
                $table->string('produk_kode', 50)->unique();
                $table->string('produk_nama');
                $table->string('produk_varian')->nullable();
                $table->string('produk_satuan', 20)->default('pcs');
                $table->string('produk_kategori', 100)->default('');
                $table->bigInteger('produk_harga_beli')->default(0);
                $table->bigInteger('produk_harga_jual')->default(0);
                $table->integer('produk_stok')->default(0);
                $table->integer('produk_min_stok')->default(0);
                $table->string('produk_barcode', 100)->default('');
                $table->boolean('produk_favorit')->default(0);
                $table->boolean('produk_pos_tampil')->default(1);
                $table->boolean('produk_aktif')->default(1);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pergerakan_stok')) {
            Schema::create('pergerakan_stok', function (Blueprint $table) {
                $table->increments('stok_id');
                $table->unsignedInteger('stok_produk_id');
                $table->date('stok_tanggal');
                $table->string('stok_jenis', 20);
                $table->integer('stok_qty');
                $table->bigInteger('stok_harga')->default(0);
                $table->string('stok_keterangan')->nullable();
                $table->unsignedInteger('stok_jurnal_id')->nullable();
                $table->timestamps();

                $table->foreign('stok_produk_id')->references('produk_id')->on('produk')->cascadeOnDelete();
            });
        }

        if (! Schema::hasTable('produksi_template')) {
            Schema::create('produksi_template', function (Blueprint $table) {
                $table->increments('template_id');
                $table->string('template_nama');
                $table->unsignedInteger('template_produk_jadi_id')->nullable();
                $table->integer('template_qty_hasil')->default(0);
                $table->bigInteger('template_biaya_tambahan')->default(0);
                $table->bigInteger('template_harga_jual')->default(0);
                $table->string('template_kategori', 100)->default('');
                $table->boolean('template_pos_aktif')->default(0);
                $table->timestamps();

                $table->foreign('template_produk_jadi_id')->references('produk_id')->on('produk')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('produksi')) {
            Schema::create('produksi', function (Blueprint $table) {
                $table->increments('produksi_id');
                $table->date('produksi_tanggal');
                $table->unsignedInteger('produksi_produk_jadi_id');
                $table->integer('produksi_qty_hasil');
                $table->bigInteger('produksi_biaya_tambahan')->default(0);
                $table->bigInteger('produksi_total_hpp')->default(0);
                $table->bigInteger('produksi_hpp_per_unit')->default(0);
                $table->string('produksi_keterangan')->nullable();
                $table->unsignedInteger('produksi_jurnal_id')->nullable();
                $table->timestamps();

                $table->foreign('produksi_produk_jadi_id')->references('produk_id')->on('produk')->restrictOnDelete();
            });
        }

        if (! Schema::hasTable('produksi_bahan')) {
            Schema::create('produksi_bahan', function (Blueprint $table) {
                $table->increments('bahan_id');
                $table->unsignedInteger('bahan_produksi_id');
                $table->unsignedInteger('bahan_produk_id');
                $table->integer('bahan_qty');
                $table->bigInteger('bahan_harga')->default(0);
                $table->bigInteger('bahan_subtotal')->default(0);
                $table->timestamps();

                $table->foreign('bahan_produksi_id')->references('produksi_id')->on('produksi')->cascadeOnDelete();
                $table->foreign('bahan_produk_id')->references('produk_id')->on('produk')->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('produksi_bahan');
        Schema::dropIfExists('produksi');
        Schema::dropIfExists('produksi_template');
        Schema::dropIfExists('pergerakan_stok');
        Schema::dropIfExists('produk');
    }
};
