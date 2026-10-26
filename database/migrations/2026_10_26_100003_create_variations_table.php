<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variation_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->string('name'); // e.g. "Ukuran", "Level Gula", "Level Es"
            $table->boolean('is_required')->default(true);
            $table->boolean('allow_multiple')->default(false);
            $table->timestamps();
        });

        Schema::create('variation_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variation_group_id')->constrained('variation_groups')->cascadeOnDelete();
            $table->string('name'); // e.g. "Regular", "Large", "Less Sugar"
            $table->decimal('extra_price', 12, 2)->default(0.00);
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variation_options');
        Schema::dropIfExists('variation_groups');
    }
};
