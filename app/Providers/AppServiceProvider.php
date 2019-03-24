<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Monolog\Logger;
use Yansongda\Pay\Pay;
use Carbon\Carbon;
use Elasticsearch\ClientBuilder as ESClientBuilder;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //当 Laravel 渲染 products.index 和 products.show 模板时，就会使用 CategoryTreeComposer 这个来注入类目树变量
        // 同时 Laravel 还支持通配符，例如 products.* 即代表当渲染 products 目录下的模板时都执行这个 ViewComposer
        
        \View::composer(['products.index','products.show'],\App\Http\ViewComposers\CategoryTreeComposer::class);

        // 对于需要全局设置的操作，通常放在 AppServiceProvider 的 boot() 方法中来执行
        Carbon::setLocale('zh');

        //只在本地开发环境中启用 SQL 日志
        if(app()->environment('local')){
            \DB::listen(function($query){
                \Log::info(Str::replaceArray('?',$query->bindings,$query->sql));
            });
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // 往服务容器中注入一个名为 alipay 的单例对象
        $this->app->singleton('alipay',function(){
            $config = config('pay.alipay');
            //$config['notify_url'] = route('payment.alipay.notify');  //代表服务器端回调地址
            //$config['notify_url'] = 'http://requestbin.fullcontact.com/17u8c971';
            $config['notify_url'] = ngrok_url('payment.alipay.notify'); //代表服务器端回调地址

            $config['return_url'] = route('payment.alipay.return');  //代表前端回调地址

            // 判断当前项目运行环境是否为线上环境
            if(app()->environment() !== 'production'){
                $config['mode']         = 'dev';
                $config['log']['level'] = Logger::DEBUG;
            }else{
                $config['log']['level'] = Logger::WARNING;
            }

            // 调用 Yansongda\Pay 来创建一个支付宝支付对象
            return Pay::alipay($config);
        });

        // 往服务容器中注入一个名为 wechat_pay 的单例对象
        $this->app->singleton('wechat_pay',function(){
            $config = config('pay.wechat');
            if(app()->environment() !== 'production'){
                $config['log']['level'] = Logger::DEBUG;
            }else{
                $config['log']['level'] = Logger::WARNING;
            }

            // 调用 Yansongda\Pay 来创建一个微信支付对象
            return Pay::wechat($config);

        });

        // 初始化 Elasticsearch 对象，并注入到 Laravel 容器中
        // 注册一个名为 es 的单例
        $this->app->singleton('es',function(){
            // 从配置文件读取 Elasticsearch 服务器列表
            $builder = ESClientBuilder::create()->setHosts(config('database.elasticsearch.hosts'));

            // 如果是开发环境
            if(app()->environment() === 'local'){
                // 配置日志，Elasticsearch 的请求和返回数据将打印到日志文件中，方便我们调试
                $builder->setLogger(app('log')->driver());
            }

            return $builder->build();
        });
    }
}
 