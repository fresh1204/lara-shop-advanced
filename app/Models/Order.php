<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class Order extends Model
{
    const REFUND_STATUS_PENDING = 'pending';
    const REFUND_STATUS_APPLIED = 'applied';
    const REFUND_STATUS_PROCESSING = 'processing';
    const REFUND_STATUS_SUCCESS = 'success';
    const REFUND_STATUS_FAILED = 'failed';

    const SHIP_STATUS_PENDING = 'pending';
    const SHIP_STATUS_DELIVERED = 'delivered';
    const SHIP_STATUS_RECEIVED = 'received';

    // 商品类型
    const TYPE_NORMAL = 'normal';
    const TYPE_CROWDFUNDING = 'crowdfunding';
    const TYPE_SECKILL = 'seckill';

    public static $refundStatusMap = [
    	self::REFUND_STATUS_PENDING    => '未退款',
        self::REFUND_STATUS_APPLIED    => '已申请退款',
        self::REFUND_STATUS_PROCESSING => '退款中',
        self::REFUND_STATUS_SUCCESS    => '退款成功',
        self::REFUND_STATUS_FAILED     => '退款失败',
    ];

    public static $shipStatusMap = [
    	self::SHIP_STATUS_PENDING   => '未发货',
        self::SHIP_STATUS_DELIVERED => '已发货',
        self::SHIP_STATUS_RECEIVED  => '已收货',
    ];

    // 订单商品类型
    public static $typeMap = [
        self::TYPE_NORMAL => '普通商品订单',
        self::TYPE_CROWDFUNDING => '众筹商品订单',
        self::TYPE_SECKILL => '秒杀商品订单',
    ];

    protected $fillable = [
    	'no',
        'address',
        'total_amount',
        'remark',
        'paid_at',
        'payment_method',
        'payment_no',
        'refund_status',
        'refund_no',
        'closed',
        'reviewed',
        'ship_status',
        'ship_data',
        'extra',
        'type',
    ];

    protected $casts = [
    	'closed'    => 'boolean',
        'reviewed'  => 'boolean',
        'address'   => 'json',
        'ship_data' => 'json',
        'extra'     => 'json',
    ];

    protected $dates = [
    	'paid_at',
    ];

    protected static function boot()
    {
    	parent::boot();

    	// 监听模型创建事件，在写入数据库之前触发,用于自动生成订单的流水号
    	static::creating(function($mode){
    		
    		// 如果模型的 订单流水号no 字段为空
    		if(!$mode->no){
    			// 调用 findAvailableNo 生成订单流水号
    			$mode->no = static::findAvailableNo();
    			// 如果生成失败，则终止创建订单
    			if(!$mode->no){
    				return false;
    			}
    		}

    	});
    }

    //关联用户模型
	public function user()
	{
		return $this->belongsTo(User::class);
	}

	//关联订单项目模型
	public function items()
	{
		return $this->hasMany(OrderItem::class);
	}

    // 生成订单号
    public static function findAvailableNo()
    {
    	//订单流水号前缀
    	$prefix = date('YmdHis');
    	for($i=0;$i<10;$i++){
    		// 随机生成 6 位的数字
    		$no = $prefix.str_pad(random_int(0,999999), 6,'0',STR_PAD_LEFT);
    		// 判断是否已经存在
    		if(!static::query()->where('no',$no)->exists()){
    			return $no;
    		}
    	}
    	\Log::warning('find order no failed');

    	return false;
    }

    // 生成退款订单号
    public static function getAvailableRefundNo()
    {
        do{
            // Uuid类可以用来生成大概率不重复的字符串
            $no = Uuid::uuid4()->getHex();

            // 为了避免重复,我们在生成之后在数据库中查询看看是否已经存在相同的退款订单号
        }while(self::query()->where('refund_no',$no)->exists());

        return $no;
    }

    // 关联优惠券模型CouponCode
    public function couponCode()
    {
        return $this->belongsTo(CouponCode::class);
    }
}
