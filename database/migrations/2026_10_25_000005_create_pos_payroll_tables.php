<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pos_shift')) {
            Schema::create('pos_shift', function (Blueprint $table) {
                $table->bigIncrements('shift_id');
                $table->unsignedBigInteger('shift_user_id')->nullable();
                $table->string('shift_username')->default('');
                $table->dateTime('shift_dibuka')->nullable();
                $table->dateTime('shift_ditutup')->nullable();
                $table->decimal('shift_saldo_awal', 15, 2)->default(0);
                $table->decimal('shift_kas_sistem', 15, 2)->default(0);
                $table->decimal('shift_kas_fisik', 15, 2)->default(0);
                $table->decimal('shift_selisih', 15, 2)->default(0);
                $table->string('shift_status', 20)->default('OPEN');
                $table->string('shift_catatan')->default('');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pos_sale')) {
            Schema::create('pos_sale', function (Blueprint $table) {
                $table->bigIncrements('sale_id');
                $table->unsignedBigInteger('sale_jurnal_id')->nullable();
                $table->unsignedBigInteger('sale_shift_id')->nullable();
                $table->string('sale_nomor_struk')->unique();
                $table->date('sale_tanggal')->nullable();
                $table->string('sale_customer')->default('');
                $table->string('sale_metode_bayar', 20)->default('TUNAI');
                $table->string('sale_akun_kas', 20)->default('1100');
                $table->decimal('sale_total', 15, 2)->default(0);
                $table->decimal('sale_bayar', 15, 2)->default(0);
                $table->decimal('sale_kembalian', 15, 2)->default(0);
                $table->string('sale_status', 20)->default('SELESAI');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('voucher')) {
            Schema::create('voucher', function (Blueprint $table) {
                $table->bigIncrements('voucher_id');
                $table->string('voucher_kode')->unique();
                $table->string('voucher_tipe', 20)->default('NOMINAL');
                $table->decimal('voucher_nilai', 15, 2)->default(0);
                $table->decimal('voucher_min_belanja', 15, 2)->default(0);
                $table->integer('voucher_kuota')->default(0);
                $table->integer('voucher_terpakai')->default(0);
                $table->date('voucher_berlaku_dari')->nullable();
                $table->date('voucher_berlaku_sampai')->nullable();
                $table->boolean('voucher_aktif')->default(1);
                $table->string('voucher_catatan')->default('');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('karyawan')) {
            Schema::create('karyawan', function (Blueprint $table) {
                $table->bigIncrements('karyawan_id');
                $table->string('karyawan_no')->default('');
                $table->string('karyawan_nama');
                $table->string('karyawan_jabatan')->default('');
                $table->string('karyawan_status_kerja', 20)->default('TETAP');
                $table->decimal('karyawan_gaji_pokok', 15, 2)->default(0);
                $table->decimal('karyawan_tunjangan', 15, 2)->default(0);
                $table->boolean('karyawan_aktif')->default(1);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('payroll')) {
            Schema::create('payroll', function (Blueprint $table) {
                $table->bigIncrements('payroll_id');
                $table->unsignedBigInteger('payroll_karyawan_id');
                $table->integer('payroll_bulan');
                $table->integer('payroll_tahun');
                $table->decimal('payroll_gaji_pokok', 15, 2)->default(0);
                $table->decimal('payroll_tunjangan', 15, 2)->default(0);
                $table->decimal('payroll_bonus', 15, 2)->default(0);
                $table->decimal('payroll_potongan', 15, 2)->default(0);
                $table->decimal('payroll_gaji_bersih', 15, 2)->default(0);
                $table->unsignedBigInteger('payroll_jurnal_id')->nullable();
                $table->string('payroll_catatan')->default('');
                $table->timestamps();
                $table->unique(['payroll_karyawan_id', 'payroll_bulan', 'payroll_tahun']);
            });
        }

        if (! Schema::hasTable('aset_tetap')) {
            Schema::create('aset_tetap', function (Blueprint $table) {
                $table->bigIncrements('aset_id');
                $table->string('aset_nama');
                $table->string('aset_kategori')->nullable();
                $table->decimal('aset_harga_beli', 15, 2)->default(0);
                $table->date('aset_tanggal_beli')->nullable();
                $table->integer('aset_masa_pakai')->default(12);
                $table->decimal('aset_penyusutan_bulan', 15, 2)->nullable();
                $table->decimal('aset_akumulasi', 15, 2)->default(0);
                $table->boolean('aset_aktif')->default(1);
                $table->unsignedBigInteger('aset_jurnal_id')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('aset_tetap');
        Schema::dropIfExists('payroll');
        Schema::dropIfExists('karyawan');
        Schema::dropIfExists('voucher');
        Schema::dropIfExists('pos_sale');
        Schema::dropIfExists('pos_shift');
    }
};
