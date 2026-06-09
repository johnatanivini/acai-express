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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();

            // Congelamos o nome e o preço unitário
            $table->string('product_name');
            $table->decimal('unit_price', 10, 2);
            $table->integer('quantity');

            // Congelamos os complementos escolhidos nesta array JSON 
            // Ex: [{"name": "Nutella", "price": 3.00}, {"name": "Leite Ninho", "price": 0}]
            $table->json('selected_extras')->nullable();

            $table->decimal('total_price', 10, 2); // (Unitário * Quantidade) + Extras
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
