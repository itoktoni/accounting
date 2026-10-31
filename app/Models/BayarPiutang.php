<?php

namespace App\Models;

class BayarPiutang extends BaseModel
{
    protected $table = "bayar_piutang";

    protected $primaryKey = "bayar_id";

    protected $fillable = [
        "bayar_piutang_id",
        "bayar_tanggal",
        "bayar_jumlah",
        "bayar_catatan",
        "bayar_jurnal_id",
    ];

    protected function casts(): array
    {
        return [
            "bayar_tanggal" => "date",
            "bayar_jumlah" => "decimal:2",
        ];
    }

    public static $filterColumns = [
        "bayar_catatan" => "Catatan",
    ];

    public static $sortColumns = [
        "bayar_tanggal",
        "bayar_jumlah",
    ];

    public static function field_name(): string
    {
        return "bayar_tanggal";
    }

    public function rules(): array
    {
        return [
            "bayar_piutang_id" => "required|exists:piutang,piutang_id",
            "bayar_tanggal" => "required|date",
            "bayar_jumlah" => "required|numeric|min:1",
            "bayar_catatan" => "nullable|string",
            "bayar_jurnal_id" => "nullable|integer",
        ];
    }

    public function hasPiutang()
    {
        return $this->belongsTo(Piutang::class, "bayar_piutang_id", "piutang_id");
    }
}
