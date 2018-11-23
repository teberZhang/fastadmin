<?php

//aliyunoss
return [
    //mock预发布
    'aliyunossDev' => [
        'AccessKeyId' => '',
        'AccessKeySecret' => '',
        'bucket' => '',
        'endpoint' => '',
        'allowtype' => ['file'], //允许上传的类型
    ],
    //生产环境
    'aliyunossMaster' => [
        'AccessKeyId' => '',
        'AccessKeySecret' => '',
        'bucket' => '',
        'endpoint' => '',
        'allowtype' => ['file'], //允许上传的类型
    ],
    'devhost' => '',
    'masterhost' => '',
];
