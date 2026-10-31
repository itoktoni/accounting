<?php

namespace App\Models;

class Proyek extends BaseModel
{
    protected $table = 'proyek';

    protected $primaryKey = 'proyek_id';

    protected $fillable = [
        'proyek_kode',
        'proyek_nama',
        'proyek_pelanggan',
        'proyek_nilai_kontrak',
        'proyek_anggaran_biaya',
        'proyek_tanggal_mulai',
        'proyek_target_selesai',
        'proyek_status',
        'proyek_catatan',
    ];

    public static $filterColumns = [
        'proyek_kode' => 'Kode',
        'proyek_nama' => 'Nama',
        'proyek_pelanggan' => 'Pelanggan',
        'proyek_status' => 'Status',
    ];

    public static $sortColumns = [
        'proyek_kode',
        'proyek_nama',
        'proyek_pelanggan',
        'proyek_status',
    ];

    protected function casts(): array
    {
        return [
            'proyek_nilai_kontrak' => 'decimal:2',
            'proyek_anggaran_biaya' => 'decimal:2',
            'proyek_tanggal_mulai' => 'date',
            'proyek_target_selesai' => 'date',
        ];
    }

    public static function field_name(): string
    {
        return 'proyek_nama';
    }

    public function rules(): array
    {
        return [
            'proyek_kode' => 'required|string|max:255',
            'proyek_nama' => 'required|string|max:255',
            'proyek_pelanggan' => 'nullable|string|max:255',
            'proyek_nilai_kontrak' => 'nullable|numeric|min:0',
            'proyek_anggaran_biaya' => 'nullable|numeric|min:0',
            'proyek_tanggal_mulai' => 'nullable|date',
            'proyek_target_selesai' => 'nullable|date',
            'proyek_status' => 'nullable|string|max:50',
            'proyek_catatan' => 'nullable|string',
        ];
    }

    public function hasEstimasi()
    {
        return $this->hasMany(ProyekEstimasi::class, 'estimasi_proyek_id', 'proyek_id');
    }

    public function hasEstimasiMasuk()
    {
        return $this->hasMany(ProyekEstimasiPendapatan::class, 'estimasi_masuk_proyek_id', 'proyek_id');
    }
}
