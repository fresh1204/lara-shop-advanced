<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// 分期信息类
class Installment extends Model
{
    //
	const STATUS_PENDING ='pending';
	const STATUS_REPAYING = 'repaying';
	const STATUS_FINISHED = 'finished';

	public static $statusMap = [
		self::STATUS_PENDING => '未执行',
		self::STATUS_REPAYING => '还款中',
		self::STATUS_FINISHED => '已完成',
	];

	protected $fillable = ['no','total_amount','count','fee_rate','fine_rate','status'];

	protected static function boot()
	{
		parent::boot();

		// 监听模型创建事件，在写入数据库之前触发
		static::creating(function($model){
			// 如果模型的 no 字段为空
			if(!$model->no){
				// 调用 findAvailableNo 生成分期流水号
				$model->no = static::findAvailableNo();

				// 如果生成失败，则终止创建订单
				if(!$model->no){
					return false;
				}
			}			

		});
	}

	// 关联用户模型
	public function user()
	{
		return $this->belongsTo(User::class);
	}

	// 关联订单模型
	public function order()
	{
		return $this->belongsTo(Order::class);
	}

	//关联还款信息项模型
	public function items()
	{
		$this->hasMany(InstallmentItem::class);
	}

	//生成分期流水号
	public static function findAvailableNo()
	{
		// 分期流水号前缀
		$prefix = date('YmdHis');
		for($i=0;$i<10;$i++){
			// 随机生成 6 位的数字
			$no = $prefix.str_pad(random_int(0,999999),6,'0',STR_PAD_LEFT);
			// 判断是否已经存在
			if(!static::query()->where('no',$no)->exists()){
				return $no;
			}
		}
	}
}
