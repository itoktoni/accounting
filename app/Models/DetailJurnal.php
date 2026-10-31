<?php

namespace App\Models;

class DetailJurnal extends BaseModel
{
    protected $table = 'detail_jurnal';

    protected $primaryKey = 'detail_id';

    protected $fillable = [
        'detail_jurnal_id',
        'detail_akun_id',
        'detail_debit',
        'detail_kredit',
    ];

    public static $filterColumns = [
        'detail_jurnal_id' => 'Jurnal',
        'detail_akun_id' => 'Akun',
    ];

    public static $sortColumns = [
        'detail_jurnal_id',
        'detail_akun_id',
        'detail_debit',
        'detail_kredit',
    ];

    protected function casts(): array
    {
        return [
            'detail_debit' => 'decimal:2',
            'detail_kredit' => 'decimal:2',
        ];
    }

    public static function field_name(): string
    {
        return 'detail_id';
    }

    public function rules(): array
    {
        return [
            'detail_jurnal_id' => 'required|integer',
            'detail_akun_id' => 'required|integer',
            'detail_debit' => 'nullable|numeric|min:0',
            'detail_kredit' => 'nullable|numeric|min:0',
        ];
    }

    public function hasJurnal()
    {
        return $this->belongsTo(Jurnal::class, 'detail_jurnal_id', 'jurnal_id');
    }

    public function hasAkun()
    {
        return $this->belongsTo(Akun::class, 'detail_akun_id', 'akun_id');
    }
}
