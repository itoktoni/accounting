<?php

namespace App\Models;

class Invoice extends BaseModel
{
    protected $table = 'invoice';

    protected $primaryKey = 'invoice_id';

    protected $fillable = [
        'invoice_nomor',
        'invoice_tanggal',
        'invoice_jatuh_tempo',
        'invoice_top_hari',
        'invoice_pelanggan',
        'invoice_alamat',
        'invoice_telepon',
        'invoice_diskon',
        'invoice_ongkir',
        'invoice_biaya_lain',
        'invoice_pajak_persen',
        'invoice_pajak_nominal',
        'invoice_catatan',
        'invoice_status',
        'invoice_jurnal_id',
    ];

    public static $filterColumns = [
        'invoice_nomor' => 'Nomor',
        'invoice_pelanggan' => 'Pelanggan',
        'invoice_status' => 'Status',
    ];

    public static $sortColumns = [
        'invoice_nomor',
        'invoice_tanggal',
        'invoice_pelanggan',
        'invoice_status',
    ];

    protected function casts(): array
    {
        return [
            'invoice_tanggal' => 'date',
            'invoice_jatuh_tempo' => 'date',
            'invoice_top_hari' => 'integer',
            'invoice_diskon' => 'decimal:2',
            'invoice_ongkir' => 'decimal:2',
            'invoice_biaya_lain' => 'decimal:2',
            'invoice_pajak_persen' => 'decimal:2',
            'invoice_pajak_nominal' => 'decimal:2',
        ];
    }

    public static function field_name(): string
    {
        return 'invoice_nomor';
    }

    public function rules(): array
    {
        return [
            'invoice_nomor' => 'required|string|max:255',
            'invoice_tanggal' => 'required|date',
            'invoice_jatuh_tempo' => 'nullable|date',
            'invoice_top_hari' => 'nullable|integer|min:0',
            'invoice_pelanggan' => 'required|string|max:255',
            'invoice_alamat' => 'nullable|string',
            'invoice_telepon' => 'nullable|string|max:255',
            'invoice_diskon' => 'nullable|numeric|min:0',
            'invoice_ongkir' => 'nullable|numeric|min:0',
            'invoice_biaya_lain' => 'nullable|numeric|min:0',
            'invoice_pajak_persen' => 'nullable|numeric|min:0',
            'invoice_pajak_nominal' => 'nullable|numeric|min:0',
            'invoice_catatan' => 'nullable|string',
            'invoice_status' => 'nullable|string|max:50',
            'invoice_jurnal_id' => 'nullable|integer',
        ];
    }

    public function hasItems()
    {
        return $this->hasMany(InvoiceItem::class, 'item_invoice_id', 'invoice_id');
    }

    // bruto, diskon_item, diskon_global, dpp, pajak, ongkir, biaya_lain, total
    public function hitungTotal(): array
    {
        $bruto = 0;
        $diskonItem = 0;
        foreach ($this->hasItems as $it) {
            $bruto += (float) $it->item_qty * (float) $it->item_harga_satuan;
            $diskonItem += (float) $it->item_diskon;
        }
        $diskonGlobal = (float) $this->invoice_diskon;
        $dpp = $bruto - $diskonItem - $diskonGlobal;
        $pajak = $dpp * (float) $this->invoice_pajak_persen / 100;
        $ongkir = (float) $this->invoice_ongkir;
        $lain = (float) $this->invoice_biaya_lain;

        return [
            'bruto' => round($bruto, 2),
            'diskon_item' => round($diskonItem, 2),
            'diskon_global' => round($diskonGlobal, 2),
            'dpp' => round($dpp, 2),
            'pajak' => round($pajak, 2),
            'ongkir' => round($ongkir, 2),
            'biaya_lain' => round($lain, 2),
            'total' => round($dpp + $pajak + $ongkir + $lain, 2),
        ];
    }
}
