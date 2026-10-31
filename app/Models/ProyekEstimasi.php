<?php

namespace App\Models;

class ProyekEstimasi extends BaseModel
{
    protected $table = 'proyek_estimasi';

    protected $primaryKey = 'estimasi_id';

    protected $fillable = [
        'estimasi_proyek_id',
        'estimasi_deskripsi',
        'estimasi_qty',
        'estimasi_satuan',
        'estimasi_harga',
        'estimasi_subtotal',
    ];

    public static $filterColumns = [
        'estimasi_deskripsi' => 'Deskripsi',
    ];

    public static $sortColumns = [
        'estimasi_deskripsi',
        'estimasi_qty',
        'estimasi_subtotal',
    ];

    protected function casts(): array
    {
        return [
            'estimasi_qty' => 'decimal:2',
            'estimasi_harga' => 'decimal:2',
            'estimasi_subtotal' => 'decimal:2',
        ];
    }

    public static function field_name(): string
    {
        return 'estimasi_deskripsi';
    }

    public function rules(): array
    {
        return [
            'estimasi_deskripsi' => 'required|string|max:255',
            'estimasi_qty' => 'required|numeric|min:0',
            'estimasi_satuan' => 'nullable|string|max:20',
            'estimasi_harga' => 'required|numeric|min:0',
        ];
    }

    public function hasProyek()
    {
        return $this->belongsTo(Proyek::class, 'estimasi_proyek_id', 'proyek_id');
    }
}
