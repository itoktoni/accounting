<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $tahun = (int) date('Y');
        $bulan = (int) date('m');

        $kas = 0;
        if (Schema::hasTable('detail_jurnal') && Schema::hasTable('akun')) {
            $kas = (float) DB::table('detail_jurnal as dj')
                ->join('akun as a', 'a.akun_id', '=', 'dj.detail_akun_id')
                ->where(fn ($q) => $q->where('a.akun_is_rekening', true)->orWhereIn('a.akun_kode', ['1100', '1110']))
                ->selectRaw('SUM(dj.detail_debit) - SUM(dj.detail_kredit) as s')->value('s');
        }

        $omzetBulan = 0;
        if (Schema::hasTable('invoice_item') && Schema::hasTable('invoice')) {
            $omzetBulan = (float) DB::table('invoice_item as ii')
                ->join('invoice as i', 'i.invoice_id', '=', 'ii.item_invoice_id')
                ->whereYear('i.invoice_tanggal', $tahun)->whereMonth('i.invoice_tanggal', $bulan)
                ->sum('ii.item_subtotal');
        }

        $piutang = 0;
        if (Schema::hasTable('piutang')) {
            $piutang = (float) DB::table('piutang')->selectRaw('SUM(piutang_jumlah - piutang_terbayar) as s')->value('s');
        }

        $hutang = 0;
        if (Schema::hasTable('hutang')) {
            $hutang = (float) DB::table('hutang')->selectRaw('SUM(hutang_jumlah - hutang_terbayar) as s')->value('s');
        }

        // Omzet 6 bulan terakhir untuk grafik
        $grafik = [];
        if (Schema::hasTable('invoice_item') && Schema::hasTable('invoice')) {
            for ($i = 5; $i >= 0; $i--) {
                $t = strtotime("-{$i} months");
                $y = (int) date('Y', $t);
                $m = (int) date('m', $t);
                $grafik[] = [
                    'label' => date('M', $t),
                    'total' => (float) DB::table('invoice_item as ii')
                        ->join('invoice as i', 'i.invoice_id', '=', 'ii.item_invoice_id')
                        ->whereYear('i.invoice_tanggal', $y)->whereMonth('i.invoice_tanggal', $m)
                        ->sum('ii.item_subtotal'),
                ];
            }
        }
        $maxGrafik = max(1, max(array_column($grafik, 'total') ?: [1]));

        $invoiceBaru = Schema::hasTable('invoice')
            ? DB::table('invoice')->orderByDesc('invoice_tanggal')->limit(5)->get()
            : collect();

        // Jatuh tempo mendesak: piutang belum lunas, jatuh tempo <= 7 hari
        $tempo = collect();
        if (Schema::hasTable('piutang')) {
            $tempo = DB::table('piutang')
                ->whereRaw('piutang_jumlah - piutang_terbayar > 0')
                ->orderBy('piutang_jatuh_tempo')->limit(5)->get()
                ->map(function ($r) {
                    $jt = $r->piutang_jatuh_tempo ?: $r->piutang_tanggal;
                    $r->hari = (int) floor((strtotime($jt) - strtotime(date('Y-m-d'))) / 86400);
                    $r->sisa = (float) $r->piutang_jumlah - (float) $r->piutang_terbayar;

                    return $r;
                });
        }

        return view('dashboard', compact('kas', 'omzetBulan', 'piutang', 'hutang', 'grafik', 'maxGrafik', 'invoiceBaru', 'tempo', 'bulan', 'tahun'));
    }
}
