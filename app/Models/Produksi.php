<?php

namespace App\Models;

class Produksi extends BaseModel
{
    protected $table = 'produksi';

    protected $primaryKey = 'produksi_id';

    protected $fillable = [
        'produksi_tanggal',
        'produksi_produk_jadi_id',
        'produksi_qty_hasil',
        'produksi_biaya_tambahan',
        'produksi_total_hpp',
        'produksi_hpp_per_unit',
        'produksi_keterangan',
        'produksi_jurnal_id',
    ];

    protected function casts(): array
    {
        return [
            'produksi_tanggal' => 'date',
            'produksi_qty_hasil' => 'integer',
            'produksi_biaya_tambahan' => 'integer',
            'produksi_total_hpp' => 'integer',
            'produksi_hpp_per_unit' => 'integer',
        ];
    }

    public static $filterColumns = [
        'produksi_tanggal' => 'Tanggal',
        'produksi_keterangan' => 'Keterangan',
    ];

    public static $sortColumns = [
        'produksi_tanggal',
        'produksi_qty_hasil',
        'produksi_total_hpp',
    ];

    public static function field_name(): string
    {
        return 'produksi_keterangan';
    }

    public function rules(): array
    {
        return [
            'produksi_tanggal' => 'required|date',
            'produksi_produk_jadi_id' => 'required|integer',
            'produksi_qty_hasil' => 'required|integer|min:1',
            'produksi_biaya_tambahan' => 'nullable|integer|min:0',
            'produksi_total_hpp' => 'nullable|integer|min:0',
            'produksi_hpp_per_unit' => 'nullable|integer|min:0',
            'produksi_keterangan' => 'nullable|string|max:255',
            'produksi_jurnal_id' => 'nullable|integer',
        ];
    }

    public function hasProdukJadi()
    {
        return $this->belongsTo(Produk::class, 'produksi_produk_jadi_id', 'produk_id');
    }

    public function hasBahan()
    {
        return $this->hasMany(ProduksiBahan::class, 'bahan_produksi_id', 'produksi_id');
    }
}
