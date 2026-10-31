<?php

namespace App\Models;

class Penawaran extends BaseModel
{
    protected $table = 'penawaran';

    protected $primaryKey = 'penawaran_id';

    protected $fillable = [
        'penawaran_nomor',
        'penawaran_tanggal',
        'penawaran_berlaku_sampai',
        'penawaran_pelanggan',
        'penawaran_alamat',
        'penawaran_telepon',
        'penawaran_termin_kode',
        'penawaran_pajak_mode',
        'penawaran_pajak_persen',
        'penawaran_diskon_global',
        'penawaran_ongkir',
        'penawaran_biaya_lain',
        'penawaran_catatan',
        'penawaran_status',
        'penawaran_invoice_id',
        'penawaran_revisi',
        'penawaran_tampil_bank',
    ];

    public static $filterColumns = [
        'penawaran_nomor' => 'Nomor',
        'penawaran_pelanggan' => 'Pelanggan',
        'penawaran_status' => 'Status',
    ];

    public static $sortColumns = [
        'penawaran_nomor',
        'penawaran_tanggal',
        'penawaran_pelanggan',
        'penawaran_status',
    ];

    protected function casts(): array
    {
        return [
            'penawaran_tanggal' => 'date',
            'penawaran_berlaku_sampai' => 'date',
            'penawaran_pajak_persen' => 'decimal:2',
            'penawaran_diskon_global' => 'decimal:2',
            'penawaran_ongkir' => 'decimal:2',
            'penawaran_biaya_lain' => 'decimal:2',
            'penawaran_revisi' => 'integer',
            'penawaran_tampil_bank' => 'boolean',
        ];
    }

    public static function field_name(): string
    {
        return 'penawaran_nomor';
    }

    public function rules(): array
    {
        return [
            'penawaran_nomor' => 'required|string|max:100',
            'penawaran_tanggal' => 'required|date',
            'penawaran_berlaku_sampai' => 'nullable|date',
            'penawaran_termin_kode' => 'nullable|string|max:20',
            'penawaran_pelanggan' => 'required|string|max:255',
            'penawaran_pajak_mode' => 'required|in:GLOBAL,PER_ITEM',
            'penawaran_pajak_persen' => 'nullable|numeric|min:0|max:100',
            'penawaran_diskon_global' => 'nullable|numeric|min:0',
            'penawaran_ongkir' => 'nullable|numeric|min:0',
            'penawaran_biaya_lain' => 'nullable|numeric|min:0',
            'penawaran_catatan' => 'nullable|string',
            'penawaran_tampil_bank' => 'nullable|boolean',
        ];
    }

    public function hasItems()
    {
        return $this->hasMany(PenawaranItem::class, 'item_penawaran_id', 'penawaran_id');
    }

    // bruto, diskon_item, diskon_global, dpp, pajak, ongkir, biaya_lain, total
    public function hitungTotal(): array
    {
        $bruto = 0;
        $diskonItem = 0;
        $pajakItem = 0;
        foreach ($this->hasItems as $it) {
            $line = (float) $it->item_qty * (float) $it->item_harga_satuan;
            $d = $line * (float) $it->item_diskon_persen / 100;
            $bruto += $line;
            $diskonItem += $d;
            $pajakItem += ($line - $d) * (float) $it->item_pajak_persen / 100;
        }
        $diskonGlobal = (float) $this->penawaran_diskon_global;
        $dpp = $bruto - $diskonItem - $diskonGlobal;
        $pajak = $this->penawaran_pajak_mode === 'PER_ITEM'
            ? $pajakItem
            : $dpp * (float) $this->penawaran_pajak_persen / 100;
        $ongkir = (float) $this->penawaran_ongkir;
        $lain = (float) $this->penawaran_biaya_lain;

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

    public function getTotalAttribute(): float
    {
        return $this->hitungTotal()['total'];
    }
}
