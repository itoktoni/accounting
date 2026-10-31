<?php

namespace App\Models;

class Vendor extends BaseModel
{
    protected $table = "vendor";

    protected $primaryKey = "vendor_id";

    protected $fillable = [
        "vendor_nama",
        "vendor_telepon",
        "vendor_alamat",
        "vendor_catatan",
    ];

    protected function casts(): array
    {
        return [];
    }

    public static $filterColumns = [
        "vendor_nama" => "Nama",
        "vendor_telepon" => "Telepon",
    ];

    public static $sortColumns = [
        "vendor_nama",
        "vendor_telepon",
        "vendor_alamat",
    ];

    public static function field_name(): string
    {
        return "vendor_nama";
    }

    public function rules(): array
    {
        return [
            "vendor_nama" => "required|string|max:255",
            "vendor_telepon" => "nullable|string|max:50",
            "vendor_alamat" => "nullable|string",
            "vendor_catatan" => "nullable|string",
        ];
    }

    public function hasHutang()
    {
        return $this->hasMany(Hutang::class, "hutang_pemasok", "vendor_nama");
    }
}
