<?php

namespace app\common\service;

use JPush\Client as JPushClient;
use think\Config;

/**
 * 极光推送
 */
class JPush implements Push
{
    /**
     * 应用程序app_key
     * @var string
     */
    protected $app_key = '';

    /**
     * 应用程序主密码
     * @var string
     */
    protected $master_secret = '';

    /**
     * 推送资源句柄
     * @var resource
     */
    protected $client = null;

    /**
     * 推送的地址
     * @var string
     */
    protected $url = 'https://api.jpush.cn/v3/push';

    /**
     * 推送结果集
     * @var array
     */
    protected $response = [];

    /**
     * 推送失败异常信息
     * @var string
     */
    protected $catch_e = null;

    /**
     * 默认参数
     * @var string
     */
    protected $paramsDefault = [];

    //实例化
    public function __construct($init = null)
    {
        $this->app_key = Config::get("jpush.app_key");
        $this->master_secret = Config::get("jpush.master_secret");
        if (isset($init['app_key']) && $init['app_key']) {
            $this->app_key = $init['app_key'];
        }
        if (isset($init['master_secret']) && $init['master_secret']) {
            $this->app_key = $init['master_secret'];
        }
        $this->init();
    }

    /**
     * 实例化
     */
    protected function init()
    {
        $this->client = new JPushClient($this->app_key,$this->master_secret);
        $this->paramsDefault = [
            'platform' => 'android', //android|ios|all
            //'alias' => '', //别名
            //'tag' => [], //标签 array('tag1', 'tag2')
            'alert' => json_encode(['module'=>'newsflash','data'=>['type'=>'jump','id'=>1000]]), //推送内容json
            'registration_id' => '120c83f76001a5b27b2', //唯一设备标识self
            //'registration_id' => '13065ffa4e527eececc', //唯一设备标识
            'notification' => '据news.bitcoin消息，Bitcoin.com商店现已与Egifter公司合作。由于合作，Bitcoin.com Store顾客可以使用比特币现金在全球商店和餐馆购买300多种顶级品牌礼品卡。',
            'notificationAlert' => 'Hi,xcj',
            'android_title' => 'Bitcoin.com商店现已与Egifter公司合作',
            'message_title' => 'hello xcj777',
            //notification自定义参数
            'notification_extras' => array(
                'key' => 'value',
                'jiguang'
            ),
            //message自定义参数
            'message_extras' => array(
                'key' => 'value',
                'jiguang777'
            ),
        ];
    }

