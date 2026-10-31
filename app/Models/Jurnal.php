<?php

namespace App\Models;

class Jurnal extends BaseModel
{
    protected $table = 'jurnal';

    protected $primaryKey = 'jurnal_id';

    protected $fillable = [
        'jurnal_tanggal',
        'jurnal_keterangan',
        'jurnal_referensi',
        'jurnal_kategori',
        'jurnal_tipe_tx',
        'jurnal_nomor_tx',
        'jurnal_pihak',
        'jurnal_proyek_id',
    ];

    public static $filterColumns = [
        'jurnal_tanggal' => 'Tanggal',
        'jurnal_keterangan' => 'Keterangan',
        'jurnal_nomor_tx' => 'Nomor',
        'jurnal_kategori' => 'Kategori',
    ];

    public static $sortColumns = [
        'jurnal_tanggal',
        'jurnal_nomor_tx',
        'jurnal_keterangan',
        'jurnal_kategori',
        'jurnal_tipe_tx',
    ];

    protected function casts(): array
    {
        return [
            'jurnal_tanggal' => 'date',
            'jurnal_proyek_id' => 'integer',
        ];
    }

    public static function field_name(): string
    {
        return 'jurnal_keterangan';
    }

    public function rules(): array
    {
        return [
            'jurnal_tanggal' => 'required|date',
            'jurnal_keterangan' => 'required|string',
            'jurnal_referensi' => 'nullable|string|max:255',
            'jurnal_kategori' => 'nullable|string|max:100',
            'jurnal_tipe_tx' => 'nullable|string|max:50',
            'jurnal_nomor_tx' => 'nullable|string|max:100',
            'jurnal_pihak' => 'nullable|string|max:255',
            'jurnal_proyek_id' => 'nullable|integer',
        ];
    }

    public function hasDetails()
    {
        return $this->hasMany(DetailJurnal::class, 'detail_jurnal_id', 'jurnal_id');
    }
}
