<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Extra;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_order_with_extras(): void
    {
        // 1. Arrange: Setup store, category, product, and extras
        $store = Store::create([
            'name' => 'Açaí Express',
            'slug' => 'acai-express',
            'whatsapp_number' => '5585999998888',
            'address_city' => 'Fortaleza',
            'is_active' => true
        ]);

        $category = Category::create([
            'store_id' => $store->id,
            'name' => 'Açaí'
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Açaí 500ml',
            'price' => 15.00,
            'is_active' => true
        ]);

        $extra1 = Extra::create([
            'store_id' => $store->id,
            'name' => 'Leite Ninho',
            'price' => 2.00
        ]);

        $extra2 = Extra::create([
            'store_id' => $store->id,
            'name' => 'Nutella',
            'price' => 3.50
        ]);

        // Link the extras to the product
        $product->extras()->attach([$extra1->id, $extra2->id]);

        // Payload matching the request structure
        $payload = [
            'customer_name' => 'John Doe',
            'customer_whatsapp' => '5511999999999',
            'payment_method' => 'card',
            'delivery_address' => 'Rua das Flores, 123',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'extras' => [$extra1->id, $extra2->id]
                ]
            ]
        ];

        // 2. Act: Send POST request to the orders endpoint with frontend secret header
        $response = $this->withHeaders([
            'X-Frontend-Secret' => config('app.frontend_secret')
        ])->postJson("/api/tenant/{$store->slug}/orders", $payload);

        // 3. Assert: Verify HTTP response
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'message',
            'order_id',
            'total'
        ]);

        // Expect: (Product price 15.00 + Extra1 2.00 + Extra2 3.50) * Quantity 2 = 20.50 * 2 = 41.00
        $response->assertJson([
            'total' => 41.00
        ]);

        // Verify order in database
        $this->assertDatabaseHas('orders', [
            'store_id' => $store->id,
            'total_amount' => 41.00,
            'payment_method' => 'card',
            'status' => 'pending'
        ]);

        // Verify items in database
        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'product_name' => 'Açaí 500ml',
            'unit_price' => 15.00,
            'quantity' => 2,
            'total_price' => 41.00
        ]);

        // Verify the order and its items in DB including JSON casts
        $order = \App\Models\Order::first();
        $this->assertNotNull($order);
        
        // Assert delivery address has customer metadata
        $this->assertEquals('John Doe', $order->delivery_address['customer_name']);
        $this->assertEquals('5511999999999', $order->delivery_address['customer_whatsapp']);
        $this->assertEquals('Rua das Flores, 123', $order->delivery_address['address']);

        // Assert selected extras structure in order items
        $orderItem = $order->items->first();
        $this->assertNotNull($orderItem);
        $this->assertCount(2, $orderItem->selected_extras);
        
        $this->assertEquals($extra1->id, $orderItem->selected_extras[0]['id']);
        $this->assertEquals('Leite Ninho', $orderItem->selected_extras[0]['name']);
        $this->assertEquals(2.00, $orderItem->selected_extras[0]['price']);

        $this->assertEquals($extra2->id, $orderItem->selected_extras[1]['id']);
        $this->assertEquals('Nutella', $orderItem->selected_extras[1]['name']);
        $this->assertEquals(3.50, $orderItem->selected_extras[1]['price']);
    }
}
