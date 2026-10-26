<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Table;
use App\Models\User;
use App\Models\VariationGroup;
use App\Models\VariationOption;
use App\Services\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    /**
     * Dashboard ringkasan admin.
     */
    public function dashboard()
    {
        $today = now()->startOfDay();
        $thisMonth = now()->startOfMonth();

        $stats = [
            'omzet_today' => Payment::where('payment_status', 'paid')->where('paid_at', '>=', $today)->sum('amount'),
            'orders_today' => Order::where('created_at', '>=', $today)->count(),
            'omzet_month' => Payment::where('payment_status', 'paid')->where('paid_at', '>=', $thisMonth)->sum('amount'),
            'orders_month' => Order::where('created_at', '>=', $thisMonth)->count(),
            'total_menus' => Menu::count(),
            'total_tables' => Table::where('is_active', true)->count(),
        ];

        // 5 Pesanan terbaru
        $recentOrders = Order::with(['table', 'latestPayment'])
            ->latest()
            ->take(8)
            ->get();

        // 5 Menu terlaris
        $bestSellers = Menu::withCount(['orderItems as total_sold' => function ($q) {
            $q->select(\Illuminate\Support\Facades\DB::raw('COALESCE(SUM(quantity), 0)'));
        }])
        ->orderBy('total_sold', 'desc')
        ->take(5)
        ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'recentOrders' => $recentOrders,
            'bestSellers' => $bestSellers,
        ]);
    }

    // ==========================================
    // 1. MANAJEMEN MENU & KATEGORI
    // ==========================================

    public function menus()
    {
        $menus = Menu::with(['category', 'variationGroups.options'])
            ->latest()
            ->paginate(15);
        $categories = Category::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.menus.index', [
            'menus' => $menus,
            'categories' => $categories,
        ]);
    }

    public function storeMenu(Request $request)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'is_available' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('menus', 'public');
        }

        Menu::create([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::random(4),
            'description' => $validated['description'] ?? null,
            'base_price' => $validated['base_price'],
            'is_available' => $request->boolean('is_available', true),
            'image' => $imagePath,
        ]);

        return back()->with('success', 'Menu berhasil ditambahkan.');
    }

    public function updateMenu(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'base_price' => ['required', 'numeric', 'min:0'],
            'is_available' => ['nullable', 'boolean'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        $imagePath = $menu->image;
        if ($request->hasFile('image')) {
            if ($menu->image && Storage::disk('public')->exists($menu->image)) {
                Storage::disk('public')->delete($menu->image);
            }
            $imagePath = $request->file('image')->store('menus', 'public');
        }

        $menu->update([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'base_price' => $validated['base_price'],
            'is_available' => $request->boolean('is_available', true),
            'image' => $imagePath,
        ]);

        return back()->with('success', 'Menu berhasil diperbarui.');
    }

    public function toggleMenuAvailability(Menu $menu)
    {
        $menu->update(['is_available' => !$menu->is_available]);
        $status = $menu->is_available ? 'tersedia' : 'habis';
        return back()->with('success', "Status menu '{$menu->name}' diubah menjadi {$status}.");
    }

    public function destroyMenu(Menu $menu)
    {
        $menu->delete();
        return back()->with('success', 'Menu berhasil dihapus.');
    }

    // Variasi Menu
    public function storeVariationGroup(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'is_required' => ['nullable', 'boolean'],
            'allow_multiple' => ['nullable', 'boolean'],
        ]);

        $group = VariationGroup::create([
            'menu_id' => $menu->id,
            'name' => $validated['name'],
            'is_required' => $request->boolean('is_required', true),
            'allow_multiple' => $request->boolean('allow_multiple', false),
        ]);

        return back()->with('success', "Grup variasi '{$group->name}' berhasil dibuat untuk menu {$menu->name}.");
    }

    public function storeVariationOption(Request $request, VariationGroup $group)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'extra_price' => ['required', 'numeric', 'min:0'],
        ]);

        VariationOption::create([
            'variation_group_id' => $group->id,
            'name' => $validated['name'],
            'extra_price' => $validated['extra_price'],
            'is_available' => true,
        ]);

        return back()->with('success', "Opsi '{$validated['name']}' berhasil ditambahkan ke variasi {$group->name}.");
    }

    public function destroyVariationOption(VariationOption $option)
    {
        $option->delete();
        return back()->with('success', 'Opsi variasi berhasil dihapus.');
    }

    // ==========================================
    // 2. MANAJEMEN MEJA & QR CODE
    // ==========================================

    public function tables(QrCodeService $qrCodeService)
    {
        $tables = Table::orderBy('table_number')->get();

        // Buat Data URI QR SVG untuk masing-masing meja
        foreach ($tables as $table) {
            $table->qr_data_uri = $qrCodeService->generateDataUri($table->qr_url, 260);
        }

        return view('admin.tables.index', [
            'tables' => $tables,
        ]);
    }

    public function storeTable(Request $request)
    {
        $validated = $request->validate([
            'table_number' => ['required', 'string', 'max:20', 'unique:tables,table_number'],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $table = Table::create([
            'table_number' => $validated['table_number'],
            'table_token' => 'token-meja-' . $validated['table_number'] . '-' . Str::random(12),
            'capacity' => $validated['capacity'],
            'is_active' => true,
        ]);

        return back()->with('success', "Meja {$table->table_number} dan QR Code berhasil dibuat.");
    }

    public function toggleTable(Table $table)
    {
        $table->update(['is_active' => !$table->is_active]);
        $status = $table->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Meja {$table->table_number} berhasil {$status}.");
    }

    public function regenerateTableQr(Table $table)
    {
        $table->update([
            'table_token' => 'token-meja-' . $table->table_number . '-' . Str::random(12),
        ]);

        return back()->with('success', "QR Code Meja {$table->table_number} berhasil diperbarui (token baru).");
    }

    public function destroyTable(Table $table)
    {
        $table->delete();
        return back()->with('success', "Meja {$table->table_number} berhasil dihapus.");
    }

    // ==========================================
    // 3. MANAJEMEN STAF (ADMIN, KASIR, DAPUR)
    // ==========================================

    public function staff()
    {
        $users = User::latest()->get();
        return view('admin.staff.index', [
            'users' => $users,
        ]);
    }

    public function storeStaff(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['required', 'email', 'max:100', 'unique:users,email'],
            'role' => ['required', 'in:admin,cashier,kitchen'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        return back()->with('success', "Akun staf {$validated['name']} ({$validated['role']}) berhasil dibuat.");
    }

    public function toggleStaff(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Akun {$user->name} berhasil {$status}.");
    }

    // ==========================================
    // 4. LAPORAN PENJUALAN & TRANSAKSI
    // ==========================================

    public function reports(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->query('end_date', now()->format('Y-m-d'));

        $startDateTime = $startDate . ' 00:00:00';
        $endDateTime = $endDate . ' 23:59:59';

        $orders = Order::whereBetween('created_at', [$startDateTime, $endDateTime])
            ->with(['table', 'latestPayment', 'items.variations'])
            ->latest()
            ->paginate(20);

        $summary = [
            'total_revenue' => Payment::where('payment_status', 'paid')
                ->whereBetween('paid_at', [$startDateTime, $endDateTime])
                ->sum('amount'),
            'cash_revenue' => Payment::where('payment_method', 'cash')
                ->where('payment_status', 'paid')
                ->whereBetween('paid_at', [$startDateTime, $endDateTime])
                ->sum('amount'),
            'qris_revenue' => Payment::where('payment_method', 'qris')
                ->where('payment_status', 'paid')
                ->whereBetween('paid_at', [$startDateTime, $endDateTime])
                ->sum('amount'),
            'total_orders' => Order::whereBetween('created_at', [$startDateTime, $endDateTime])->count(),
            'completed_orders' => Order::where('order_status', 'completed')
                ->whereBetween('created_at', [$startDateTime, $endDateTime])
                ->count(),
            'cancelled_orders' => Order::where('order_status', 'cancelled')
                ->whereBetween('created_at', [$startDateTime, $endDateTime])
                ->count(),
        ];

        // Menu terlaris dalam periode
        $topMenus = Menu::select('menus.name')
            ->join('order_items', 'menus.id', '=', 'order_items.menu_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$startDateTime, $endDateTime])
            ->where('orders.order_status', '!=', 'cancelled')
            ->selectRaw('SUM(order_items.quantity) as total_qty, SUM(order_items.subtotal) as total_sales')
            ->groupBy('menus.id', 'menus.name')
            ->orderBy('total_qty', 'desc')
            ->take(10)
            ->get();

        return view('admin.reports.index', [
            'orders' => $orders,
            'summary' => $summary,
            'topMenus' => $topMenus,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }
}
