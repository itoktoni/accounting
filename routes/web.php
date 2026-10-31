<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WebsiteSettingController;
use App\Models\Notification;
use App\Services\CentrifugoService;
use Buki\AutoRoute\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::middleware('auth')->post('/centrifugo/token', function (Request $request) {
    if (! config('langkahkecil.notification_enable')) {
        return response()->json(['token' => 'disabled']);
    }

    $centrifugo = app(CentrifugoService::class);
    $user = Auth::user();

    if ($request->input('channel')) {
        return response()->json([
            'token' => $centrifugo->generateSubscriptionToken((string) $user->id, $request->input('channel')),
        ]);
    }

    return response()->json([
        'token' => $centrifugo->generateConnectionToken((string) $user->id),
    ]);
});

Route::middleware(['auth', 'verified', 'access'])->group(function () {

    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::auto('/user', 'UsersController', ['name' => 'user']);

    Route::auto('/akun', 'AkunController', ['name' => 'akun']);
    Route::auto('/jurnal', 'JurnalController', ['name' => 'jurnal']);
    Route::auto('/laporan', 'LaporanController', ['name' => 'laporan']);
    Route::auto('/produk', 'ProdukController', ['name' => 'produk']);
    Route::auto('/produksi', 'ProduksiController', ['name' => 'produksi']);
    Route::auto('/produksitemplate', 'ProduksiTemplateController', ['name' => 'produksitemplate']);
    Route::auto('/customer', 'CustomerController', ['name' => 'customer']);
    Route::auto('/vendor', 'VendorController', ['name' => 'vendor']);
    Route::auto('/piutang', 'PiutangController', ['name' => 'piutang']);
    Route::auto('/hutang', 'HutangController', ['name' => 'hutang']);
    Route::auto('/invoice', 'InvoiceController', ['name' => 'invoice']);
    Route::auto('/penawaran', 'PenawaranController', ['name' => 'penawaran']);
    Route::auto('/termin', 'TerminController', ['name' => 'termin']);
    Route::auto('/diskon', 'DiskonController', ['name' => 'diskon']);
    Route::auto('/pajak', 'PajakController', ['name' => 'pajak']);
    Route::auto('/purchaseorder', 'PurchaseOrderController', ['name' => 'purchaseorder']);
    Route::auto('/proyek', 'ProyekController', ['name' => 'proyek']);
    Route::auto('/anggaran', 'AnggaranController', ['name' => 'anggaran']);
    Route::auto('/posshift', 'PosShiftController', ['name' => 'posshift']);
    Route::auto('/possale', 'PosSaleController', ['name' => 'possale']);
    Route::auto('/voucher', 'VoucherController', ['name' => 'voucher']);
    Route::auto('/karyawan', 'KaryawanController', ['name' => 'karyawan']);
    Route::auto('/payroll', 'PayrollController', ['name' => 'payroll']);
    Route::auto('/asettetap', 'AsetTetapController', ['name' => 'asettetap']);

    Route::get('/native-bridge-test', function () {
        return view('pages.settings.native-bridge-test');
    })->name('native-bridge-test');

    Route::get('/settings/website', [WebsiteSettingController::class, 'index'])->name('settings.website');
    Route::post('/settings/website', [WebsiteSettingController::class, 'save'])->name('settings.website.save');

    Route::prefix('notifications-web')->group(function () {
        Route::get('/', function (Request $request) {
            $notifications = Notification::where('user_id', Auth::id())
                ->orderByDesc('created_at')
                ->limit($request->input('limit', 50))
                ->get();

            $unreadCount = Notification::where('user_id', Auth::id())
                ->where('read', false)
                ->count();

            return response()->json([
                'notifications' => $notifications->map(fn ($n) => [
                    'id' => $n->id,
                    'icon' => $n->icon,
                    'iconColor' => $n->icon_color,
                    'title' => $n->title,
                    'body' => $n->body,
                    'url' => $n->url,
                    'type' => $n->type,
                    'read' => $n->read,
                    'time' => $n->created_at?->diffForHumans() ?? '',
                    'created_at' => $n->created_at->toIso8601String(),
                ]),
                'unread_count' => $unreadCount,
            ]);
        });

        Route::put('/{id}/read', function (int $id) {
            $notification = Notification::where('user_id', Auth::id())->findOrFail($id);
            $notification->update(['read' => true]);

            return response()->json(['message' => 'Marked as read']);
        });

        Route::put('/read-all', function () {
            Notification::where('user_id', Auth::id())
                ->where('read', false)
                ->update(['read' => true]);

            return response()->json(['message' => 'All marked as read']);
        });
    });
});

require __DIR__.'/settings.php';
