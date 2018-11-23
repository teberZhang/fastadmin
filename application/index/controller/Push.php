<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\library\PushSender;
use app\common\service\JPush;

class Push extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';

    public function _initialize()
    {
        parent::_initialize();
    }

    public function push()
    {
        $pushSender = new PushSender(new JPush());
        $id = 100;
        $content = 'hello every one';
        $title = 'hi';
        $params = [
            'platform'=>'all',
            'type' => 'jump',
            'alert' => json_encode(['module'=>'newsflash','data'=>['type'=>'jump','id'=>$id]]), //跳转类json
            'notification' => $content,
            'android_title' => $title,
            'notification_extras' => ['module'=>'newsflash','data'=>['type'=>'jump','id'=>$id]], //安卓用此跳转
        ];
        $result = $pushSender->pushall($params);
        println($result);
    }

}
