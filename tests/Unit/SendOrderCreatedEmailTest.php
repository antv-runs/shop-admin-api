<?php

namespace Tests\Unit;

use App\Jobs\SendOrderCreatedEmail;
use App\Mail\OrderCreated;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendOrderCreatedEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_handle_sends_email_to_user()
    {
        Mail::fake();

        // build order with a user and one item
        $order = Order::factory()->create();
        $product = Product::factory()->create(['price' => 99.99]);

        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => $product->price,
            'total' => $product->price,
        ]);

        $job = new SendOrderCreatedEmail($order);
        $job->handle();

        Mail::assertSent(OrderCreated::class, function ($mail) use ($order) {
            return $mail->hasTo($order->user->email) && $mail->order->id === $order->id;
        });
    }
}
