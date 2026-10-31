<?php

namespace App\Models;

class Produk extends BaseModel
{
    protected $table = 'produk';

    protected $primaryKey = 'produk_id';

    protected $fillable = [
        'produk_kode',
        'produk_nama',
        'produk_varian',
        'produk_satuan',
        'produk_kategori',
        'produk_harga_beli',
        'produk_harga_jual',
        'produk_stok',
        'produk_min_stok',
        'produk_barcode',
        'produk_favorit',
        'produk_pos_tampil',
        'produk_aktif',
    ];

    protected function casts(): array
    {
        return [
            'produk_harga_beli' => 'integer',
            'produk_harga_jual' => 'integer',
            'produk_stok' => 'integer',
            'produk_min_stok' => 'integer',
            'produk_favorit' => 'boolean',
            'produk_pos_tampil' => 'boolean',
            'produk_aktif' => 'boolean',
        ];
    }

    public static $filterColumns = [
        'produk_kode' => 'Kode',
        'produk_nama' => 'Nama',
        'produk_kategori' => 'Kategori',
        'produk_barcode' => 'Barcode',
    ];

    public static $sortColumns = [
        'produk_kode',
        'produk_nama',
        'produk_kategori',
        'produk_harga_jual',
        'produk_stok',
    ];

    public static function field_name(): string
    {
        return 'produk_nama';
    }

    public function rules(): array
    {
        return [
            'produk_kode' => 'required|string|max:50',
            'produk_nama' => 'required|string|max:255',
            'produk_varian' => 'nullable|string|max:255',
            'produk_satuan' => 'nullable|string|max:20',
            'produk_kategori' => 'nullable|string|max:100',
            'produk_harga_beli' => 'nullable|integer|min:0',
            'produk_harga_jual' => 'nullable|integer|min:0',
            'produk_stok' => 'nullable|integer',
            'produk_min_stok' => 'nullable|integer|min:0',
            'produk_barcode' => 'nullable|string|max:100',
            'produk_favorit' => 'nullable|boolean',
            'produk_pos_tampil' => 'nullable|boolean',
            'produk_aktif' => 'nullable|boolean',
        ];
    }

    public function hasPergerakan()
    {
        return $this->hasMany(PergerakanStok::class, 'stok_produk_id', 'produk_id');
    }

    public function hasBahanDipakai()
    {
        return $this->hasMany(ProduksiBahan::class, 'bahan_produk_id', 'produk_id');
    }

    public function hasProduksiJadi()
    {
        return $this->hasMany(Produksi::class, 'produksi_produk_jadi_id', 'produk_id');
    }
}
