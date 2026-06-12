<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->constrained()->onDelete('cascade'); // Vincula a um pedido real

            // Notas de 1 a 5 para cada critério
            $table->tinyInteger('rating_quality');
            $table->tinyInteger('rating_delivery');
            $table->tinyInteger('rating_packaging');
            $table->tinyInteger('rating_service');
            $table->tinyInteger('rating_value');

            // Nota final calculada (com precisão decimal, ex: 4.75)
            $table->decimal('final_score', 3, 2);

            $table->text('comment')->nullable(); // Comentário opcional do cliente
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
