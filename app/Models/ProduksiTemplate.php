<?php

namespace App\Models;

class ProduksiTemplate extends BaseModel
{
    protected $table = 'produksi_template';

    protected $primaryKey = 'template_id';

    protected $fillable = [
        'template_nama',
        'template_produk_jadi_id',
        'template_qty_hasil',
        'template_biaya_tambahan',
        'template_harga_jual',
        'template_kategori',
        'template_pos_aktif',
    ];

    protected function casts(): array
    {
        return [
            'template_qty_hasil' => 'integer',
            'template_biaya_tambahan' => 'integer',
            'template_harga_jual' => 'integer',
            'template_pos_aktif' => 'boolean',
        ];
    }

    public static $filterColumns = [
        'template_nama' => 'Nama',
        'template_kategori' => 'Kategori',
    ];

    public static $sortColumns = [
        'template_nama',
        'template_kategori',
        'template_qty_hasil',
    ];

    public static function field_name(): string
    {
        return 'template_nama';
    }

    public function rules(): array
    {
        return [
            'template_nama' => 'required|string|max:255',
            'template_produk_jadi_id' => 'nullable|integer',
            'template_qty_hasil' => 'nullable|integer|min:0',
            'template_biaya_tambahan' => 'nullable|integer|min:0',
            'template_harga_jual' => 'nullable|integer|min:0',
            'template_kategori' => 'nullable|string|max:100',
            'template_pos_aktif' => 'nullable|boolean',
        ];
    }

    public function hasProdukJadi()
    {
        return $this->belongsTo(Produk::class, 'template_produk_jadi_id', 'produk_id');
    }
}
