<?php

namespace App\Models;

class InvoiceItem extends BaseModel
{
    protected $table = 'invoice_item';

    protected $primaryKey = 'item_id';

    protected $fillable = [
        'item_invoice_id',
        'item_deskripsi',
        'item_qty',
        'item_satuan',
        'item_harga_satuan',
        'item_diskon',
        'item_subtotal',
    ];

    public static $filterColumns = [
        'item_deskripsi' => 'Deskripsi',
    ];

    public static $sortColumns = [
        'item_deskripsi',
        'item_qty',
        'item_harga_satuan',
        'item_subtotal',
    ];

    protected function casts(): array
    {
        return [
            'item_qty' => 'decimal:2',
            'item_harga_satuan' => 'decimal:2',
            'item_diskon' => 'decimal:2',
            'item_subtotal' => 'decimal:2',
        ];
    }

    public static function field_name(): string
    {
        return 'item_deskripsi';
    }

    public function rules(): array
    {
        return [
            'item_invoice_id' => 'required|integer',
            'item_deskripsi' => 'required|string|max:255',
            'item_qty' => 'nullable|numeric|min:0',
            'item_satuan' => 'nullable|string|max:50',
            'item_harga_satuan' => 'nullable|numeric|min:0',
            'item_diskon' => 'nullable|numeric|min:0',
            'item_subtotal' => 'nullable|numeric|min:0',
        ];
    }

    public function hasInvoice()
    {
        return $this->belongsTo(Invoice::class, 'item_invoice_id', 'invoice_id');
    }
}
