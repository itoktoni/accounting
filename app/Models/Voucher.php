<?php

namespace App\Models;

class Voucher extends BaseModel
{
    protected $table = 'voucher';

    protected $primaryKey = 'voucher_id';

    protected $fillable = [
        'voucher_kode',
        'voucher_tipe',
        'voucher_nilai',
        'voucher_min_belanja',
        'voucher_kuota',
        'voucher_terpakai',
        'voucher_berlaku_dari',
        'voucher_berlaku_sampai',
        'voucher_aktif',
        'voucher_catatan',
    ];

    public static $filterColumns = [
        'voucher_kode' => 'Kode',
        'voucher_tipe' => 'Tipe',
    ];

    public static $sortColumns = [
        'voucher_kode',
        'voucher_tipe',
        'voucher_nilai',
    ];

    protected function casts(): array
    {
        return [
            'voucher_berlaku_dari' => 'date',
            'voucher_berlaku_sampai' => 'date',
            'voucher_aktif' => 'boolean',
        ];
    }

    public static function field_name(): string
    {
        return 'voucher_kode';
    }

    public function rules(): array
    {
        return [
            'voucher_kode' => 'required|string|max:255',
            'voucher_tipe' => 'nullable|string|max:20',
            'voucher_nilai' => 'nullable|numeric',
            'voucher_min_belanja' => 'nullable|numeric',
            'voucher_kuota' => 'nullable|integer',
            'voucher_terpakai' => 'nullable|integer',
            'voucher_berlaku_dari' => 'nullable|date',
            'voucher_berlaku_sampai' => 'nullable|date',
            'voucher_aktif' => 'nullable|boolean',
            'voucher_catatan' => 'nullable|string|max:255',
        ];
    }
}
