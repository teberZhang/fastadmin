<?php

namespace app\common\service;

use think\facade\Config;

/**
 * aliyunOss文件上传
 */
class AliyunOss implements Oss
{
    /**
     * 上传结果集
     * @var array
     */
    protected $response = [];

    /**
     * 上传失败异常信息
     * @var string
     */
    protected $catch_e = null;

    /**
     * aliyunoss上传
     * @params $params上传信息
     * @params $object文件名称
     * @params $filePath文件本地地址
     * @return array
     */
    public function upload($params = ['object'=>'','filePath'=>''],$type='file')
    {
        if(empty($params)){
            return ['code'=>0,'msg'=>'Invalid parameters'];
        }
        $configOss = ALIYUNOSS_CODE == 1 ? Config::get("oss.aliyunossMaster") : Config::get("oss.aliyunossDev");
        // 阿里云主账号AccessKey拥有所有API的访问权限，风险很高。强烈建议您创建并使用RAM账号进行API访问或日常运维，请登录 https://ram.console.aliyun.com 创建RAM账号。
        $accessKeyId = $configOss['AccessKeyId'];
        $accessKeySecret = $configOss['AccessKeySecret'];
        // Endpoint以杭州为例，其它Region请按实际情况填写。
        $endpoint = "http://".$configOss['endpoint'];
        // 存储空间名称
        $bucket= $configOss['bucket'];
        //校验是否是允许上传的格式
        if(!isset($type) || !in_array($type,$configOss['allowtype'])){
            return ['code'=>0,'msg'=>'Please select the correct upload type'];
        }
        switch($type){
            case 'file' : //文件类型
                if(!isset($params['object']) || !$params['object']){
                    return ['code'=>0,'msg'=>'Parameter object can not be empty'];
                }
                if(!isset($params['filePath']) || !$params['filePath']){
                    return ['code'=>0,'msg'=>'Parameter filePath can not be empty'];
                }
                // 文件名称
                $object = $params['object'];
                // <yourLocalFile>由本地文件路径加文件名包括后缀组成，例如/users/local/myfile.txt
                $filePath = $params['filePath'];
                try{
                    $ossClient = new \OSS\OssClient($accessKeyId, $accessKeySecret, $endpoint);
                    $result = $ossClient->uploadFile($bucket, $object, $filePath);
                    $this->response = $result;
                    $returnData = ['code'=>0,'msg'=>'Upload fail','response'=>$result];
                    if(isset($result['info']['http_code']) && $result['info']['http_code'] == '200'){
                        $returnData = ['code'=>1,'msg'=>'Upload successful','response'=>$result];
                    }
                } catch(\OSS\core\OssException $e) {
                    //printf($e->getMessage() . "\n");
                    $this->catch_e = $e->getMessage();
                    $returnData = ['code'=>0,'msg'=>$e->getMessage()];
                }
                break;
            default:
                $returnData = ['code'=>0,'msg'=>'Please select upload mode'];
        }
        return $returnData;
    }

    /**
     * aliyunoss上传结果
     * @return array
     */
    public function getResponse()
    {
        // TODO: Implement getResponse() method.
        return $this->response;
    }

    /**
     * aliyunoss上传异常信息
     * @return array
     */
    public function getCatchE()
    {
        // TODO: Implement getCatchE() method.
        return $this->catch_e;
    }
}
