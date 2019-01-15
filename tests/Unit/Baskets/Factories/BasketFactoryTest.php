<?php

namespace Tests\Unit\Orders\Services;

use Tests\TestCase;
use Illuminate\Support\Facades\Event;
use GetCandy\Api\Core\Baskets\Models\Basket;
use GetCandy\Api\Core\Baskets\Models\BasketLine;
use GetCandy\Api\Core\Baskets\Services\BasketService;
use GetCandy\Api\Core\Products\Models\ProductVariant;
use GetCandy\Api\Core\Baskets\Factories\BasketFactory;
use GetCandy\Api\Core\Baskets\Interfaces\BasketFactoryInterface;
use GetCandy\Api\Core\Products\Factories\ProductVariantFactory;

/**
 * @group current
 */
class BasketFactoryTest extends TestCase
{

    public function test_instance_can_be_swapped()
    {
        $current = $this->app->make(BasketFactoryInterface::class);

        $this->assertInstanceOf(BasketFactory::class, $current);
        $this->app->instance(BasketFactoryInterface::class, new \stdClass);
        $swapped = $this->app->make(BasketFactoryInterface::class);

        $this->assertInstanceOf(\stdClass::class, $swapped);
    }

    public function test_can_be_initialised_with_a_basket()
    {
        $basket = $this->getinitalbasket();

        $factory = $this->app->make(BasketFactory::class);

        $basket = $factory->init($basket)->get();

        $this->assertInstanceOf(Basket::class, $basket);
    }

    public function test_basket_gets_hydrated()
    {
        $basket = $this->getinitalbasket();

        $factory = $this->app->make(BasketFactory::class);

        $subTotal = 0;
        $taxTotal = 0;

        $variantFactory = $this->app->make(ProductVariantFactory::class);

        // Work out what we think it should be
        foreach ($basket->lines as $line) {
            $variant = $variantFactory->init($line->variant)->get();
            $subTotal += $variant->unit_cost;
            $taxTotal += $variant->unit_tax;
        }

        $total = $subTotal + $taxTotal;

        $this->assertEquals($basket->sub_total, 0);
        $this->assertEquals($basket->total_tax, 0);
        $this->assertEquals($basket->total_cost, 0);

        $factory->init($basket)->get();

        $this->assertEquals($basket->sub_total, $subTotal);
        $this->assertEquals($basket->total_tax, $taxTotal);
        $this->assertEquals($basket->total_cost, $total);

    }

    private function getinitalbasket($user = null)
    {
        $variant = ProductVariant::first();
        $basket = Basket::forceCreate([
            'currency' => 'GBP',
        ]);

        if ($user) {
            $basket->user_id = $user->id;
            $basket->save();
        }

        BasketLine::forceCreate([
            'product_variant_id' => $variant->id,
            'basket_id' => $basket->id,
            'quantity' => 1,
            'total' => $variant->price,
        ]);
        return $basket;
    }
}