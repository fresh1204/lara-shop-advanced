<?php

namespace App\Console\Commands\Cron;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\CrowdfundingProduct;
use Carbon\Carbon;
use App\Services\OrderService;
use App\Jobs\RefundCrowdfundingOrders;

class FinishCrowdfunding extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:finish-crowdfunding';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '结束众筹';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        CrowdfundingProduct::query()
            // 众筹结束时间早于当前时间
            ->where('end_at','<=',Carbon::now())
            // 众筹状态为众筹中
            ->where('status',CrowdfundingProduct::STATUS_FUNDING)
            ->get()
            ->each(function(CrowdfundingProduct $crowdfunding){
                // 如果众筹目标金额大于实际众筹金额
                if($crowdfunding->target_amount > $crowdfunding->total_amount){
                    // 调用众筹失败逻辑
                    $this->crowdfundingFailed($crowdfunding);
                }else{
                    // 否则调用众筹成功逻辑
                    $this->crowdfundingSucceed($crowdfunding);
                }
            });

    }

    //众筹成功
    protected function crowdfundingSucceed(CrowdfundingProduct $crowdfunding)
    {
        // 只需将众筹状态改为众筹成功即可
        $crowdfunding->update([
            'status' => CrowdfundingProduct::STATUS_SUCCESS,
        ]);
    }

    //众筹失败
    protected function crowdfundingFailed(CrowdfundingProduct $crowdfunding)
    {
        // 将众筹状态改为众筹失败
        $dd = $crowdfunding->update([
            'status' => CrowdfundingProduct::STATUS_FAIL,
        ]);
        
        /*
         * 众筹失败退款部分涉及到了与外部 API 的交互，是属于长耗时的操作,
         * 可以让这些任务分散到不同的队列处理服务器上，减轻定时任务服务器压力,
         * 因此我们可以把这部分的逻辑抽取成一个异步任务
        */
        //把定时任务中的失败退款逻辑改为触发这个异步任务
        dispatch(new RefundCrowdfundingOrders($crowdfunding));

        /*
        // 获取订单服务实例
        $orderService = app(OrderService::class);

        // 查询出所有参与了此众筹的订单
        Order::query()
            // 订单类型为众筹商品订单
            ->where('type',Order::TYPE_CROWDFUNDING)
            // 已支付的订单
            ->whereNotNull('paid_at')
            ->whereHas('items',function($query) use ($crowdfunding) {
                // 包含了当前商品
                $query->where('product_id',$crowdfunding->product_id);
            })
            ->get()
            ->each(function(Order $order) use ($orderService){
                // 调用退款逻辑
                $OrderService->refundOrder($order);
            });
        */
    }
}
