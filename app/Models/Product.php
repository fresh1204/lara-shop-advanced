<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    //商品类型
    const TYPE_NORMAL = 'normal';
    const TYPE_CROWDFUNDING = 'crowdfunding';
    public static $typeMap = [
        self::TYPE_NORMAL  => '普通商品',
        self::TYPE_CROWDFUNDING => '众筹商品',
    ];

    protected $fillable = [
    	'title','description','image','on_sale',
    	'rating','sold_count','review_count','price',
        'type','long_title',
    ];

    protected $casts = [
    	'on_sale' => 'boolean',
    ];

    //与商品SKU关联
    public function skus()
    {
    	return $this->hasMany(ProductSku::class);
    }

    //获取图片的绝对路径
    public function getImageUrlAttribute()
    {

    	//如果 image 字段本身就已经是完整的 url 就直接返回
    	if(Str::startsWith($this->attributes['image'],['http://','https://'])){
  
    		return $this->attributes['image'];
    	}

    	return \Storage::disk('public')->url($this->attributes['image']);
    }

    // 与类目模型的关联关系
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    //与众筹商品模型的关联关系
    public function crowdfunding()
    {
        return $this->hasOne(crowdfundingProduct::class);
    }

    // 关联商品属性
    public function properties()
    {
        return $this->hasMany(ProductProperty::class);
    }

    // 定义一个访问器
    public function getGroupedPropertiesAttribute()
    {
        return $this->properties
            
            // 按照属性名聚合，返回的集合的 key 是属性名，value 是包含该属性名的所有属性集合
            ->groupBy('name')

            ->map(function($properties){
                // 使用 map 方法将属性集合变为属性值集合
                return $properties->pluck('value')->all();
            });
    }

    // 将商品模型转为数组
    public function toESArray()
    {
        // 只取出需要的字段
        $arr = array_only($this->toArray(),[
            'id',
            'type',
            'title',
            'category_id',
            'long_title',
            'on_sale',
            'rating',
            'sold_count',
            'review_count',
            'price',
        ]);

        // 如果商品有类目，则 category 字段为类目名数组，否则为空字符串
        $arr['category'] = $this->category ? explode(' - ',$this->category->full_name) : '';
        // 类目的 path 字段
        $arr['category_path'] = $this->category ? $this->category->path : '';
        // strip_tags 函数可以将 html 标签去除
        $arr['description'] = strip_tags($this->description);
        // 只取出需要的 SKU 字段
        $arr['skus'] = $this->skus->map(function (ProductSku $sku){
            return array_only($sku->toArray(),['title','description','price']);
        });
        // 只取出需要的商品属性字段
        $arr['properties'] = $this->properties->map(function(ProductProperty $property){
            //return array_only($property->toArray(),['name','value']);
            
            // 对应地增加一个 search_value 字段，用符号 : 将属性名和属性值拼接起来
            return array_merge(array_only($property->toArray(),['name','value']),[
                'search_value' => $property->name.':'.$property->value,
            ]);
        });

        return $arr;
    }
}
