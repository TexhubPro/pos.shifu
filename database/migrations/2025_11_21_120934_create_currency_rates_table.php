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
        Schema::create('currency_rates', function (Blueprint $table) {
            $table->id();
            $table->string('currency_code', 3)->default('USD');
            $table->decimal('rate', 12, 4); // how many TJS per unit
            $table->timestamp('effective_at')->useCurrent();
            $table->boolean('is_active')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['currency_code', 'effective_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_rates');
    }
};
