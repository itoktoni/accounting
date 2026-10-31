<?php

namespace App\Models;

class PergerakanStok extends BaseModel
{
    protected $table = 'pergerakan_stok';

    protected $primaryKey = 'stok_id';

    protected $fillable = [
        'stok_produk_id',
        'stok_tanggal',
        'stok_jenis',
        'stok_qty',
        'stok_harga',
        'stok_keterangan',
        'stok_jurnal_id',
    ];

    protected function casts(): array
    {
        return [
            'stok_tanggal' => 'date',
            'stok_qty' => 'integer',
            'stok_harga' => 'integer',
        ];
    }

    public static $filterColumns = [
        'stok_tanggal' => 'Tanggal',
        'stok_jenis' => 'Jenis',
        'stok_keterangan' => 'Keterangan',
    ];

    public static $sortColumns = [
        'stok_tanggal',
        'stok_jenis',
        'stok_qty',
    ];

    public static function field_name(): string
    {
        return 'stok_jenis';
    }

    public function rules(): array
    {
        return [
            'stok_produk_id' => 'required|integer',
            'stok_tanggal' => 'required|date',
            'stok_jenis' => 'required|string|max:20',
            'stok_qty' => 'required|integer',
            'stok_harga' => 'nullable|integer|min:0',
            'stok_keterangan' => 'nullable|string|max:255',
            'stok_jurnal_id' => 'nullable|integer',
        ];
    }

    public function hasProduk()
    {
        return $this->belongsTo(Produk::class, 'stok_produk_id', 'produk_id');
    }
}
