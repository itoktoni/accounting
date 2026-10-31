<?php

namespace App\Models;

class PenawaranItem extends BaseModel
{
    protected $table = 'penawaran_item';

    protected $primaryKey = 'item_id';

    protected $fillable = [
        'item_penawaran_id',
        'item_deskripsi',
        'item_qty',
        'item_satuan',
        'item_harga_satuan',
        'item_diskon_persen',
        'item_pajak_persen',
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
            'item_diskon_persen' => 'decimal:2',
            'item_pajak_persen' => 'decimal:2',
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
            'item_deskripsi' => 'required|string',
            'item_qty' => 'required|numeric|min:0',
            'item_harga_satuan' => 'required|numeric|min:0',
            'item_diskon_persen' => 'nullable|numeric|min:0|max:100',
            'item_pajak_persen' => 'nullable|numeric|min:0|max:100',
        ];
    }

    public function hasPenawaran()
    {
        return $this->belongsTo(Penawaran::class, 'item_penawaran_id', 'penawaran_id');
    }
}
