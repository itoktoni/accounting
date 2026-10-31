<?php

namespace App\Models;

class Karyawan extends BaseModel
{
    protected $table = 'karyawan';

    protected $primaryKey = 'karyawan_id';

    protected $fillable = [
        'karyawan_no',
        'karyawan_nama',
        'karyawan_jabatan',
        'karyawan_status_kerja',
        'karyawan_gaji_pokok',
        'karyawan_tunjangan',
        'karyawan_aktif',
    ];

    public static $filterColumns = [
        'karyawan_nama' => 'Nama',
        'karyawan_jabatan' => 'Jabatan',
        'karyawan_status_kerja' => 'Status',
    ];

    public static $sortColumns = [
        'karyawan_nama',
        'karyawan_jabatan',
        'karyawan_status_kerja',
    ];

    protected function casts(): array
    {
        return [
            'karyawan_aktif' => 'boolean',
        ];
    }

    public static function field_name(): string
    {
        return 'karyawan_nama';
    }

    public function rules(): array
    {
        return [
            'karyawan_no' => 'nullable|string|max:255',
            'karyawan_nama' => 'required|string|max:255',
            'karyawan_jabatan' => 'nullable|string|max:255',
            'karyawan_status_kerja' => 'nullable|string|max:20',
            'karyawan_gaji_pokok' => 'nullable|numeric',
            'karyawan_tunjangan' => 'nullable|numeric',
            'karyawan_aktif' => 'nullable|boolean',
        ];
    }

    public function hasPayrolls()
    {
        return $this->hasMany(Payroll::class, 'payroll_karyawan_id', 'karyawan_id');
    }
}
