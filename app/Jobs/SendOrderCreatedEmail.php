<?php

namespace App\Jobs;

use App\Mail\OrderCreated;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderCreatedEmail implements ShouldQueue
{
    use Dispatchable, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 30;
    public Order $order;

    public function __construct(Order $order)
    {
        // serialize order for the queue (the model will be re‑hydrated automatically)
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user = $this->order->user;

        if (! $user || ! $user->email) {
            Log::warning('Order email not sent: suer or email missing', [
                'order_id' => $this->order->id,
            ]);

            return;
        }

        Mail::to($user->email)
            ->send(new OrderCreated($this->order));

        Log::info('Order created email sent successfully', [
            'order_id' => $this->order->id,
            'user_id' => $user->id,
        ]);
    }

    /**
     * Define retry delays (in seconds) for each attempt.
     *
     * Attempt #1 retry after 10 seconds
     * Attempt #2 retry after 30 seconds
     * Attempt #3 retry after 60 seconds
     *
     * Purpose:
     * - Prevent immediate retry spam
     * - Give SMTP server time to recover
     * - Reduce risk of hitting rate limits
     */
    public function backoff()
    {
        return [10, 30, 60];
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Order created email failed permanently', [
            'order_id' => $this->order->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
