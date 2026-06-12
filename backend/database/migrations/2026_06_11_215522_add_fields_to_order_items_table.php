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
        Schema::table('order_items', function (Blueprint $table) {
            // Adiciona o preço histórico da compra
            $table->decimal('price_at_purchase', 10, 2)->after('quantity');

            // Adiciona a coluna de adicionais/extras salvos em JSON (opcional, mas altamente recomendado)
            $table->json('extras')->nullable()->after('price_at_purchase');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['price_at_purchase', 'extras']);
        });
    }
};
