<?php

namespace App\Models;

class Payroll extends BaseModel
{
    protected $table = 'payroll';

    protected $primaryKey = 'payroll_id';

    protected $fillable = [
        'payroll_karyawan_id',
        'payroll_bulan',
        'payroll_tahun',
        'payroll_gaji_pokok',
        'payroll_tunjangan',
        'payroll_bonus',
        'payroll_potongan',
        'payroll_gaji_bersih',
        'payroll_jurnal_id',
        'payroll_catatan',
    ];

    public static $filterColumns = [
        'payroll_bulan' => 'Bulan',
        'payroll_tahun' => 'Tahun',
    ];

    public static $sortColumns = [
        'payroll_tahun',
        'payroll_bulan',
        'payroll_gaji_bersih',
    ];

    protected function casts(): array
    {
        return [
            'payroll_bulan' => 'integer',
            'payroll_tahun' => 'integer',
        ];
    }

    public static function field_name(): string
    {
        return 'payroll_id';
    }

    public function rules(): array
    {
        return [
            'payroll_karyawan_id' => 'required|integer',
            'payroll_bulan' => 'required|integer|min:1|max:12',
            'payroll_tahun' => 'required|integer',
            'payroll_gaji_pokok' => 'nullable|numeric',
            'payroll_tunjangan' => 'nullable|numeric',
            'payroll_bonus' => 'nullable|numeric',
            'payroll_potongan' => 'nullable|numeric',
            'payroll_gaji_bersih' => 'nullable|numeric',
            'payroll_catatan' => 'nullable|string|max:255',
        ];
    }

    public function hasKaryawan()
    {
        return $this->belongsTo(Karyawan::class, 'payroll_karyawan_id', 'karyawan_id');
    }
}
