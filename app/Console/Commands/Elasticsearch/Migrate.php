<?php

namespace App\Console\Commands\Elasticsearch;

use Illuminate\Console\Command;

class Migrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'es:migrate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Elasticsearch 索引结构迁移';

    protected $es;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->es = app('es');

        //索引类数组，先留空
        $indices = [];
        // 遍历索引类数组
        foreach($indices as $indexClass){
            // 调用类数组的 getAliasName() 方法来获取索引别名
            $aliasName = $indexClass::getAliasName();
            $this->info('正在处理索引 '.$aliasName);

            // 通过 exists 方法判断这个别名是否存在
            if(!$this->es->indices()->exists(['index' => $aliasName])){
                $this->info('索引不存在，准备创建');
                $this->createIndex($aliasName,$indexClass);
                $this->info('创建成功，准备初始化数据');
                $indexClass::rebuild($aliasName);
                $this->info('操作成功');
                continue;
            }

            // 如果索引已经存在，那么尝试更新索引，如果更新失败会抛出异常
            try{
                $this->info('索引存在，准备更新');
                $this->updateIndex($aliasName,$indexClass);
            }catch(\Exception $e){
                 $this->warn('更新失败，准备重建');
                 $this->reCreateIndex($aliasName, $indexClass);
            }
            $this->info($aliasName.' 操作成功');
        }
    }

    // 创建新索引
    protected function createIndex($aliasName,$indexClass)
    {
        // 调用 create() 方法创建索引
        $this->es->indices()->create([
            // 第一个版本的索引名后缀为 _0
            'index' => $aliasName.'_0',
            'body' => [
                // 调用索引类的 getSettings() 方法获取索引设置
                'settings' => $indexClass::getSettings(),
                'mappings' => [
                    '_doc' => [
                        // 调用索引类的 getProperties() 方法获取索引字段
                        'properties' => $indexClass::getProperties(),
                    ],
                ],
                'aliases' => [
                    // 同时创建别名
                    $aliasName => new \stdClass(),
                ],
            ],
        ]);
    }

    // 更新已有的索引
    protected function updateIndex($aliasName,$indexClass)
    {

    }

    // 重建索引
    protected function reCreateIndex($aliasName,$indexClass)
    {

    }
}
