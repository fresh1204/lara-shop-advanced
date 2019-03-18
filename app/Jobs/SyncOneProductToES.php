<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Product;

/**
 *
 * 同步变更数据到Elasticsearch (实现新增或修改商品时，能够同步到Elasticsearch)
 *
*/
class SyncOneProductToES implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $product;

    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //获取数据模型转为数组的数据
        $data = $this->product->toESArray();
        
        app('es')->index([
            'index' => 'products',
            'type' => '_doc',
            'id' => $data['id'],
            'body' => $data,
        ]);
    }
}
