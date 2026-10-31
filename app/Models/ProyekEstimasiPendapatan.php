<?php

namespace App\Models;

class ProyekEstimasiPendapatan extends BaseModel
{
    protected $table = 'proyek_estimasi_pendapatan';

    protected $primaryKey = 'estimasi_masuk_id';

    protected $fillable = [
        'estimasi_masuk_proyek_id',
        'estimasi_masuk_deskripsi',
        'estimasi_masuk_qty',
        'estimasi_masuk_satuan',
        'estimasi_masuk_harga',
        'estimasi_masuk_subtotal',
    ];

    public static $filterColumns = [
        'estimasi_masuk_deskripsi' => 'Deskripsi',
    ];

    public static $sortColumns = [
        'estimasi_masuk_deskripsi',
        'estimasi_masuk_qty',
        'estimasi_masuk_subtotal',
    ];

    protected function casts(): array
    {
        return [
            'estimasi_masuk_qty' => 'decimal:2',
            'estimasi_masuk_harga' => 'decimal:2',
            'estimasi_masuk_subtotal' => 'decimal:2',
        ];
    }

    public static function field_name(): string
    {
        return 'estimasi_masuk_deskripsi';
    }

    public function rules(): array
    {
        return [
            'estimasi_masuk_deskripsi' => 'required|string|max:255',
            'estimasi_masuk_qty' => 'required|numeric|min:0',
            'estimasi_masuk_satuan' => 'nullable|string|max:20',
            'estimasi_masuk_harga' => 'required|numeric|min:0',
        ];
    }

    public function hasProyek()
    {
        return $this->belongsTo(Proyek::class, 'estimasi_masuk_proyek_id', 'proyek_id');
    }
}
