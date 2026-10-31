<?php

namespace App\Models;

class Akun extends BaseModel
{
    protected $table = 'akun';

    protected $primaryKey = 'akun_id';

    protected $fillable = [
        'akun_kode',
        'akun_nama',
        'akun_tipe',
        'akun_subtipe',
        'akun_saldo_normal',
        'akun_peran',
        'akun_is_rekening',
        'akun_no_rekening',
    ];

    public static $filterColumns = [
        'akun_kode' => 'Kode',
        'akun_nama' => 'Nama',
        'akun_tipe' => 'Tipe',
        'akun_subtipe' => 'Subtipe',
    ];

    public static $sortColumns = [
        'akun_kode',
        'akun_nama',
        'akun_tipe',
        'akun_subtipe',
        'akun_saldo_normal',
    ];

    protected function casts(): array
    {
        return [
            'akun_is_rekening' => 'boolean',
        ];
    }

    public static function field_name(): string
    {
        return 'akun_nama';
    }

    public function rules(): array
    {
        return [
            'akun_kode' => 'required|string|max:20',
            'akun_nama' => 'required|string|max:255',
            'akun_tipe' => 'required|string|max:20',
            'akun_subtipe' => 'nullable|string|max:100',
            'akun_saldo_normal' => 'required|string|in:DEBIT,KREDIT',
            'akun_peran' => 'nullable|string|max:50',
            'akun_is_rekening' => 'nullable|boolean',
            'akun_no_rekening' => 'nullable|string|max:100',
        ];
    }

    public function hasDetails()
    {
        return $this->hasMany(DetailJurnal::class, 'detail_akun_id', 'akun_id');
    }
}
