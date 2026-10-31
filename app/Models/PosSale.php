<?php

namespace App\Models;

class PosSale extends BaseModel
{
    protected $table = 'pos_sale';

    protected $primaryKey = 'sale_id';

    protected $fillable = [
        'sale_jurnal_id',
        'sale_shift_id',
        'sale_nomor_struk',
        'sale_tanggal',
        'sale_customer',
        'sale_metode_bayar',
        'sale_akun_kas',
        'sale_total',
        'sale_bayar',
        'sale_kembalian',
        'sale_status',
    ];

    public static $filterColumns = [
        'sale_nomor_struk' => 'No Struk',
        'sale_customer' => 'Customer',
        'sale_status' => 'Status',
    ];

    public static $sortColumns = [
        'sale_nomor_struk',
        'sale_tanggal',
        'sale_total',
        'sale_status',
    ];

    protected function casts(): array
    {
        return [
            'sale_tanggal' => 'date',
        ];
    }

    public static function field_name(): string
    {
        return 'sale_nomor_struk';
    }

    public function rules(): array
    {
        return [
            'sale_nomor_struk' => 'required|string|max:255',
            'sale_tanggal' => 'nullable|date',
            'sale_customer' => 'nullable|string|max:255',
            'sale_metode_bayar' => 'nullable|string|max:20',
            'sale_akun_kas' => 'nullable|string|max:20',
            'sale_total' => 'nullable|numeric',
            'sale_bayar' => 'nullable|numeric',
            'sale_kembalian' => 'nullable|numeric',
            'sale_status' => 'nullable|string|max:20',
        ];
    }

    public function hasShift()
    {
        return $this->belongsTo(PosShift::class, 'sale_shift_id', 'shift_id');
    }
}
