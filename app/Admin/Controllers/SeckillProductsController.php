<?php
namespace App\Admin\Controllers;

use App\Models\Product;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use App\Models\ProductSku;

class SeckillProductsController extends CommonProductsController
{
	// 返回当前管理的商品类型
	public function getProductType()
	{
		return Product::TYPE_SECKILL;
	}

	// 定义列表应该展示哪些字段
	public  function customGrid(Grid $grid)
	{
		$grid->id('ID')->sortable();
		$grid->title('商品名称');
		$grid->on_sale('已上架')->display(function($value){
			return $value ? '是' : '否';
		});
		$grid->price('价格');
		$grid->column('seckill.start_at','开始时间');
		$grid->column('seckill.end_at','结束时间');
		$grid->sold_count('销量');
	}

	// 定义表单应该有哪些额外的字段
	public  function customForm(Form $form)
	{
		// 秒杀相关字段
		$form->datetime('seckill.start_at','秒杀开始时间')->rules('required|date');
		$form->datetime('seckill.end_at','秒杀结束时间')->rules('required|date');

		// 当商品表单保存完毕后触发redis存储删除库存量
		$form->saved(function(Form $form){
			$product = $form->model();
			// 商品重新加载秒杀
			$product->load(['seckill']);
			// 获取当前时间与秒杀结束时间的差值
			$diff = $product->seckill->end_at->getTimestamp() - time();
			// 遍历商品 SKU
			$product->skus->each(function(ProductSku $sku) use ($diff,$product){
				// 如果秒杀商品是上架并且尚未到结束时间
				if($product->on_sale && $diff > 0){
					// 将秒杀商品的库存存储到redis中，并设置过期时间为秒杀截止时间
					\Redis::setex('seckill_sku_'.$sku->id,$diff,$sku->stock);
				}else{
					// 否则将SKU的库存从redis中删除
					\Redis::del('seckill_sku_'.$sku->id);
				}
			});
		});
	}
}