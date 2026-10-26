<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\KitchenController;
use App\Models\Table;
use Illuminate\Support\Facades\Route;

// ==========================================
// 1. HALAMAN UTAMA & PELANGGAN (QR SCAN)
// ==========================================

Route::get('/', function () {
    // Jika ada session meja aktif, langsung arahkan ke menu meja tersebut
    if (session()->has('customer_table_number') && session()->has('customer_table_token')) {
        return redirect()->route('customer.menu', [
            'table_number' => session('customer_table_number'),
            'token' => session('customer_table_token'),
        ]);
    }

    $sampleTables = Table::where('is_active', true)->take(6)->get();
    return view('welcome', compact('sampleTables'));
})->name('home');

// Menu Digital Pelanggan
Route::get('/meja/{table_number}', [CustomerController::class, 'showMenu'])->name('customer.menu');
Route::post('/checkout', [CustomerController::class, 'checkout'])->name('customer.checkout');
Route::get('/pesanan/{orderNumber}', [CustomerController::class, 'orderStatus'])->name('customer.order.status');
Route::get('/api/pesanan/{orderNumber}/status', [CustomerController::class, 'apiOrderStatus'])->name('customer.order.status.api');
Route::post('/pesanan/{orderNumber}/simulate-qris', [CustomerController::class, 'simulateQris'])->name('customer.order.simulate_qris');

// ==========================================
// 2. AUTENTIKASI STAF
// ==========================================

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ==========================================
// 3. MODUL KASIR (ROLE: CASHIER, ADMIN)
// ==========================================

Route::middleware(['auth', 'role:cashier,admin'])->prefix('kasir')->name('cashier.')->group(function () {
    Route::get('/', [CashierController::class, 'dashboard'])->name('dashboard');
    Route::get('/api/orders', [CashierController::class, 'apiOrders'])->name('api.orders');
    Route::post('/pesanan/{order}/confirm', [CashierController::class, 'confirmOrder'])->name('orders.confirm');
    Route::post('/pesanan/{order}/cancel', [CashierController::class, 'cancelOrder'])->name('orders.cancel');
    Route::post('/pesanan/{order}/pay-cash', [CashierController::class, 'verifyCashPayment'])->name('orders.pay_cash');
    Route::post('/pesanan/{order}/send-kitchen', [CashierController::class, 'sendToKitchen'])->name('orders.send_kitchen');
    Route::post('/pesanan/{order}/ready', [CashierController::class, 'markReady'])->name('orders.ready');
    Route::post('/pesanan/{order}/complete', [CashierController::class, 'markCompleted'])->name('orders.complete');
});

// ==========================================
// 4. MODUL DAPUR / KDS (ROLE: KITCHEN, ADMIN)
// ==========================================

Route::middleware(['auth', 'role:kitchen,admin'])->prefix('dapur')->name('kitchen.')->group(function () {
    Route::get('/', [KitchenController::class, 'index'])->name('kds');
    Route::get('/api/orders', [KitchenController::class, 'apiOrders'])->name('api.orders');
});

// ==========================================
// 5. MODUL ADMIN (ROLE: ADMIN)
// ==========================================

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    // Menu
    Route::get('/menus', [AdminController::class, 'menus'])->name('menus');
    Route::post('/menus', [AdminController::class, 'storeMenu'])->name('menus.store');
    Route::put('/menus/{menu}', [AdminController::class, 'updateMenu'])->name('menus.update');
    Route::post('/menus/{menu}/toggle', [AdminController::class, 'toggleMenuAvailability'])->name('menus.toggle');
    Route::delete('/menus/{menu}', [AdminController::class, 'destroyMenu'])->name('menus.destroy');

    // Variasi Menu
    Route::post('/menus/{menu}/variation-group', [AdminController::class, 'storeVariationGroup'])->name('menus.variation_group.store');
    Route::post('/variation-group/{group}/option', [AdminController::class, 'storeVariationOption'])->name('variation_group.option.store');
    Route::delete('/variation-option/{option}', [AdminController::class, 'destroyVariationOption'])->name('variation_option.destroy');

    // Meja & QR
    Route::get('/tables', [AdminController::class, 'tables'])->name('tables');
    Route::post('/tables', [AdminController::class, 'storeTable'])->name('tables.store');
    Route::post('/tables/{table}/toggle', [AdminController::class, 'toggleTable'])->name('tables.toggle');
    Route::post('/tables/{table}/regenerate-qr', [AdminController::class, 'regenerateTableQr'])->name('tables.regenerate_qr');
    Route::delete('/tables/{table}', [AdminController::class, 'destroyTable'])->name('tables.destroy');

    // Staf
    Route::get('/staff', [AdminController::class, 'staff'])->name('staff');
    Route::post('/staff', [AdminController::class, 'storeStaff'])->name('staff.store');
    Route::post('/staff/{user}/toggle', [AdminController::class, 'toggleStaff'])->name('staff.toggle');

    // Laporan
    Route::get('/reports', [AdminController::class, 'reports'])->name('reports');
});
