<?php

namespace App\Models;

class Anggaran extends BaseModel
{
    protected $table = 'anggaran';

    protected $primaryKey = 'anggaran_id';

    protected $fillable = [
        'anggaran_bulan',
        'anggaran_tahun',
        'anggaran_akun_kode',
        'anggaran_nominal',
        'anggaran_catatan',
    ];

    public static $filterColumns = [
        'anggaran_bulan' => 'Bulan',
        'anggaran_tahun' => 'Tahun',
        'anggaran_akun_kode' => 'Kode Akun',
    ];

    public static $sortColumns = [
        'anggaran_bulan',
        'anggaran_tahun',
        'anggaran_akun_kode',
        'anggaran_nominal',
    ];

    protected function casts(): array
    {
        return [
            'anggaran_bulan' => 'integer',
            'anggaran_tahun' => 'integer',
            'anggaran_nominal' => 'decimal:2',
        ];
    }

    public static function field_name(): string
    {
        return 'anggaran_akun_kode';
    }

    public function rules(): array
    {
        return [
            'anggaran_bulan' => 'required|integer|min:1|max:12',
            'anggaran_tahun' => 'required|integer|min:2000|max:2100',
            'anggaran_akun_kode' => 'required|string|max:255',
            'anggaran_nominal' => 'nullable|numeric|min:0',
            'anggaran_catatan' => 'nullable|string',
        ];
    }
}
