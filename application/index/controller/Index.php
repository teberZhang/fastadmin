<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\library\Token;
use think\cache\driver\Redis;
use MongoDb\Driver\Manager;
use MongoDb\Collection;

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

    public function mongo()
    {
        //
    }

}
