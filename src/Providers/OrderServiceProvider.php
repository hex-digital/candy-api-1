<?php

namespace GetCandy\Api\Providers;

use Illuminate\Support\ServiceProvider;
use GetCandy\Api\Core\Orders\OrderCriteria;
use GetCandy\Api\Core\Orders\Services\OrderService;
use GetCandy\Api\Core\Orders\Interfaces\OrderServiceInterface;
use GetCandy\Api\Core\Orders\Interfaces\OrderCriteriaInterface;

class OrderServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(OrderCriteriaInterface::class, function ($app) {
            return $app->make(OrderCriteria::class);
        });

        $this->app->bind(OrderServiceInterface::class, function ($app) {
            return $app->make(OrderService::class);
        });
    }
}
