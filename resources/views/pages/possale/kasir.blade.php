<?php /** @var App\Models\PosSale $model */ ?>

<x-layouts::app>
    <x-breadcrumb :items="[['url' => moduleRoute('getTable'), 'label' => moduleLabel()], ['url' => '', 'label' => 'Kasir']]" />

    <div class="grid grid-cols-12 gap-4">
        <div class="col-span-12 lg:col-span-7">
            <x-card label="Produk">
                <div class="col-span-12 mb-3">
                    <input id="cari-produk" placeholder="Cari produk..." autocomplete="off"
                        class="w-full h-11 px-4 bg-white border border-outline-variant rounded-lg text-sm outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container" />
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 col-span-12" id="grid-produk">
                    @forelse($products as $product)
                    <button type="button" onclick="tambahCart({{ $product->field_primary }})"
                        data-nama="{{ strtolower($product->produk_nama) }}"
                        class="prod-card border border-outline-variant rounded-xl p-3 text-left hover:border-primary hover:bg-primary/5 active:scale-95 transition-all">
                        <p class="text-sm font-bold truncate">{{ $product->produk_nama }}</p>
                        <p class="text-sm font-semibold text-primary mt-1">Rp {{ formatAngka($product->produk_harga_jual) }}</p>
                        <p class="text-xs text-on-surface-variant">Stok: {{ $product->produk_stok }}</p>
                    </button>
                    @empty
                    <p class="text-sm text-on-surface-variant col-span-full">Belum ada produk. Tambah lewat modul produk.</p>
                    @endforelse
                </div>
            </x-card>
        </div>

        <div class="col-span-12 lg:col-span-5">
            <form id="form-checkout" method="POST" action="{{ moduleRoute('postCheckout') }}">
                @csrf
                <x-card label="Keranjang">
                    <div id="cart-list" class="col-span-12 space-y-2 min-h-24">
                        <p id="cart-kosong" class="text-sm text-on-surface-variant">Belum ada item. Klik produk untuk menambah.</p>
                    </div>
                    <div class="col-span-12 mt-3 p-3 rounded-xl bg-surface-container text-sm space-y-1">
                        <div class="flex justify-between"><span class="text-on-surface-variant">Total item</span><span id="cart-n" class="font-semibold">0</span></div>
                        <div class="flex justify-between text-lg"><span class="font-bold">Total</span><span id="cart-total" class="font-bold">Rp 0</span></div>
                        <div class="flex justify-between"><span class="text-on-surface-variant">Kembalian</span><span id="cart-kembali" class="font-semibold">Rp 0</span></div>
                    </div>
                    <x-select col="12" name="sale_shift_id" label="Shift" :options="$shifts->pluck('shift_username', 'shift_id')->toArray()" />
                    <x-input col="12" name="sale_customer" label="Customer" />
                    <x-select col="12" name="sale_metode_bayar" label="Metode Bayar" :options="['TUNAI' => 'TUNAI', 'QRIS' => 'QRIS', 'TRANSFER' => 'TRANSFER']" />
                    <x-input col="12" type="number" step="0.01" min="0" name="sale_bayar" label="Bayar (Rp)" id="fld-bayar" />
                    <input type="hidden" name="sale_total" id="fld-total" value="0" />
                    <input type="hidden" name="sale_akun_kas" value="1100" />
                    <div id="cart-hidden"></div>
                </x-card>

                <div class="flex gap-2 mt-3">
                    <button type="button" onclick="resetCart()"
                        class="flex-1 inline-flex items-center justify-center gap-1 h-11 px-4 text-sm font-semibold rounded-lg border border-outline-variant text-on-surface-variant hover:bg-surface-container transition-all">
                        <span class="material-symbols-outlined">close</span> Batal
                    </button>
                    <button type="submit"
                        class="flex-[2] inline-flex items-center justify-center gap-1 h-11 px-4 text-sm font-semibold rounded-lg bg-primary text-on-primary hover:bg-primary/90 shadow-sm transition-all active:scale-95">
                        <span class="material-symbols-outlined">payments</span> Bayar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    var PRODUK = @json($products->mapWithKeys(fn ($p) => [$p->field_primary => ['nama' => $p->produk_nama, 'harga' => (float) $p->produk_harga_jual, 'stok' => (int) $p->produk_stok]]));
    var cart = {};

    function fmtRp(n) { return 'Rp ' + Math.round(n).toLocaleString('id-ID'); }

    function tambahCart(id) {
        id = String(id);
        var p = PRODUK[id];
        if (!p) return;
        var cur = cart[id] ? cart[id].qty : 0;
        if (cur + 1 > p.stok) { alert('Stok ' + p.nama + ' tinggal ' + p.stok); return; }
        cart[id] = { qty: cur + 1, harga: p.harga, nama: p.nama };
        renderCart();
    }

    function ubahQty(id, d) {
        id = String(id);
        if (!cart[id]) return;
        var q = cart[id].qty + d;
        if (q <= 0) { delete cart[id]; }
        else {
            if (q > PRODUK[id].stok) { alert('Stok ' + PRODUK[id].nama + ' tinggal ' + PRODUK[id].stok); return; }
            cart[id].qty = q;
        }
        renderCart();
    }

    function hapusItem(id) { delete cart[String(id)]; renderCart(); }
    function resetCart() { cart = {}; document.getElementById('fld-bayar').value = ''; renderCart(); }

    function cartTotal() {
        var t = 0, n = 0;
        Object.values(cart).forEach(function (it) { t += it.qty * it.harga; n += it.qty; });
        return { total: t, n: n };
    }

    function renderCart() {
        var box = document.getElementById('cart-list');
        var ids = Object.keys(cart);
        var t = cartTotal();
        if (!ids.length) {
            box.innerHTML = '<p class="text-sm text-on-surface-variant">Belum ada item. Klik produk untuk menambah.</p>';
        } else {
            box.innerHTML = ids.map(function (id) {
                var it = cart[id];
                return '<div class="flex items-center gap-2 border border-outline-variant rounded-lg p-2">'
                    + '<div class="flex-1 min-w-0"><p class="text-sm font-semibold truncate">' + it.nama + '</p>'
                    + '<p class="text-xs text-on-surface-variant">' + fmtRp(it.harga) + '</p></div>'
                    + '<div class="flex items-center gap-1 shrink-0">'
                    + '<button type="button" onclick="ubahQty(\'' + id + '\',-1)" class="w-7 h-7 rounded-md border border-outline-variant font-bold">−</button>'
                    + '<span class="w-8 text-center text-sm font-bold">' + it.qty + '</span>'
                    + '<button type="button" onclick="ubahQty(\'' + id + '\',1)" class="w-7 h-7 rounded-md border border-outline-variant font-bold">+</button>'
                    + '</div>'
                    + '<p class="text-sm font-bold w-24 text-right shrink-0">' + fmtRp(it.qty * it.harga) + '</p>'
                    + '<button type="button" onclick="hapusItem(\'' + id + '\')" class="text-red-600 font-bold text-lg leading-none shrink-0">×</button>'
                    + '</div>';
            }).join('');
        }
        document.getElementById('cart-n').textContent = t.n;
        document.getElementById('cart-total').textContent = fmtRp(t.total);
        document.getElementById('fld-total').value = t.total;
        var bayar = parseFloat(document.getElementById('fld-bayar').value) || 0;
        document.getElementById('cart-kembali').textContent = fmtRp(bayar - t.total);
        var hid = document.getElementById('cart-hidden');
        hid.innerHTML = ids.map(function (id, i) {
            return '<input type="hidden" name="items[' + i + '][product_id]" value="' + id + '">'
                + '<input type="hidden" name="items[' + i + '][qty]" value="' + cart[id].qty + '">';
        }).join('');
    }

    document.getElementById('fld-bayar').addEventListener('input', renderCart);
    document.getElementById('cari-produk').addEventListener('input', function (e) {
        var q = e.target.value.toLowerCase();
        document.querySelectorAll('.prod-card').forEach(function (el) {
            el.style.display = el.dataset.nama.includes(q) ? '' : 'none';
        });
    });
    document.getElementById('form-checkout').addEventListener('submit', function (e) {
        var t = cartTotal();
        if (!t.n) { e.preventDefault(); alert('Keranjang masih kosong.'); return; }
        var bayar = parseFloat(document.getElementById('fld-bayar').value) || 0;
        if (bayar < t.total) { e.preventDefault(); alert('Uang bayar kurang ' + fmtRp(t.total - bayar)); }
    });
    renderCart();
    </script>
</x-layouts::app>
