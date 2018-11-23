<?php

namespace app\common\service;

/**
 * Oss文件上传
 */
interface Oss
{
    //上传
    public function upload();
    //获取上传结果
    public function getResponse();
    //获取上传异常信息
    public function getCatchE();
}
