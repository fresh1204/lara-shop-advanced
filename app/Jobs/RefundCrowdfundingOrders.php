<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Order;
use App\Models\CrowdfundingProduct;
use App\Services\OrderService;

class RefundCrowdfundingOrders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $crowdfunding;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(CrowdfundingProduct $crowdfunding)
    {
        //
        $this->crowdfunding = $crowdfunding;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // 如果众筹的状态不是失败则不执行退款
        if($this->crowdfunding->status !== CrowdfundingProduct::STATUS_FAIL){
            return;
        }

        // 将定时任务中的众筹失败退款代码移到这里
        $orderService = app(OrderService::class);

        // 查询出所有参与了此众筹的订单
        Order::query()
            // 订单类型为众筹商品订单
            ->where('type',Order::TYPE_CROWDFUNDING)
            // 已支付的订单
            ->whereNotNull('paid_at')
            //->where('refund_status','pending')
            ->whereHas('items',function($query) {
                // 包含了当前商品
                $query->where('product_id',$this->crowdfunding->product_id);
            })
            ->get()
            ->each(function(Order $order) use ($orderService){
                // 调用退款逻辑
                $orderService->refundOrder($order);
            });
    }
}
