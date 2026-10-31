<?php

namespace App\Models;

class PoItem extends BaseModel
{
    protected $table = 'po_item';

    protected $primaryKey = 'poi_id';

    protected $fillable = [
        'poi_po_id',
        'poi_produk_id',
        'poi_deskripsi',
        'poi_qty',
        'poi_satuan',
        'poi_harga_satuan',
        'poi_diskon',
        'poi_subtotal',
    ];

    public static $filterColumns = [
        'poi_deskripsi' => 'Deskripsi',
    ];

    public static $sortColumns = [
        'poi_deskripsi',
        'poi_qty',
        'poi_harga_satuan',
        'poi_subtotal',
    ];

    protected function casts(): array
    {
        return [
            'poi_qty' => 'decimal:2',
            'poi_harga_satuan' => 'decimal:2',
            'poi_diskon' => 'decimal:2',
            'poi_subtotal' => 'decimal:2',
        ];
    }

    public static function field_name(): string
    {
        return 'poi_deskripsi';
    }

    public function rules(): array
    {
        return [
            'poi_po_id' => 'required|integer',
            'poi_produk_id' => 'nullable|integer',
            'poi_deskripsi' => 'required|string|max:255',
            'poi_qty' => 'nullable|numeric|min:0',
            'poi_satuan' => 'nullable|string|max:50',
            'poi_harga_satuan' => 'nullable|numeric|min:0',
            'poi_diskon' => 'nullable|numeric|min:0',
            'poi_subtotal' => 'nullable|numeric|min:0',
        ];
    }

    public function hasPurchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'poi_po_id', 'po_id');
    }
}
