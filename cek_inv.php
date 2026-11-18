<?php

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$i = App\Models\Invoice::find(2);
if (! $i) {
    echo "invoice 2 NOT FOUND\n";
    exit;
}
echo 'pelanggan='.$i->invoice_pelanggan.PHP_EOL;
echo 'alamat='.var_export($i->invoice_alamat, true).PHP_EOL;
echo 'telepon='.var_export($i->invoice_telepon, true).PHP_EOL;
$c = App\Models\Customer::where('customer_nama', $i->invoice_pelanggan)->first();
echo 'customer='.($c ? $c->customer_nama.' | alamat='.var_export($c->customer_alamat, true).' | telp='.var_export($c->customer_telepon, true) : 'TIDAK ADA').PHP_EOL;
