<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use think\Db;
use app\common\library\Token;
use think\cache\driver\Redis;
use MongoDb\Driver\Manager;
use MongoDb\Collection;
use app\common\library\RabbitPublisher;

class Index extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    public function _initialize()
    {
        parent::_initialize();
    }

    public function index()
    {
        return $this->view->fetch();
    }

    public function news()
    {
        $config = [
            'host' => '127.0.0.1',
            'port' => 6379,
            'password' => 'xiaoyong666',
            'select' => 0,
            'timeout' => 0,
            'expire' => 0,
            'persistent' => false,
            'prefix' => '',
        ];
        $redis = new Redis($config);
        $redis->set("test","hello redis");
        echo $redis->get("test");
    }

    //生产者
    public function send()
    {
        $merchantData = [
            'id' => rand(10,10000),
        ];
        //入MQ
        $rabbitPublisher = new RabbitPublisher();
        $rabbitPublisher->taskMerchant($merchantData);
    }

    //消费者
    public function receive()
    {
        //Mq消费
        $rabbitPublisher = new RabbitPublisher();
        $rabbitPublisher->workerMerchant();
    }

}
