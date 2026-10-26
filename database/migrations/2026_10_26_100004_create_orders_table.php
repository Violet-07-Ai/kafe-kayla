<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('table_id')->constrained('tables')->cascadeOnDelete();
            $table->string('customer_name')->default('Tamu');
            $table->decimal('total_amount', 12, 2);
            $table->enum('order_status', [
                'pending_confirmation', // Menunggu konfirmasi kasir
                'confirmed',            // Dikonfirmasi kasir
                'in_kitchen',           // Diteruskan ke dapur
                'ready_to_serve',       // Siap disajikan
                'completed',            // Sudah disajikan / selesai
                'cancelled'             // Dibatalkan
            ])->default('pending_confirmation');
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('sent_to_kitchen_at')->nullable();
            $table->timestamp('ready_to_serve_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
