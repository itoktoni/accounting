<?php

namespace App\Models;

class AsetTetap extends BaseModel
{
    protected $table = 'aset_tetap';

    protected $primaryKey = 'aset_id';

    protected $fillable = [
        'aset_nama',
        'aset_kategori',
        'aset_harga_beli',
        'aset_tanggal_beli',
        'aset_masa_pakai',
        'aset_penyusutan_bulan',
        'aset_akumulasi',
        'aset_aktif',
        'aset_jurnal_id',
    ];

    public static $filterColumns = [
        'aset_nama' => 'Nama',
        'aset_kategori' => 'Kategori',
    ];

    public static $sortColumns = [
        'aset_nama',
        'aset_kategori',
        'aset_tanggal_beli',
    ];

    protected function casts(): array
    {
        return [
            'aset_tanggal_beli' => 'date',
            'aset_masa_pakai' => 'integer',
            'aset_aktif' => 'boolean',
        ];
    }

    public static function field_name(): string
    {
        return 'aset_nama';
    }

    public function rules(): array
    {
        return [
            'aset_nama' => 'required|string|max:255',
            'aset_kategori' => 'nullable|string|max:255',
            'aset_harga_beli' => 'required|numeric|min:0',
            'aset_tanggal_beli' => 'nullable|date',
            'aset_masa_pakai' => 'required|integer|min:1',
            'aset_penyusutan_bulan' => 'nullable|numeric',
            'aset_akumulasi' => 'nullable|numeric',
            'aset_aktif' => 'nullable|boolean',
        ];
    }
}
