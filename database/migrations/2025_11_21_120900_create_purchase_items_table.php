<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 15, 3)->default(0);
            $table->string('unit')->default('pcs');
            $table->decimal('unit_cost', 15, 2)->default(0); // stored in TJS
            $table->decimal('total_cost', 15, 2)->default(0); // stored in TJS
            $table->enum('input_currency', ['TJS', 'USD'])->default('TJS');
            $table->decimal('input_unit_cost', 15, 2)->nullable();
            $table->decimal('exchange_rate', 12, 4)->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};
