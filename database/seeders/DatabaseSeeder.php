<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Table;
use App\Models\User;
use App\Models\VariationGroup;
use App\Models\VariationOption;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Akun Pengguna (Admin, Kasir, Dapur)
        User::updateOrCreate(
            ['email' => 'admin@kafekayla.com'],
            [
                'name' => 'Administrator Kayla',
                'username' => 'admin',
                'role' => 'admin',
                'password' => Hash::make('admin123'),
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'kasir@kafekayla.com'],
            [
                'name' => 'Kasir Utama',
                'username' => 'kasir',
                'role' => 'cashier',
                'password' => Hash::make('kasir123'),
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'dapur@kafekayla.com'],
            [
                'name' => 'Chef Dapur Kayla',
                'username' => 'dapur',
                'role' => 'kitchen',
                'password' => Hash::make('dapur123'),
                'is_active' => true,
            ]
        );

        // 2. Data Meja & Token QR
        for ($i = 1; $i <= 10; $i++) {
            $tableNum = sprintf('%02d', $i);
            Table::updateOrCreate(
                ['table_number' => $tableNum],
                [
                    'table_token' => 'token-meja-' . $tableNum . '-' . Str::random(12),
                    'capacity' => ($i <= 6) ? 4 : 6,
                    'is_active' => true,
                ]
            );
        }

        // 3. Kategori Menu
        $catCoffee = Category::create([
            'name' => 'Coffee',
            'slug' => 'coffee',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $catNonCoffee = Category::create([
            'name' => 'Non-Coffee',
            'slug' => 'non-coffee',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $catFood = Category::create([
            'name' => 'Makanan Utama',
            'slug' => 'makanan-utama',
            'sort_order' => 3,
            'is_active' => true,
        ]);

        $catSnacks = Category::create([
            'name' => 'Camilan & Snack',
            'slug' => 'camilan-snack',
            'sort_order' => 4,
            'is_active' => true,
        ]);

        // 4. Menu & Variasi
        // Menu 1: Kopi Susu Gula Aren
        $menu1 = Menu::create([
            'category_id' => $catCoffee->id,
            'name' => 'Kopi Susu Gula Aren',
            'slug' => 'kopi-susu-gula-aren',
            'description' => 'Espresso robusta & arabika pilihan berpadu dengan susu segar dan sirup gula aren asli khas Nusantara.',
            'base_price' => 18000.00,
            'is_available' => true,
        ]);

        $vg1 = VariationGroup::create([
            'menu_id' => $menu1->id,
            'name' => 'Ukuran',
            'is_required' => true,
            'allow_multiple' => false,
        ]);
        VariationOption::create(['variation_group_id' => $vg1->id, 'name' => 'Regular', 'extra_price' => 0]);
        VariationOption::create(['variation_group_id' => $vg1->id, 'name' => 'Large', 'extra_price' => 4000]);

        $vg2 = VariationGroup::create([
            'menu_id' => $menu1->id,
            'name' => 'Level Gula',
            'is_required' => true,
            'allow_multiple' => false,
        ]);
        VariationOption::create(['variation_group_id' => $vg2->id, 'name' => 'Normal Sugar (100%)', 'extra_price' => 0]);
        VariationOption::create(['variation_group_id' => $vg2->id, 'name' => 'Less Sugar (50%)', 'extra_price' => 0]);
        VariationOption::create(['variation_group_id' => $vg2->id, 'name' => 'No Sugar (0%)', 'extra_price' => 0]);

        $vg3 = VariationGroup::create([
            'menu_id' => $menu1->id,
            'name' => 'Level Es',
            'is_required' => true,
            'allow_multiple' => false,
        ]);
        VariationOption::create(['variation_group_id' => $vg3->id, 'name' => 'Normal Ice', 'extra_price' => 0]);
        VariationOption::create(['variation_group_id' => $vg3->id, 'name' => 'Less Ice', 'extra_price' => 0]);
        VariationOption::create(['variation_group_id' => $vg3->id, 'name' => 'No Ice', 'extra_price' => 0]);

        // Menu 2: Caffe Latte
        $menu2 = Menu::create([
            'category_id' => $catCoffee->id,
            'name' => 'Caffe Latte',
            'slug' => 'caffe-latte',
            'description' => 'Kombinasi klasik double shot espresso dengan steamed milk yang lembut dan creamy.',
            'base_price' => 20000.00,
            'is_available' => true,
        ]);
        $vgLatte1 = VariationGroup::create(['menu_id' => $menu2->id, 'name' => 'Suhu', 'is_required' => true, 'allow_multiple' => false]);
        VariationOption::create(['variation_group_id' => $vgLatte1->id, 'name' => 'Dingin (Iced)', 'extra_price' => 2000]);
        VariationOption::create(['variation_group_id' => $vgLatte1->id, 'name' => 'Panas (Hot)', 'extra_price' => 0]);

        // Menu 3: Americano
        $menu3 = Menu::create([
            'category_id' => $catCoffee->id,
            'name' => 'Americano',
            'slug' => 'americano',
            'description' => 'Espresso murni dicampur air panas atau dingin dengan aroma kopi yang pekat dan menyegarkan.',
            'base_price' => 15000.00,
            'is_available' => true,
        ]);
        $vgAmer = VariationGroup::create(['menu_id' => $menu3->id, 'name' => 'Sajian', 'is_required' => true, 'allow_multiple' => false]);
        VariationOption::create(['variation_group_id' => $vgAmer->id, 'name' => 'Iced Americano', 'extra_price' => 1000]);
        VariationOption::create(['variation_group_id' => $vgAmer->id, 'name' => 'Hot Americano', 'extra_price' => 0]);

        // Menu 4: Matcha Fusion Latte
        $menu4 = Menu::create([
            'category_id' => $catNonCoffee->id,
            'name' => 'Matcha Latte',
            'slug' => 'matcha-latte',
            'description' => 'Bubuk matcha murni dari Jepang dipadukan dengan susu segar premium bercita rasa otentik.',
            'base_price' => 22000.00,
            'is_available' => true,
        ]);
        $vgMatcha1 = VariationGroup::create(['menu_id' => $menu4->id, 'name' => 'Ukuran', 'is_required' => true, 'allow_multiple' => false]);
        VariationOption::create(['variation_group_id' => $vgMatcha1->id, 'name' => 'Regular', 'extra_price' => 0]);
        VariationOption::create(['variation_group_id' => $vgMatcha1->id, 'name' => 'Large', 'extra_price' => 5000]);

        // Menu 5: Red Velvet Velvet Cheese
        $menu5 = Menu::create([
            'category_id' => $catNonCoffee->id,
            'name' => 'Red Velvet Cheese',
            'slug' => 'red-velvet-cheese',
            'description' => 'Minuman manis red velvet dengan aroma vanila gurih, disajikan dengan topping cream cheese.',
            'base_price' => 24000.00,
            'is_available' => true,
        ]);

        // Menu 6: Nasi Goreng Spesial Kayla
        $menu6 = Menu::create([
            'category_id' => $catFood->id,
            'name' => 'Nasi Goreng Spesial Kafe Kayla',
            'slug' => 'nasi-goreng-spesial-kafe-kayla',
            'description' => 'Nasi goreng racikan bumbu rempah spesial kafe, dilengkapi suwiran ayam, acar, dan kerupuk gurih.',
            'base_price' => 25000.00,
            'is_available' => true,
        ]);
        $vgNasgor1 = VariationGroup::create(['menu_id' => $menu6->id, 'name' => 'Level Kepedasan', 'is_required' => true, 'allow_multiple' => false]);
        VariationOption::create(['variation_group_id' => $vgNasgor1->id, 'name' => 'Tidak Pedas', 'extra_price' => 0]);
        VariationOption::create(['variation_group_id' => $vgNasgor1->id, 'name' => 'Pedas Sedang', 'extra_price' => 0]);
        VariationOption::create(['variation_group_id' => $vgNasgor1->id, 'name' => 'Sangat Pedas (Level 3)', 'extra_price' => 0]);

        $vgNasgor2 = VariationGroup::create(['menu_id' => $menu6->id, 'name' => 'Topping Ekstra', 'is_required' => false, 'allow_multiple' => true]);
        VariationOption::create(['variation_group_id' => $vgNasgor2->id, 'name' => 'Telur Mata Sapi', 'extra_price' => 4000]);
        VariationOption::create(['variation_group_id' => $vgNasgor2->id, 'name' => 'Sosis Sapi Panggang', 'extra_price' => 4000]);

        // Menu 7: Mie Goreng Kayla
        $menu7 = Menu::create([
            'category_id' => $catFood->id,
            'name' => 'Mie Goreng Rempah',
            'slug' => 'mie-goreng-rempah',
            'description' => 'Mie kenyal ditumis dengan sayuran segar, telur orak-arik, dan saus tiram gurih.',
            'base_price' => 23000.00,
            'is_available' => true,
        ]);

        // Menu 8: Kentang Goreng
        $menu8 = Menu::create([
            'category_id' => $catSnacks->id,
            'name' => 'French Fries Golden Crispy',
            'slug' => 'french-fries-golden-crispy',
            'description' => 'Kentang goreng renyah di luar lembut di dalam dengan taburan garam gurih dan pilihan cocolan saus.',
            'base_price' => 16000.00,
            'is_available' => true,
        ]);
        $vgFries = VariationGroup::create(['menu_id' => $menu8->id, 'name' => 'Pilihan Saus', 'is_required' => true, 'allow_multiple' => false]);
        VariationOption::create(['variation_group_id' => $vgFries->id, 'name' => 'Saus Sambal & Tomat', 'extra_price' => 0]);
        VariationOption::create(['variation_group_id' => $vgFries->id, 'name' => 'Saus Keju Creamy', 'extra_price' => 3000]);

        // Menu 9: Cireng Rujak
        $menu9 = Menu::create([
            'category_id' => $catSnacks->id,
            'name' => 'Cireng Crispy Bumbu Rujak',
            'slug' => 'cireng-crispy-bumbu-rujak',
            'description' => 'Cireng renyah kenyal khas Sunda dengan sambal rujak gula merah pedas manis yang nagih.',
            'base_price' => 15000.00,
            'is_available' => true,
        ]);

        // Menu 10: Roti Bakar Cokelat Keju
        $menu10 = Menu::create([
            'category_id' => $catSnacks->id,
            'name' => 'Roti Bakar Cokelat Keju',
            'slug' => 'roti-bakar-cokelat-keju',
            'description' => 'Roti bakar empuk dengan isian cokelat lumer berlimpah parutan keju cheddar gurih.',
            'base_price' => 18000.00,
            'is_available' => true,
        ]);
    }
}
