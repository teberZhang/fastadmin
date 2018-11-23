<?php

namespace app\common\library;
use app\common\service\Oss;

/**
 * Oss上传调度
 */
class OssClient
{
    protected $ossClient;

    public function __construct(Oss $ossClient)
    {
        $this->ossClient = $ossClient;
    }

    /**
     * 上传
     */
    public function upload($params = [])
    {
        return $this->ossClient->upload($params);
    }

    /**
     * 获取上传结果
     */
    public function getResponse()
    {
        return $this->ossClient->getResponse();
    }

    /**
     * 获取上传异常信息
     */
    public function getCatchE()
    {
        return $this->ossClient->getCatchE();
    }
}
