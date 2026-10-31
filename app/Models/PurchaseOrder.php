<?php

namespace App\Models;

class PurchaseOrder extends BaseModel
{
    protected $table = 'purchase_order';

    protected $primaryKey = 'po_id';

    protected $fillable = [
        'po_nomor',
        'po_tanggal',
        'po_expected_date',
        'po_vendor',
        'po_alamat',
        'po_telepon',
        'po_diskon',
        'po_ongkir',
        'po_biaya_lain',
        'po_catatan',
        'po_status',
        'po_hutang_id',
        'po_jurnal_id',
    ];

    public static $filterColumns = [
        'po_nomor' => 'Nomor',
        'po_vendor' => 'Vendor',
        'po_status' => 'Status',
    ];

    public static $sortColumns = [
        'po_nomor',
        'po_tanggal',
        'po_vendor',
        'po_status',
    ];

    protected function casts(): array
    {
        return [
            'po_tanggal' => 'date',
            'po_expected_date' => 'date',
            'po_diskon' => 'decimal:2',
            'po_ongkir' => 'decimal:2',
            'po_biaya_lain' => 'decimal:2',
        ];
    }

    public static function field_name(): string
    {
        return 'po_nomor';
    }

    public function rules(): array
    {
        return [
            'po_nomor' => 'required|string|max:255',
            'po_tanggal' => 'required|date',
            'po_expected_date' => 'nullable|date',
            'po_vendor' => 'required|string|max:255',
            'po_alamat' => 'nullable|string',
            'po_telepon' => 'nullable|string|max:255',
            'po_diskon' => 'nullable|numeric|min:0',
            'po_ongkir' => 'nullable|numeric|min:0',
            'po_biaya_lain' => 'nullable|numeric|min:0',
            'po_catatan' => 'nullable|string',
            'po_status' => 'nullable|string|max:50',
            'po_hutang_id' => 'nullable|integer',
            'po_jurnal_id' => 'nullable|integer',
        ];
    }

    public function hasItems()
    {
        return $this->hasMany(PoItem::class, 'poi_po_id', 'po_id');
    }
}
