<?php

namespace Tests\Feature;

use App\Jobs\SendOrderCreatedEmail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_creation_dispatches_email_job()
    {
        Queue::fake();

        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 10]);

        $this->actingAs($user);

        $payload = [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ];

        $response = $this->postJson('/api/orders', $payload);
        $response->assertStatus(201);

        Queue::assertPushed(SendOrderCreatedEmail::class, function ($job) use ($user) {
            return $job->order->user_id === $user->id
                && $job->afterCommit === true;
        });
    }
}
