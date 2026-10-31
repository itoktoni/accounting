<?php

namespace App\Models;

class PosShift extends BaseModel
{
    protected $table = 'pos_shift';

    protected $primaryKey = 'shift_id';

    protected $fillable = [
        'shift_user_id',
        'shift_username',
        'shift_dibuka',
        'shift_ditutup',
        'shift_saldo_awal',
        'shift_kas_sistem',
        'shift_kas_fisik',
        'shift_selisih',
        'shift_status',
        'shift_catatan',
    ];

    public static $filterColumns = [
        'shift_username' => 'Username',
        'shift_status' => 'Status',
    ];

    public static $sortColumns = [
        'shift_username',
        'shift_dibuka',
        'shift_status',
    ];

    protected function casts(): array
    {
        return [
            'shift_dibuka' => 'datetime',
            'shift_ditutup' => 'datetime',
        ];
    }

    public static function field_name(): string
    {
        return 'shift_username';
    }

    public function rules(): array
    {
        return [
            'shift_username' => 'required|string|max:255',
            'shift_dibuka' => 'nullable|date',
            'shift_ditutup' => 'nullable|date',
            'shift_saldo_awal' => 'nullable|numeric',
            'shift_kas_sistem' => 'nullable|numeric',
            'shift_kas_fisik' => 'nullable|numeric',
            'shift_selisih' => 'nullable|numeric',
            'shift_status' => 'nullable|string|max:20',
            'shift_catatan' => 'nullable|string|max:255',
        ];
    }

    public function hasSales()
    {
        return $this->hasMany(PosSale::class, 'sale_shift_id', 'shift_id');
    }
}
