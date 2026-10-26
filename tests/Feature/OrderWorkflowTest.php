<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Table;
use App\Models\User;
use App\Models\VariationGroup;
use App\Models\VariationOption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected Table $table;
    protected User $admin;
    protected User $cashier;
    protected User $kitchen;
    protected Menu $menu;
    protected VariationOption $optionLarge;

    protected function setUp(): void
    {
        parent::setUp();

        $this->table = Table::create([
            'table_number' => '01',
            'table_token' => 'valid-secret-token-01',
            'capacity' => 4,
            'is_active' => true,
        ]);

        $this->admin = User::create([
            'name' => 'Admin User',
            'username' => 'admin_test',
            'email' => 'admin@test.com',
            'role' => 'admin',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->cashier = User::create([
            'name' => 'Cashier User',
            'username' => 'cashier_test',
            'email' => 'cashier@test.com',
            'role' => 'cashier',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $this->kitchen = User::create([
            'name' => 'Kitchen User',
            'username' => 'kitchen_test',
            'email' => 'kitchen@test.com',
            'role' => 'kitchen',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $category = Category::create([
            'name' => 'Kopi',
            'slug' => 'kopi',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $this->menu = Menu::create([
            'category_id' => $category->id,
            'name' => 'Kopi Susu Aren',
            'slug' => 'kopi-susu-aren',
            'description' => 'Kopi susu gula aren enak.',
            'base_price' => 18000.00,
            'is_available' => true,
        ]);

        $group = VariationGroup::create([
            'menu_id' => $this->menu->id,
            'name' => 'Ukuran',
            'is_required' => true,
            'allow_multiple' => false,
        ]);

        $this->optionLarge = VariationOption::create([
            'variation_group_id' => $group->id,
            'name' => 'Large',
            'extra_price' => 4000.00,
            'is_available' => true,
        ]);
    }

    public function test_customer_can_access_menu_with_valid_table_token(): void
    {
        $response = $this->get(route('customer.menu', [
            'table_number' => '01',
            'token' => 'valid-secret-token-01',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Meja 01');
        $response->assertSee('Kopi Susu Aren');
    }

    public function test_customer_cannot_access_menu_with_invalid_token(): void
    {
        $response = $this->get(route('customer.menu', [
            'table_number' => '01',
            'token' => 'wrong-token',
        ]));

        $response->assertStatus(200);
        $response->assertSee('QR Code Tidak Valid');
    }

    public function test_customer_can_checkout_and_server_calculates_price_correctly(): void
    {
        $payload = [
            'table_number' => '01',
            'table_token' => 'valid-secret-token-01',
            'customer_name' => 'Budi Santoso',
            'payment_method' => 'cash',
            'cart_items' => [
                [
                    'menu_id' => $this->menu->id,
                    'quantity' => 2,
                    'special_notes' => 'Less ice please',
                    'selected_options' => [$this->optionLarge->id],
                ]
            ]
        ];

        $response = $this->postJson(route('customer.checkout'), $payload);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Expected: (18000 + 4000) * 2 = 44000
        $this->assertDatabaseHas('orders', [
            'table_id' => $this->table->id,
            'customer_name' => 'Budi Santoso',
            'total_amount' => 44000.00,
            'order_status' => 'pending_confirmation',
        ]);

        $this->assertDatabaseHas('payments', [
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'amount' => 44000.00,
        ]);
    }

    public function test_cashier_must_not_send_unpaid_order_to_kitchen(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-TEST-001',
            'table_id' => $this->table->id,
            'customer_name' => 'Pelanggan Uji',
            'total_amount' => 22000.00,
            'order_status' => 'confirmed',
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'amount' => 22000.00,
        ]);

        // Attempt to send to kitchen while still unpaid
        $response = $this->actingAs($this->cashier)
            ->post(route('cashier.orders.send_kitchen', $order));

        $response->assertSessionHas('error');
        $this->assertEquals('confirmed', $order->fresh()->order_status);
    }

    public function test_cashier_can_verify_cash_and_forward_to_kitchen(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-TEST-002',
            'table_id' => $this->table->id,
            'customer_name' => 'Pelanggan Uji 2',
            'total_amount' => 22000.00,
            'order_status' => 'confirmed',
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'amount' => 22000.00,
        ]);

        // 1. Verify cash payment
        $responsePay = $this->actingAs($this->cashier)
            ->post(route('cashier.orders.pay_cash', $order));

        $responsePay->assertSessionHas('success');
        $this->assertEquals('paid', $order->fresh()->latestPayment->payment_status);

        // 2. Forward to kitchen now succeeds
        $responseKitchen = $this->actingAs($this->cashier)
            ->post(route('cashier.orders.send_kitchen', $order));

        $responseKitchen->assertSessionHas('success');
        $this->assertEquals('in_kitchen', $order->fresh()->order_status);
    }

    public function test_qris_simulation_allows_testing_sandbox_transitions(): void
    {
        $order = Order::create([
            'order_number' => 'ORD-TEST-QRIS',
            'table_id' => $this->table->id,
            'customer_name' => 'Pelanggan QRIS',
            'total_amount' => 25000.00,
            'order_status' => 'confirmed',
        ]);

        Payment::create([
            'order_id' => $order->id,
            'payment_method' => 'qris',
            'payment_status' => 'pending',
            'amount' => 25000.00,
        ]);

        // Simulate success
        $res = $this->post(route('customer.order.simulate_qris', $order->order_number), [
            'action' => 'success',
        ]);

        $res->assertSessionHas('status_message');
        $this->assertEquals('paid', $order->fresh()->latestPayment->payment_status);
    }
}
