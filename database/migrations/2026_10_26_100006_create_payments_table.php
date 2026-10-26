<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->enum('payment_method', ['cash', 'qris']);
            $table->enum('payment_status', [
                'pending',  // Menunggu pembayaran
                'paid',     // Lunas
                'failed',   // Gagal
                'expired'   // Kedaluwarsa
            ])->default('pending');
            $table->decimal('amount', 12, 2);
            $table->string('transaction_id')->nullable()->index();
            $table->text('qr_string')->nullable();
            $table->string('snap_token')->nullable();
            $table->json('payment_payload')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('cashier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
