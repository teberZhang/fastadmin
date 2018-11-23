<?php

namespace app\common\library;
use app\common\service\Push;

/**
 * 推送
 */
class PushSender
{
    protected $pushSender;

    public function __construct(Push $pushSender)
    {
        $this->pushSender = $pushSender;
    }

    /**
     * 指定个人或群体=推送消息
     */
    public function push($params = [])
    {
        return $this->pushSender->push($params);
    }

    /**
     * 推送消息(全推送)
     */
    public function pushall($params = [])
    {
        return $this->pushSender->pushall($params);
    }

    /**
     * 获取推送结果
     */
    public function getResponse()
    {
        return $this->pushSender->getResponse();
    }

    /**
     * 获取推送异常信息
     */
    public function getCatchE()
    {
        return $this->pushSender->getCatchE();
    }
}
