<?php

namespace App\Models;

class Hutang extends BaseModel
{
    protected $table = "hutang";

    protected $primaryKey = "hutang_id";

    protected $fillable = [
        "hutang_tanggal",
        "hutang_jatuh_tempo",
        "hutang_pemasok",
        "hutang_keterangan",
        "hutang_jumlah",
        "hutang_terbayar",
        "hutang_status",
        "hutang_jurnal_id",
        "hutang_akun_kode",
    ];

    protected function casts(): array
    {
        return [
            "hutang_tanggal" => "date",
            "hutang_jatuh_tempo" => "date",
            "hutang_jumlah" => "decimal:2",
            "hutang_terbayar" => "decimal:2",
        ];
    }

    public static $filterColumns = [
        "hutang_pemasok" => "Pemasok",
        "hutang_status" => "Status",
        "hutang_keterangan" => "Keterangan",
    ];

    public static $sortColumns = [
        "hutang_tanggal",
        "hutang_pemasok",
        "hutang_jumlah",
        "hutang_status",
    ];

    public static function field_name(): string
    {
        return "hutang_pemasok";
    }

    public function rules(): array
    {
        return [
            "hutang_tanggal" => "required|date",
            "hutang_jatuh_tempo" => "nullable|date",
            "hutang_pemasok" => "required|string|max:255",
            "hutang_keterangan" => "nullable|string",
            "hutang_jumlah" => "required|numeric|min:0",
            "hutang_status" => "nullable|string|max:50",
            "hutang_jurnal_id" => "nullable|integer",
            "hutang_akun_kode" => "nullable|string|max:50",
        ];
    }

    public function hasBayar()
    {
        return $this->hasMany(BayarHutang::class, "bh_hutang_id", "hutang_id");
    }

    public function getSisaAttribute(): float
    {
        return (float) $this->hutang_jumlah - (float) $this->hutang_terbayar;
    }
}
