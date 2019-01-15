<?php

namespace Tests\Unit\Orders\Services;

use Tests\TestCase;
use Illuminate\Support\Facades\Event;
use GetCandy\Api\Core\Orders\Services\OrderService;
use GetCandy\Api\Core\Orders\Events\OrderSavedEvent;
use GetCandy\Api\Core\Baskets\Services\BasketService;
use GetCandy\Api\Core\Products\Models\ProductVariant;
use GetCandy\Api\Core\Orders\Events\OrderProcessedEvent;
use GetCandy\Api\Core\Orders\Interfaces\OrderServiceInterface;

/**
 * @group orders
 */
class OrderServiceTest extends TestCase
{
    public function test_it_can_be_injected()
    {
        $service = $this->app->make(OrderServiceInterface::class);
        $this->assertInstanceOf(OrderService::class, $service);
    }

    public function test_can_create_order_from_basket()
    {
        Event::fake();

        $baskets = $this->app->make(BasketService::class);
        $service = $this->app->make(OrderServiceInterface::class);

        $variant = ProductVariant::first();

        $basket = $baskets->store([
            'variants' => [
                ['id' => $variant->encodedId(), 'quantity' => 1],
            ],
        ]);

        $order = $service->store($basket->encodedId());

        Event::assertDispatched(OrderSavedEvent::class, function ($e) use ($order) {
            return $e->order->id === $order->id;
        });

        $order = $service->recalculate($order);

        $this->assertEquals($basket->sub_total, $order->sub_total);
        $this->assertEquals($basket->total_cost, $order->order_total);
        $this->assertEquals($basket->total_tax, $order->tax_total);
        $this->assertEquals($basket->discount_total, $order->discount_total);
    }
}