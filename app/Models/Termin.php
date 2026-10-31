<?php

namespace App\Models;

class Termin extends BaseModel
{
    protected $table = 'termin';

    protected $primaryKey = 'termin_id';

    protected $fillable = [
        'termin_kode',
        'termin_nama',
        'termin_hari',
        'termin_keterangan',
        'termin_aktif',
    ];

    public static $filterColumns = [
        'termin_kode' => 'Kode',
        'termin_nama' => 'Nama',
    ];

    public static $sortColumns = [
        'termin_kode',
        'termin_nama',
        'termin_hari',
    ];

    protected function casts(): array
    {
        return [
            'termin_hari' => 'integer',
            'termin_aktif' => 'boolean',
        ];
    }

    public static function field_name(): string
    {
        return 'termin_nama';
    }

    public function rules(): array
    {
        return [
            'termin_kode' => 'required|string|max:20',
            'termin_nama' => 'required|string|max:255',
            'termin_hari' => 'required|integer|min:0|max:365',
            'termin_keterangan' => 'nullable|string',
        ];
    }
}
