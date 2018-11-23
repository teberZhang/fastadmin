<?php

namespace app\common\service;

/**
 * 推送基类
 */
interface Push
{
    //指定个人或群体=推送消息
    public function push();
    //推送消息(全推送)
    public function pushall();
    //获取推送结果
    public function getResponse();
    //获取推送异常信息
    public function getCatchE();
}
