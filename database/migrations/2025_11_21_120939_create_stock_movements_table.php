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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->nullableMorphs('referenceable');
            $table->enum('type', ['purchase', 'sale', 'correction', 'adjustment', 'return']);
            $table->decimal('quantity', 15, 3);
            $table->decimal('stock_after', 15, 3)->nullable();
            $table->text('comment')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
