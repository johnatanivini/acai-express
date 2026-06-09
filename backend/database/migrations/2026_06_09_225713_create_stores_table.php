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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // Ex: acaidaesquina

            // Identidade Visual
            $table->string('logo_url')->nullable();
            $table->string('primary_color')->default('#4C1D95'); // Roxo escuro padrão

            // Contato e Endereço
            $table->string('whatsapp_number');
            $table->string('address_street')->nullable();
            $table->string('address_number')->nullable();
            $table->string('address_neighborhood')->nullable();
            $table->string('address_city')->nullable();

            // Integração Stripe Connect e Assinatura (O modelo de R$ 99/mês)
            $table->string('stripe_account_id')->nullable(); // Conta da loja para receber do cliente
            $table->string('stripe_subscription_id')->nullable(); // Assinatura SaaS da loja com você
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
