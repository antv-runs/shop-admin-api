<?php

namespace App\Services;

use App\Contracts\OrderServiceInterface;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderService implements OrderServiceInterface
{
    /**
     * Create a new order and its items inside a transaction
     *
     * The $data array should contain:
     *  - user_id
     *     - items: array of ['product_id'=>int, 'quantity'=>int]
     *
     * Prices are looked up from the products table to prevent trusting FE data.
     */
    public function createOrder(array $data)
    {
        return DB::transaction(function () use ($data) {
            $userId = $data['user_id'];
            $items = $data['items'] ?? [];

            if (empty($items)) {
                throw new \InvalidArgumentException('Order must contain at least one item.');
            }

            $totalAmount = 0;
            $orderItemsPayload = [];

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $quantity = max(1, (int) $item['quantity']);
                $price = $product->price;
                $lineTotal = bcmul($price, $quantity, 2);

                $totalAmount = bcadd($totalAmount, $lineTotal, 2);

                $orderItemsPayload[] = [
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => $lineTotal,
                ];
            }

            $order = Order::create([
                'user_id' => $userId,
                'total_amount' => $totalAmount,
            ]);

            // create items using relationship for convenience
            foreach ($orderItemsPayload as $payload) {
                $order->items()->create($payload);
            }

            // load items relationship before returning
            $order->load('items.product');

            return $order;
        });
    }

    /**
     * Get orders belonging to a user (paginated)
     */
    public function getOrdersForUser($userId, $perPage = 15)
    {
        return Order::with('items.product')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Retrieve a single order and ensure the given user owns it.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getOrderForUser($orderId, $userId)
    {
        return Order::with('items.product')
            ->where('id', $orderId)
            ->where('user_id', $userId)
            ->firstOrFail();
    }
}
