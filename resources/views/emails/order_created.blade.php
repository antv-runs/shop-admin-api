@component('mail::message')
# Order #{{ $order->id }} Confirmation

Thank you for your purchase! Here are the items you ordered:

@foreach($order->items as $item)
- {{ $item->product->name }} x {{ $item->quantity }} — {{ number_format($item->total, 0, ',', '.') }} ₫
@endforeach

**Total amount:** {{ number_format($order->total_amount, 0, ',', '.') }} ₫

Thanks for shopping with us!

Regards,<br>
{{ config('app.name') }}
@endcomponent