    /**
     * 指定个人或群体=推送消息
     *
     * @param   string       $platform   设备类型android|ios|or(必填)
     * @param   string       $alias     别名 alias
     * @param   array       $tag    标签 array('tag1', 'tag2')
     * @param   json        $alert     推送消息内容(必填)
     * @param   string      $registration_id     唯一设备标识
     * @param   string       $notification     别名 alias
     * @param   string       $notificationAlert     别名 alias
     * @param   string       $android_title     别名 alias
     * @param   string       $message_title     别名 alias
     * @param   string       $notification_extras     别名 alias
     * @param   string       $message_extras     别名 alias
     * @return  boolean
     */
    public function push($params=[])
    {
        //合并参数
        $params = array_merge($this->paramsDefault,$params);
        //return $params;
        $client = $this->client;
        try {

            $clientResouce = $client->push();
            //push-platform
            if(isset($params['platform']) && $params['platform'] == 'ios'){ //ios-push
                $clientResouce = $clientResouce->setPlatform('ios');
            } elseif(isset($params['platform']) && $params['platform'] == 'android') { //android-push
                $clientResouce = $clientResouce->setPlatform('android');
            } else { //all-push
                $clientResouce = $clientResouce->setPlatform('all');
            }
            // 一般情况下，关于 audience 的设置只需要调用 addAlias、addTag、addTagAnd  或 addRegistrationId
            // 这四个方法中的某一个即可，这里仅作为示例，当然全部调用也可以，多项 audience 调用表示其结果的交集
            // 即是说一般情况下，下面三个方法和没有列出的 addTagAnd 一共四个，只适用一个便可满足大多数的场景需求

            //push-alias
            if(isset($params['alias']) && $params['alias']){
                $clientResouce = $clientResouce->addAlias($params['alias']);
            }
            //push-tag
            if(isset($params['tag']) && $params['tag']){
                $clientResouce = $clientResouce->addTag($params['tag']);
            }
            //push-registration_id
            if(isset($params['registration_id']) && $params['registration_id']){
                $clientResouce = $clientResouce->addRegistrationId($params['registration_id']);
            }
            $clientResouce = $clientResouce->setNotificationAlert($params['notificationAlert']);
            //push-alert
            if(isset($params['platform']) && $params['platform'] == 'ios'){ //ios-push
                $clientResouce = $clientResouce->iosNotification($params['notification'], array(
                    'sound' => 'sound.caf',
                    'badge' => '+1', //角标消息数量
                    // 'content-available' => true,
                    // 'mutable-content' => true,
                    'category' => 'jiguang',
                    'extras' => $params['notification_extras'],
                ));
            } elseif(isset($params['platform']) && $params['platform'] == 'android') { //android-push
                $clientResouce = $clientResouce->androidNotification($params['notification'], array(
                    'title' => $params['android_title'],
                    // 'builder_id' => 2,
                    'extras' => $params['notification_extras'],
                ));
            } else { //all-push
                $clientResouce = $clientResouce->iosNotification($params['notification'], array(
                    'sound' => 'sound.caf',
                    'badge' => '+1', //角标消息数量
                    // 'content-available' => true,
                    // 'mutable-content' => true,
                    'category' => 'jiguang',
                    'extras' => $params['notification_extras'],
                ));
                $clientResouce = $clientResouce->androidNotification($params['notification'], array(
                    'title' => $params['android_title'],
                    // 'builder_id' => 2,
                    'extras' => $params['notification_extras'],
                ));
            }
            $clientResouce = $clientResouce->message($params['alert'], array(
                'title' => $params['message_title'],
                'content_type' => 'text',
                'extras' => $params['message_extras'],
            ));
            $clientResouce = $clientResouce->options(array(
                // sendno: 表示推送序号，纯粹用来作为 API 调用标识，
                // API 返回时被原样返回，以方便 API 调用方匹配请求与返回
                // 这里设置为 100 仅作为示例

                // 'sendno' => 100,

                // time_to_live: 表示离线消息保留时长(秒)，
                // 推送当前用户不在线时，为该用户保留多长时间的离线消息，以便其上线时再次推送。
                // 默认 86400 （1 天），最长 10 天。设置为 0 表示不保留离线消息，只有推送当前在线的用户可以收到
                // 这里设置为 1 仅作为示例

                // 'time_to_live' => 1,

                // apns_production: 表示APNs是否生产环境，
                // True 表示推送生产环境，False 表示要推送开发环境；如果不指定则默认为推送生产环境

                'apns_production' => false,

                // big_push_duration: 表示定速推送时长(分钟)，又名缓慢推送，把原本尽可能快的推送速度，降低下来，
                // 给定的 n 分钟内，均匀地向这次推送的目标用户推送。最大值为1400.未设置则不是定速推送
                // 这里设置为 1 仅作为示例

                // 'big_push_duration' => 1
            ));
            $this->response = $clientResouce->send();
            return true;

        } catch (\JPush\Exceptions\APIConnectionException $e) {
            // try something here
            $this->catch_e = $e;
            return false;
        } catch (\JPush\Exceptions\APIRequestException $e) {
            // try something here
            $this->catch_e = $e;
            return false;
        }
    }

    /**
     * 推送消息(全推送)
     * @param   string       $alert   推送内容
     * @return  boolean
     */
    public function pushall($params = ['alert'=>'xcj','platform'=>'all','type'=>'notice'])
    {
        $client = $this->client;
        $pusher = $client->push();
        //设置发送平台
        $pusher->setPlatform($params['platform']);
        //设置发送对象，发送给所有人
        $pusher->addAllAudience();
        //跳转类
        if($params['type'] == 'jump'){
            if(isset($params['platform']) && $params['platform'] == 'all') { //all-push
                $pusher = $pusher->androidNotification($params['notification'], array(
                    'title' => $params['android_title'],
                    'extras' => $params['notification_extras'],
                ));
            }
            $pusher = $pusher->message($params['alert'], array());
        } else {
            //通知类
            $pusher->setNotificationAlert($params['alert']);
        }

        try {
            $this->response = $pusher->send();
            return true;
        } catch (\JPush\Exceptions\JPushException $e) {
            //print $e;
            $this->catch_e = $e;
            return false;
        }
    }

    /**
     * 获取推送结果
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * 获取推送异常信息
     */
    public function getCatchE()
    {
        return $this->catch_e;
    }

}
