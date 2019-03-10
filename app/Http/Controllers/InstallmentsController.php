<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Installment;

class InstallmentsController extends Controller
{
    //分期付款列表
    public function index(Request $request)
    {
    	$installments = Installment::query()
    		->where('user_id',$request->user()->id)
    		->paginate(10);

    	return view('installments.index',['installments' => $installments]);
    }

    //分期详情
    public function show(Installment $installment)
    {
    	//权限检查
    	$this->authorize('own',$installment);

    	// 取出当前分期付款的所有的还款计划，并按还款顺序排序
    	$items = $installment->items()->orderBy('sequence')->get();

    	return view('installments.show',[
    		'installment' => $installment,
    		'items' => $items,
    		// 下一个未完成还款的还款计划
    		'nextItem' => $items->where('paid_at',null)->first(),
    	]);
    }
}
