<?php

namespace App\Models;

class Pajak extends BaseModel
{
    protected $table = 'pajak';

    protected $primaryKey = 'pajak_id';

    protected $fillable = [
        'pajak_kode',
        'pajak_nama',
        'pajak_persen',
        'pajak_aktif',
    ];

    public static $filterColumns = [
        'pajak_kode' => 'Kode',
        'pajak_nama' => 'Nama',
    ];

    public static $sortColumns = [
        'pajak_kode',
        'pajak_nama',
        'pajak_persen',
    ];

    protected function casts(): array
    {
        return [
            'pajak_persen' => 'decimal:2',
            'pajak_aktif' => 'boolean',
        ];
    }

    public static function field_name(): string
    {
        return 'pajak_nama';
    }

    public function rules(): array
    {
        return [
            'pajak_kode' => 'required|string|max:20',
            'pajak_nama' => 'required|string|max:255',
            'pajak_persen' => 'required|numeric|min:0|max:100',
        ];
    }
}
