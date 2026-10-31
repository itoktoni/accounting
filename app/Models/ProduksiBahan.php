<?php

namespace App\Models;

class ProduksiBahan extends BaseModel
{
    protected $table = 'produksi_bahan';

    protected $primaryKey = 'bahan_id';

    protected $fillable = [
        'bahan_produksi_id',
        'bahan_produk_id',
        'bahan_qty',
        'bahan_harga',
        'bahan_subtotal',
    ];

    protected function casts(): array
    {
        return [
            'bahan_qty' => 'integer',
            'bahan_harga' => 'integer',
            'bahan_subtotal' => 'integer',
        ];
    }

    public static $filterColumns = [
        'bahan_produk_id' => 'Produk',
    ];

    public static $sortColumns = [
        'bahan_qty',
        'bahan_subtotal',
    ];

    public static function field_name(): string
    {
        return 'bahan_id';
    }

    public function rules(): array
    {
        return [
            'bahan_produksi_id' => 'required|integer',
            'bahan_produk_id' => 'required|integer',
            'bahan_qty' => 'required|integer|min:1',
            'bahan_harga' => 'nullable|integer|min:0',
            'bahan_subtotal' => 'nullable|integer|min:0',
        ];
    }

    public function hasProduksi()
    {
        return $this->belongsTo(Produksi::class, 'bahan_produksi_id', 'produksi_id');
    }

    public function hasProduk()
    {
        return $this->belongsTo(Produk::class, 'bahan_produk_id', 'produk_id');
    }
}
