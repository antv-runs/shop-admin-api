<?php

namespace App\Contracts;

interface OrderServiceInterface
{
    /**
     * Create a new order
     *
     * @param array $data   // expects ['user_id' => ..., 'items'=>[['product_id'=>..., 'quantity'=>...], ...]]
     * @return \App\Models\Order
     */
    public function createOrder(array $data);

    /**
     * Get paginated orders belonging to a user
     */
    public function getOrdersForUser($userId, $perPage = 15);

    /**
     * Get a single order by id ensuring it belongs to given user
     */
    public function getOrderForUser($orderId, $userId);
}
