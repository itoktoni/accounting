<?php

namespace App\Models;

class Diskon extends BaseModel
{
    protected $table = 'diskon';

    protected $primaryKey = 'diskon_id';

    protected $fillable = [
        'diskon_kode',
        'diskon_nama',
        'diskon_persen',
        'diskon_aktif',
    ];

    public static $filterColumns = [
        'diskon_kode' => 'Kode',
        'diskon_nama' => 'Nama',
    ];

    public static $sortColumns = [
        'diskon_kode',
        'diskon_nama',
        'diskon_persen',
    ];

    protected function casts(): array
    {
        return [
            'diskon_persen' => 'decimal:2',
            'diskon_aktif' => 'boolean',
        ];
    }

    public static function field_name(): string
    {
        return 'diskon_nama';
    }

    public function rules(): array
    {
        return [
            'diskon_kode' => 'required|string|max:20',
            'diskon_nama' => 'required|string|max:255',
            'diskon_persen' => 'required|numeric|min:0|max:100',
        ];
    }
}
