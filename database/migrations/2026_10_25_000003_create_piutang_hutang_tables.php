<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable("customer")) {
            Schema::create("customer", function (Blueprint $table) {
                $table->id("customer_id");
                $table->string("customer_nama")->unique();
                $table->string("customer_telepon")->default("");
                $table->text("customer_alamat")->default("");
                $table->text("customer_catatan")->default("");
                $table->timestamps();
            });
        }

        if (! Schema::hasTable("vendor")) {
            Schema::create("vendor", function (Blueprint $table) {
                $table->id("vendor_id");
                $table->string("vendor_nama")->unique();
                $table->string("vendor_telepon")->default("");
                $table->text("vendor_alamat")->default("");
                $table->text("vendor_catatan")->default("");
                $table->timestamps();
            });
        }

        if (! Schema::hasTable("piutang")) {
            Schema::create("piutang", function (Blueprint $table) {
                $table->id("piutang_id");
                $table->date("piutang_tanggal");
                $table->date("piutang_jatuh_tempo")->nullable();
                $table->string("piutang_pelanggan");
                $table->text("piutang_keterangan")->nullable();
                $table->decimal("piutang_jumlah", 15, 2);
                $table->decimal("piutang_terbayar", 15, 2)->default(0);
                $table->string("piutang_status")->default("BELUM LUNAS");
                $table->unsignedBigInteger("piutang_jurnal_id")->nullable();
                $table->unsignedBigInteger("piutang_invoice_id")->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable("hutang")) {
            Schema::create("hutang", function (Blueprint $table) {
                $table->id("hutang_id");
                $table->date("hutang_tanggal");
                $table->date("hutang_jatuh_tempo")->nullable();
                $table->string("hutang_pemasok");
                $table->text("hutang_keterangan")->nullable();
                $table->decimal("hutang_jumlah", 15, 2);
                $table->decimal("hutang_terbayar", 15, 2)->default(0);
                $table->string("hutang_status")->default("BELUM LUNAS");
                $table->unsignedBigInteger("hutang_jurnal_id")->nullable();
                $table->string("hutang_akun_kode")->default("2100");
                $table->timestamps();
            });
        }

        if (! Schema::hasTable("bayar_piutang")) {
            Schema::create("bayar_piutang", function (Blueprint $table) {
                $table->id("bayar_id");
                $table->foreignId("bayar_piutang_id")->constrained("piutang", "piutang_id")->cascadeOnDelete();
                $table->date("bayar_tanggal");
                $table->decimal("bayar_jumlah", 15, 2);
                $table->text("bayar_catatan")->nullable();
                $table->unsignedBigInteger("bayar_jurnal_id")->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable("bayar_hutang")) {
            Schema::create("bayar_hutang", function (Blueprint $table) {
                $table->id("bh_id");
                $table->foreignId("bh_hutang_id")->constrained("hutang", "hutang_id")->cascadeOnDelete();
                $table->date("bh_tanggal");
                $table->decimal("bh_jumlah", 15, 2);
                $table->text("bh_catatan")->nullable();
                $table->unsignedBigInteger("bh_jurnal_id")->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists("bayar_hutang");
        Schema::dropIfExists("bayar_piutang");
        Schema::dropIfExists("hutang");
        Schema::dropIfExists("piutang");
        Schema::dropIfExists("vendor");
        Schema::dropIfExists("customer");
    }
};
