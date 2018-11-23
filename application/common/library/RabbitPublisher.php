<?php

namespace app\common\library;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use think\Config;
use think\Db;
class RabbitPublisher
{
    /**
     * C2C订单过期(延时队列)
     * @client Publisher(生产者)
     * @access public
     * @param $orderItem   C2C订单信息
     * @return Response
     */
    public function taskMerchant($orderItem=[])
    {
        $expiredMinute = 30; //订单过期时间（分钟）
        $rabbitmq = Config('rabbitmq');
        $connection = new AMQPStreamConnection($rabbitmq['host'],$rabbitmq['port'],$rabbitmq['username'],$rabbitmq['password']);
        $channel = $connection->channel();
        //$expiration = $expiredMinute*60*1000; //ttl生存期毫秒数
        $expiration = 5000;
        $cache_exchange_name = 'cache_exchange'.$expiration;
        $cache_queue_name = 'cache_queue'.$expiration;
        $keyMark = 'merchantC2c'; //c2c-mq标识
        $channel->exchange_declare('delay_exchange'.$keyMark, 'direct',false,false,false);
        $channel->exchange_declare($cache_exchange_name, 'direct',false,false,false);
        $tale = new AMQPTable();
        $tale->set('x-dead-letter-exchange', 'delay_exchange');
        $tale->set('x-dead-letter-routing-key','delay_exchange');
        $tale->set('x-message-ttl',$expiration);
        $channel->queue_declare($cache_queue_name,false,true,false,false,false,$tale);
        $channel->queue_bind($cache_queue_name, $cache_exchange_name,'');
        $channel->queue_declare($keyMark.'_queue',false,true,false,false,false);
        $channel->queue_bind($keyMark.'_queue', 'delay_exchange'.$keyMark,'delay_exchange'.$keyMark);
        $msg = new AMQPMessage(json_encode($orderItem),array(
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ));
        $channel->basic_publish($msg,$cache_exchange_name,'');
        echo date('Y-m-d H:i:s')." [x] Sent 'Hello World!' ".PHP_EOL;
        $channel->close();
        $connection->close();
    }

    /**
     * C2C订单过期(延时队列)
     * @client Consumer(消费者)
     * @access public
     * @return Response
     */
    public function workerMerchant()
    {
        $rabbitmq = Config('rabbitmq');
        $connection = new AMQPStreamConnection($rabbitmq['host'],$rabbitmq['port'],$rabbitmq['username'],$rabbitmq['password']);
        $channel = $connection->channel();
        $keyMark = 'merchantC2c'; //c2c-mq标识
        $channel->exchange_declare('delay_exchange'.$keyMark, 'direct',false,false,false);
        $channel->exchange_declare('cache_exchange'.$keyMark, 'direct',false,false,false);
        $channel->queue_declare($keyMark.'_queue',false,true,false,false,false);
        $channel->queue_bind($keyMark.'_queue', 'delay_exchange'.$keyMark,'delay_exchange'.$keyMark);

        echo ' [*] Waiting for message. To exit press CTRL+C '.PHP_EOL;

        //只有consumer已经处理并确认了上一条message时queue才分派新的message给它
        $channel->basic_qos(null, 1, null);
        $receiver = new self();
        $channel->basic_consume($keyMark.'_queue','',false,false,false,false,[$receiver,'callMerchant']);
        while (count($channel->callbacks)) {
            $channel->wait();
        }
        $channel->close();
        $connection->close();
    }

    /**
     * C2C订单过期(延时队列)
     * @client Consumer-callback(消费者-回调)
     * @access public
     * @return Response
     */
    public function callMerchant($msg)
    {
        $content = json_decode($msg->body,true);
        $nowDate = date("Y-m-d H:i:s",time());
        //把用户信息插入数据库
        Db::name('rabbitmq')->insert([
            'username'=>'a'.$content['id'],
            'phone'=>'138'.$content['id'],
            'date' => $nowDate
        ]);
        echo date('Y-m-d H:i:s')." [x] Received",$content['id'],PHP_EOL;
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']); //不加这句会中断
    }
}
