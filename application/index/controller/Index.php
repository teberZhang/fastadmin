<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use think\Db;
use app\common\library\Token;
use think\cache\driver\Redis;
use MongoDb\Driver\Manager;
use MongoDb\Collection;
use app\common\library\RabbitPublisher;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use think\Config;

class Index extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';
    protected $_redis;

    public function _initialize()
    {
        parent::_initialize();
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
        $this->_redis = $redis;
    }

    public function index()
    {
        $rabbitPublisher = new RabbitPublisher();
        $rabbitPublisher->workerMerchant();
    }

    public function news()
    {
        /*$config = [
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
        $redis->set("test","hello redis666");
        echo $redis->get("test");*/

        // 定义锁标识
        $key = 'mylock';

        // 获取锁
        $is_lock = $this->lock($key, 10);

        if($is_lock){
            echo 'get lock success<br>';
            echo 'do sth..<br>';
            sleep(5);
            echo 'success<br>';
            $this->unlock($key);

        // 获取锁失败
        }else{
            echo 'request too frequently<br>';
        }
    }

    /**
     * 获取锁
     * @param  String  $key    锁标识
     * @param  Int     $expire 锁过期时间
     * @return Boolean
     */
    public function lock($key, $expire=5){
        $is_lock = $this->_redis->setnx($key, time()+$expire);

        // 不能获取锁
        if(!$is_lock){

            // 判断锁是否过期
            $lock_time = $this->_redis->get($key);

            // 锁已过期，删除锁，重新获取
            if(time()>$lock_time){
                $this->unlock($key);
                $is_lock = $this->_redis->setnx($key, time()+$expire);
            }
        }

        return $is_lock? true : false;
    }

    /**
     * 释放锁
     * @param  String  $key 锁标识
     * @return Boolean
     */
    public function unlock($key){
        return $this->_redis->del($key);
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

    public function test()
    {
        $pid = pcntl_fork();
        return 'aaaa';
    }

}
