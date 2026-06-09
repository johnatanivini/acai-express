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
        Schema::table('users', function (Blueprint $table) {
            // Um usuário pode ser dono de uma loja (Store Admin) ou cliente de uma loja
            $table->foreignId('store_id')->nullable()->constrained()->cascadeOnDelete();

            // Controle de acesso: super_admin, store_admin, customer
            $table->string('role')->default('customer');

            // Login via telefone é comum em delivery
            $table->string('phone')->nullable()->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropColumn(['store_id', 'role', 'phone']);
        });
    }
};
