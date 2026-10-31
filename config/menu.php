<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Menu Configuration
    |--------------------------------------------------------------------------
    |
    | Define menu items for desktop sidebar, mobile drawer, and bottom nav.
    | Each item: route (string), icon (string), label (string)
    | Sections: label (string), items (array)
    | Items may have 'children' (array of sub-items) rendered as collapsible group.
    | Bottom nav: only 5 items max, uses short label
    |
    */

    'sidebar' => [
        [
            'label' => null,
            'items' => [
                ['route' => 'dashboard', 'icon' => 'home', 'label' => 'Dashboard'],
            ],
        ],
        [
            'label' => 'Master Data',
            'items' => [
                ['route' => 'akun.getTable', 'icon' => 'account_balance', 'label' => 'Akun (COA)', 'match' => ['akun.*']],
                ['route' => 'produk.getTable', 'icon' => 'inventory', 'label' => 'Produk', 'match' => ['produk.*']],
                ['route' => 'customer.getTable', 'icon' => 'people', 'label' => 'Customer & Vendor', 'match' => ['customer.*', 'vendor.*']],
                ['route' => 'karyawan.getTable', 'icon' => 'badge', 'label' => 'Karyawan & Payroll', 'match' => ['karyawan.*', 'payroll.*']],
                ['route' => 'asettetap.getTable', 'icon' => 'domain', 'label' => 'Aset Tetap', 'match' => ['asettetap.*']],
                ['route' => 'proyek.getTable', 'icon' => 'work', 'label' => 'Proyek & Anggaran', 'match' => ['proyek.*', 'anggaran.*']],
                ['route' => 'termin.getTable', 'icon' => 'event_note', 'label' => 'Termin', 'match' => ['termin.*']],
                ['route' => 'diskon.getTable', 'icon' => 'percent', 'label' => 'Diskon', 'match' => ['diskon.*']],
                ['route' => 'pajak.getTable', 'icon' => 'receipt', 'label' => 'Pajak', 'match' => ['pajak.*']],
                ['route' => 'user.getTable', 'icon' => 'manage_accounts', 'label' => 'Users', 'match' => ['user.*']],
            ],
        ],
        [
            'label' => 'Transaksi',
            'items' => [
                ['route' => 'jurnal.getTable', 'icon' => 'receipt_long', 'label' => 'Jurnal', 'match' => ['jurnal.*']],
                ['route' => 'invoice.getTable', 'icon' => 'description', 'label' => 'Invoice', 'match' => ['invoice.*']],
                ['route' => 'penawaran.getTable', 'icon' => 'request_quote', 'label' => 'Penawaran', 'match' => ['penawaran.*']],
                ['route' => 'purchaseorder.getTable', 'icon' => 'shopping_cart', 'label' => 'Purchase Order', 'match' => ['purchaseorder.*']],
                ['route' => 'piutang.getTable', 'icon' => 'call_received', 'label' => 'Piutang', 'match' => ['piutang.*']],
                ['route' => 'hutang.getTable', 'icon' => 'call_made', 'label' => 'Hutang', 'match' => ['hutang.*']],
                ['route' => 'possale.getTable', 'icon' => 'point_of_sale', 'label' => 'POS', 'match' => ['possale.*', 'posshift.*', 'voucher.*']],
                ['route' => 'produksi.getTable', 'icon' => 'precision_manufacturing', 'label' => 'Produksi', 'match' => ['produksi.*', 'produksitemplate.*']],
            ],
        ],
        [
            'label' => 'Laporan Keuangan',
            'items' => [
                ['route' => 'laporan.getNeraca', 'icon' => 'account_balance', 'label' => 'Neraca', 'match' => ['laporan.getNeraca']],
                ['route' => 'laporan.getLabaRugi', 'icon' => 'show_chart', 'label' => 'Laba Rugi', 'match' => ['laporan.getLabaRugi']],
                ['route' => 'laporan.getArusKas', 'icon' => 'waterfall_chart', 'label' => 'Arus Kas', 'match' => ['laporan.getArusKas']],
                ['route' => 'laporan.getProyeksiArusKas', 'icon' => 'trending_up', 'label' => 'Proyeksi Arus Kas', 'match' => ['laporan.getProyeksiArusKas']],
                ['route' => 'laporan.getPerubahanModal', 'icon' => 'pie_chart', 'label' => 'Perubahan Modal', 'match' => ['laporan.getPerubahanModal']],
                ['route' => 'laporan.getRingkasanEksekutif', 'icon' => 'dashboard', 'label' => 'Ringkasan Eksekutif', 'match' => ['laporan.getRingkasanEksekutif']],
                ['route' => 'laporan.getHutangPiutangKontak', 'icon' => 'contacts', 'label' => 'Hutang Piutang per Kontak', 'match' => ['laporan.getHutangPiutangKontak']],
                ['route' => 'laporan.getRingkasanBank', 'icon' => 'account_balance_wallet', 'label' => 'Ringkasan Bank', 'match' => ['laporan.getRingkasanBank']],
                ['route' => 'laporan.getBukuBesar', 'icon' => 'book', 'label' => 'Buku Besar', 'match' => ['laporan.getBukuBesar']],
                ['route' => 'laporan.getBukuBesarPembantu', 'icon' => 'library_books', 'label' => 'Buku Besar Pembantu', 'match' => ['laporan.getBukuBesarPembantu']],
                ['route' => 'laporan.getJurnal', 'icon' => 'receipt_long', 'label' => 'Jurnal', 'match' => ['laporan.getJurnal']],
                ['route' => 'laporan.getTrialBalance', 'icon' => 'balance', 'label' => 'Trial Balance', 'match' => ['laporan.getTrialBalance']],
                ['route' => 'laporan.getTutupBuku', 'icon' => 'lock', 'label' => 'Tutup Buku', 'match' => ['laporan.getTutupBuku', 'laporan.postTutupBuku']],
            ],
        ],
        [
            'label' => 'Laporan Penjualan',
            'items' => [
                ['icon' => 'sell', 'label' => 'Transaksi', 'children' => [
                    ['route' => 'laporan.getPenjualan', 'label' => 'Penjualan', 'match' => ['laporan.getPenjualan']],
                    ['route' => 'laporan.getDetailPenjualan', 'label' => 'Detail Penjualan', 'match' => ['laporan.getDetailPenjualan']],
                    ['route' => 'laporan.getTagihanPelanggan', 'label' => 'Tagihan Pelanggan', 'match' => ['laporan.getTagihanPelanggan']],
                    ['route' => 'laporan.getPelunasanTagihan', 'label' => 'Pelunasan Tagihan', 'match' => ['laporan.getPelunasanTagihan']],
                    ['route' => 'laporan.getRekapPembayaranPelanggan', 'label' => 'Rekap Pembayaran', 'match' => ['laporan.getRekapPembayaranPelanggan']],
                ]],
                ['icon' => 'group', 'label' => 'Piutang', 'children' => [
                    ['route' => 'laporan.getUmurPiutang', 'label' => 'Umur Piutang', 'match' => ['laporan.getUmurPiutang']],
                    ['route' => 'laporan.getPiutangPelanggan', 'label' => 'Piutang Pelanggan', 'match' => ['laporan.getPiutangPelanggan']],
                    ['route' => 'laporan.getSaldoPiutangTermin', 'label' => 'Saldo Piutang Termin', 'match' => ['laporan.getSaldoPiutangTermin']],
                    ['route' => 'laporan.getCreditMemo', 'label' => 'Credit Memo', 'match' => ['laporan.getCreditMemo']],
                ]],
                ['icon' => 'analytics', 'label' => 'Analisis', 'children' => [
                    ['route' => 'laporan.getProfitabilitasProduk', 'label' => 'Profitabilitas Produk', 'match' => ['laporan.getProfitabilitasProduk']],
                    ['route' => 'laporan.getProfitabilitasTagihan', 'label' => 'Profitabilitas per Tagihan', 'match' => ['laporan.getProfitabilitasTagihan']],
                    ['route' => 'laporan.getPendapatanPelanggan', 'label' => 'Pendapatan per Pelanggan', 'match' => ['laporan.getPendapatanPelanggan']],
                    ['route' => 'laporan.getPenjualanProduk', 'label' => 'Penjualan per Produk', 'match' => ['laporan.getPenjualanProduk']],
                    ['route' => 'laporan.getPenjualanKategori', 'label' => 'Per Kategori', 'match' => ['laporan.getPenjualanKategori']],
                    ['route' => 'laporan.getPenjualanPeriode', 'label' => 'Per Periode', 'match' => ['laporan.getPenjualanPeriode']],
                    ['route' => 'laporan.getPenjualanProdukPelanggan', 'label' => 'Produk per Pelanggan', 'match' => ['laporan.getPenjualanProdukPelanggan']],
                    ['route' => 'laporan.getPemesananProduk', 'label' => 'Pemesanan per Produk', 'match' => ['laporan.getPemesananProduk']],
                    ['route' => 'laporan.getPenjualanSales', 'label' => 'Per Sales', 'match' => ['laporan.getPenjualanSales']],
                    ['route' => 'laporan.getPenjualanRegion', 'label' => 'Per Region', 'match' => ['laporan.getPenjualanRegion']],
                ]],
                ['icon' => 'local_shipping', 'label' => 'Operasional', 'children' => [
                    ['route' => 'laporan.getPengirimanPenjualan', 'label' => 'Pengiriman', 'match' => ['laporan.getPengirimanPenjualan']],
                    ['route' => 'laporan.getOngkosKirimEkspedisi', 'label' => 'Ongkir per Ekspedisi', 'match' => ['laporan.getOngkosKirimEkspedisi']],
                    ['route' => 'laporan.getRiwayatHargaJual', 'label' => 'Riwayat Harga Jual', 'match' => ['laporan.getRiwayatHargaJual']],
                    ['route' => 'laporan.getReturProduk', 'label' => 'Retur per Produk', 'match' => ['laporan.getReturProduk']],
                    ['route' => 'laporan.getPaymentConnect', 'label' => 'Payment Connect', 'match' => ['laporan.getPaymentConnect']],
                    ['route' => 'laporan.getPaymentGateway', 'label' => 'Payment Gateway', 'match' => ['laporan.getPaymentGateway']],
                ]],
            ],
        ],
        [
            'label' => 'Laporan Pembelian',
            'items' => [
                ['icon' => 'shopping_cart', 'label' => 'Transaksi', 'children' => [
                    ['route' => 'laporan.getPembelian', 'label' => 'Pembelian', 'match' => ['laporan.getPembelian']],
                    ['route' => 'laporan.getDetailPembelian', 'label' => 'Detail Pembelian', 'match' => ['laporan.getDetailPembelian']],
                    ['route' => 'laporan.getTagihanVendor', 'label' => 'Tagihan Vendor', 'match' => ['laporan.getTagihanVendor']],
                    ['route' => 'laporan.getPelunasanPembelian', 'label' => 'Pelunasan Pembelian', 'match' => ['laporan.getPelunasanPembelian']],
                ]],
                ['icon' => 'store', 'label' => 'Hutang', 'children' => [
                    ['route' => 'laporan.getUmurHutang', 'label' => 'Umur Hutang', 'match' => ['laporan.getUmurHutang']],
                    ['route' => 'laporan.getHutangVendor', 'label' => 'Hutang Vendor', 'match' => ['laporan.getHutangVendor']],
                    ['route' => 'laporan.getSaldoHutangTermin', 'label' => 'Saldo Hutang Termin', 'match' => ['laporan.getSaldoHutangTermin']],
                    ['route' => 'laporan.getDebitMemo', 'label' => 'Debit Memo', 'match' => ['laporan.getDebitMemo']],
                ]],
                ['icon' => 'analytics', 'label' => 'Analisis', 'children' => [
                    ['route' => 'laporan.getPembelianProduk', 'label' => 'Per Produk', 'match' => ['laporan.getPembelianProduk']],
                    ['route' => 'laporan.getPembelianVendor', 'label' => 'Per Vendor', 'match' => ['laporan.getPembelianVendor']],
                    ['route' => 'laporan.getPembelianPeriode', 'label' => 'Per Periode', 'match' => ['laporan.getPembelianPeriode']],
                    ['route' => 'laporan.getPembelianProdukVendor', 'label' => 'Produk per Vendor', 'match' => ['laporan.getPembelianProdukVendor']],
                    ['route' => 'laporan.getPembelianRegion', 'label' => 'Per Region', 'match' => ['laporan.getPembelianRegion']],
                ]],
                ['icon' => 'local_shipping', 'label' => 'Operasional', 'children' => [
                    ['route' => 'laporan.getPengirimanPembelian', 'label' => 'Pengiriman', 'match' => ['laporan.getPengirimanPembelian']],
                    ['route' => 'laporan.getReturPembelianProduk', 'label' => 'Retur Pembelian', 'match' => ['laporan.getReturPembelianProduk']],
                ]],
            ],
        ],
        [
            'label' => 'CMS',
            'items' => [
                ['route' => 'cms-type.getTable', 'icon' => 'category', 'label' => 'Types', 'match' => ['cms-type.*']],
                ['route' => 'field.getTable', 'icon' => 'input', 'label' => 'Fields', 'match' => ['field.*']],
                ['route' => 'section.getTable', 'icon' => 'view_agenda', 'label' => 'Sections', 'match' => ['section.*']],
                ['route' => 'content.getTable', 'icon' => 'article', 'label' => 'Content', 'match' => ['content.*']],
                ['route' => 'category.getTable', 'icon' => 'sell', 'label' => 'Categories', 'match' => ['category.*']],
                ['route' => 'tag.getTable', 'icon' => 'label', 'label' => 'Tags', 'match' => ['tag.*']],
                ['route' => 'menu.getTable', 'icon' => 'menu', 'label' => 'Menus', 'match' => ['menu.*']],
            ],
        ],
        [
            'label' => 'Settings',
            'items' => [
                ['route' => 'settings.website', 'icon' => 'language', 'label' => 'Website'],
                ['route' => 'settings.env', 'icon' => 'settings', 'label' => 'Environment'],
                ['route' => 'native-bridge-test', 'icon' => 'phone_android', 'label' => 'NativeBridge Test'],
            ],
        ],
    ],

    'bottom_nav' => [

        ['route' => 'dashboard', 'icon' => 'home', 'label' => 'Left'],
        ['route' => 'dashboard', 'icon' => 'home', 'label' => 'Kiri'],
        ['route' => 'dashboard', 'icon' => 'home', 'label' => 'Home'],
        ['route' => 'dashboard', 'icon' => 'home', 'label' => 'Kanan'],
        ['route' => 'dashboard', 'icon' => 'home', 'label' => 'Right'],

    ],

];
