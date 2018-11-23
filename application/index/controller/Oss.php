<?php

namespace app\index\controller;

use app\common\controller\Frontend;
use app\common\library\OssClient;
use app\common\service\AliyunOss;
use app\common\model\Attachment;
use fast\Random;
use think\Config;

class Oss extends Frontend
{

    protected $noNeedLogin = '*';
    protected $noNeedRight = '*';
    protected $layout = '';
    protected $attachmentModel;

    public function _initialize()
    {
        parent::_initialize();
        $this->attachmentModel = new Attachment();
    }

    /**
     * 图片上传
     * @access public
     * @return Response
     */
    public function uploads()
    {
        $file = $this->request->file('img');
        if (empty($file))
        {
            return jsonArr(NO_UPLOAD_FILE,'');
        }
        $nowtime = time();
        $nowdate = date("Y-m-d H:i:s",$nowtime);
        //判断是否已经存在附件
        $sha1 = $file->hash();
        $upload = Config::get('upload.');
        preg_match('/(\d+)(\w+)/', $upload['maxsize'], $matches);
        $type = strtolower($matches[2]);
        $typeDict = ['b' => 0, 'k' => 1, 'kb' => 1, 'm' => 2, 'mb' => 2, 'gb' => 3, 'g' => 3];
        $size = (int) $upload['maxsize'] * pow(1024, isset($typeDict[$type]) ? $typeDict[$type] : 0);
        $fileInfo = $file->getInfo();
        $suffix = strtolower(pathinfo($fileInfo['name'], PATHINFO_EXTENSION));
        $suffix = $suffix ? $suffix : 'file';

        $mimetypeArr = explode(',', strtolower($upload['mimetype']));
        $typeArr = explode('/', $fileInfo['type']);
        //验证文件后缀
        if ($upload['mimetype'] !== '*' &&
            (
                !in_array($suffix, $mimetypeArr)
                || (stripos($typeArr[0] . '/', $upload['mimetype']) !== false && (!in_array($fileInfo['type'], $mimetypeArr) && !in_array($typeArr[0] . '/*', $mimetypeArr)))
            )
        ) {
            return jsonArr(UPLOAD_LIMITED,'');
        }
        $replaceArr = [
            '{year}'     => date("Y"),
            '{mon}'      => date("m"),
            '{day}'      => date("d"),
            '{hour}'     => date("H"),
            '{min}'      => date("i"),
            '{sec}'      => date("s"),
            '{random}'   => Random::alnum(16),
            '{random32}' => Random::alnum(32),
            '{filename}' => $suffix ? substr($fileInfo['name'], 0, strripos($fileInfo['name'], '.')) : $fileInfo['name'],
            '{suffix}'   => $suffix,
            '{.suffix}'  => $suffix ? '.' . $suffix : '',
            '{filemd5}'  => md5_file($fileInfo['tmp_name']),
        ];
        $savekey = $upload['savekey'];
        $savekey = str_replace(array_keys($replaceArr), array_values($replaceArr), $savekey);

        $uploadDir = substr($savekey, 0, strripos($savekey, '/') + 1);
        $fileName = substr($savekey, strripos($savekey, '/') + 1);
        $splInfo = $file->validate(['size' => $size]);
        if ($splInfo)
        {
            $imagewidth = $imageheight = 0;
            if (in_array($suffix, ['gif', 'jpg', 'jpeg', 'bmp', 'png', 'swf','pdf']))
            {
                $imgInfo = getimagesize($splInfo->getPathname());
                $imagewidth = isset($imgInfo[0]) ? $imgInfo[0] : $imagewidth;
                $imageheight = isset($imgInfo[1]) ? $imgInfo[1] : $imageheight;
            }
            //上传oss
            $ossClient = new OssClient(new AliyunOss());
            $ossResult = $ossClient->upload(['object'=>ltrim($uploadDir.$fileName,'/'),'filePath'=>$fileInfo['tmp_name']]);
            $responseInfo = $ossClient->getResponse();
            if($ossResult['code'] == 1 && isset($responseInfo['info']) && $responseInfo['info']['http_code'] == 200){
                $osshost = ALIYUNOSS_CODE == 1 ? Config::get('oss.masterhost') : Config::get('oss.devhost') ;
                $ossUrl = $osshost.$uploadDir.$fileName;
                $params = array(
                    'admin_id'    => 0,
                    'user_id'     => (int)$this->auth->id,
                    'filesize'    => $fileInfo['size'],
                    'imagewidth'  => $imagewidth,
                    'imageheight' => $imageheight,
                    'imagetype'   => $suffix,
                    'imageframes' => 0,
                    'mimetype'    => $fileInfo['type'],
                    'url'         => $ossUrl,
                    'uploadtime'  => time(),
                    'storage'     => 'local',
                    'sha1'        => $sha1,
                );
                $this->attachmentModel->save($params);
                $this->success(__('Upload successful'), [
                    'url' => $ossUrl
                ]);
            } else {
                //oss上传失败
                $this->error('上传失败');
            }
        } else {
            // 上传失败获取错误信息
            $this->error($file->getError());
        }
    }

}
