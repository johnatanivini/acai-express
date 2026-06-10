<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Extra;
use App\Models\Product;
use App\Models\User;
use App\Models\Store as ModelsStore;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // 1. Limpa o banco para não dar conflito (opcional, mas recomendado no dev)
        DB::table('product_extra')->delete();
        DB::table('extras')->delete();
        DB::table('products')->delete();
        DB::table('categories')->delete();
        DB::table('stores')->delete();
        DB::table('users')->delete();

        // 2. Criar uma Loja Demo
        $store = ModelsStore::create([
            'name' => 'Açaí Express',
            'slug' => 'acai-express',
            'whatsapp_number' => '5585999998888',
            'address_city' => 'Fortaleza',
        ]);

        // 3. Criar Admin da Loja
        User::create([
            'name' => 'Admin Açaí',
            'email' => 'admin@acaiexpress.com',
            'password' => Hash::make('password'),
            'role' => 'store_admin',
            'store_id' => $store->id,
        ]);

        // 4. Criar Categorias
        $catAcai = Category::create(['store_id' => $store->id, 'name' => 'Açaí']);

        // 5. Criar Produtos
        $p1 = Product::create([
            'category_id' => $catAcai->id,
            'name' => 'Açaí 500ml',
            'price' => 15.00,
        ]);

        // 6. Criar Extras (Complementos)
        $e1 = Extra::create(['store_id' => $store->id, 'name' => 'Leite Ninho', 'price' => 2.00]);
        $e2 = Extra::create(['store_id' => $store->id, 'name' => 'Nutella', 'price' => 3.50]);

        // 7. Vincular Extras aos Produtos (Pivô)
        $p1->extras()->attach([$e1->id, $e2->id]);
    }
}
