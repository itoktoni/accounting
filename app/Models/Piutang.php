<?php

namespace App\Models;

class Piutang extends BaseModel
{
    protected $table = "piutang";

    protected $primaryKey = "piutang_id";

    protected $fillable = [
        "piutang_tanggal",
        "piutang_jatuh_tempo",
        "piutang_pelanggan",
        "piutang_keterangan",
        "piutang_jumlah",
        "piutang_terbayar",
        "piutang_status",
        "piutang_jurnal_id",
        "piutang_invoice_id",
    ];

    protected function casts(): array
    {
        return [
            "piutang_tanggal" => "date",
            "piutang_jatuh_tempo" => "date",
            "piutang_jumlah" => "decimal:2",
            "piutang_terbayar" => "decimal:2",
        ];
    }

    public static $filterColumns = [
        "piutang_pelanggan" => "Pelanggan",
        "piutang_status" => "Status",
        "piutang_keterangan" => "Keterangan",
    ];

    public static $sortColumns = [
        "piutang_tanggal",
        "piutang_pelanggan",
        "piutang_jumlah",
        "piutang_status",
    ];

    public static function field_name(): string
    {
        return "piutang_pelanggan";
    }

    public function rules(): array
    {
        return [
            "piutang_tanggal" => "required|date",
            "piutang_jatuh_tempo" => "nullable|date",
            "piutang_pelanggan" => "required|string|max:255",
            "piutang_keterangan" => "nullable|string",
            "piutang_jumlah" => "required|numeric|min:0",
            "piutang_status" => "nullable|string|max:50",
            "piutang_jurnal_id" => "nullable|integer",
            "piutang_invoice_id" => "nullable|integer",
        ];
    }

    public function hasBayar()
    {
        return $this->hasMany(BayarPiutang::class, "bayar_piutang_id", "piutang_id");
    }

    public function getSisaAttribute(): float
    {
        return (float) $this->piutang_jumlah - (float) $this->piutang_terbayar;
    }
}
