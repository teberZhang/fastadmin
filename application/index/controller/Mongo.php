<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use MongoDb\Driver\Manager;
use MongoDb\Collection;

class Mongo extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';
    protected $mongoManager;
    protected $mongoCollection;

    public function _initialize()
    {
        parent::_initialize();
        $this->mongoManager = new Manager($this->getUri());
        $this->mongoCollection = new Collection($this->mongoManager, "mldn","dept");
    }

    public function test() {
        // 读取一条数据
        $data = $this->mongoCollection->findOne();
        print_r($data);
    }

    protected function getUri()
    {
        return getenv('MONGODB_URI') ?: 'mongodb://127.0.0.1:27017';
    }

}
