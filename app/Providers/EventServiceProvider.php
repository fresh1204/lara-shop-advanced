<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

use App\Events\OrderReviewed;
use App\Listeners\UpdateProductRating;

use App\Events\OrderPaid;
use App\Listeners\UpdateProductSoldCount;
use App\Listeners\SendOrderPaidMail;
use App\Listeners\UpdateCrowdfundingProductProgress;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    // 注册事件与监听器关系
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        OrderReviewed::class => [
            UpdateProductRating::class,
        ],

        OrderPaid::class => [
            UpdateProductSoldCount::class,
            SendOrderPaidMail::class,
            UpdateCrowdfundingProductProgress::class,
        ],

    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
