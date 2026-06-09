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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Cliente

            // Status do Pedido: pending, preparing, out_for_delivery, delivered, canceled
            $table->string('status')->default('pending');

            $table->decimal('total_amount', 10, 2);
            $table->string('payment_method'); // stripe_card, pix, money_on_delivery

            // Guardamos o JSON do endereço para congelar a "foto" da entrega
            $table->json('delivery_address')->nullable();

            // Referência do pagamento na Stripe
            $table->string('stripe_payment_intent')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
