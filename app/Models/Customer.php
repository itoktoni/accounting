<?php

namespace App\Models;

class Customer extends BaseModel
{
    protected $table = "customer";

    protected $primaryKey = "customer_id";

    protected $fillable = [
        "customer_nama",
        "customer_telepon",
        "customer_alamat",
        "customer_catatan",
    ];

    protected function casts(): array
    {
        return [];
    }

    public static $filterColumns = [
        "customer_nama" => "Nama",
        "customer_telepon" => "Telepon",
    ];

    public static $sortColumns = [
        "customer_nama",
        "customer_telepon",
        "customer_alamat",
    ];

    public static function field_name(): string
    {
        return "customer_nama";
    }

    public function rules(): array
    {
        return [
            "customer_nama" => "required|string|max:255",
            "customer_telepon" => "nullable|string|max:50",
            "customer_alamat" => "nullable|string",
            "customer_catatan" => "nullable|string",
        ];
    }

    public function hasPiutang()
    {
        return $this->hasMany(Piutang::class, "piutang_pelanggan", "customer_nama");
    }
}
