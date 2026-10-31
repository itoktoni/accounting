<?php

namespace App\Models;

class BayarHutang extends BaseModel
{
    protected $table = "bayar_hutang";

    protected $primaryKey = "bh_id";

    protected $fillable = [
        "bh_hutang_id",
        "bh_tanggal",
        "bh_jumlah",
        "bh_catatan",
        "bh_jurnal_id",
    ];

    protected function casts(): array
    {
        return [
            "bh_tanggal" => "date",
            "bh_jumlah" => "decimal:2",
        ];
    }

    public static $filterColumns = [
        "bh_catatan" => "Catatan",
    ];

    public static $sortColumns = [
        "bh_tanggal",
        "bh_jumlah",
    ];

    public static function field_name(): string
    {
        return "bh_tanggal";
    }

    public function rules(): array
    {
        return [
            "bh_hutang_id" => "required|exists:hutang,hutang_id",
            "bh_tanggal" => "required|date",
            "bh_jumlah" => "required|numeric|min:1",
            "bh_catatan" => "nullable|string",
            "bh_jurnal_id" => "nullable|integer",
        ];
    }

    public function hasHutang()
    {
        return $this->belongsTo(Hutang::class, "bh_hutang_id", "hutang_id");
    }
}
