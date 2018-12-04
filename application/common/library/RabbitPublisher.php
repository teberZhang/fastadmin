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
        $rabbitmq = Config('rabbitmq');
        $connection = new AMQPStreamConnection($rabbitmq['host'],$rabbitmq['port'],$rabbitmq['username'],$rabbitmq['password']);
        $channel = $connection->channel();
        $expiration = 6000;
        $cache_exchange_name = 'cache_exchange'.$expiration;
        $cache_queue_name = 'cache_queue'.$expiration;
        $channel->exchange_declare('delay_exchange', 'direct',false,false,false);
        $channel->exchange_declare($cache_exchange_name, 'direct',false,false,false);
        $tale = new AMQPTable();
        $tale->set('x-dead-letter-exchange', 'delay_exchange');
        $tale->set('x-dead-letter-routing-key','delay_exchange');
        $tale->set('x-message-ttl',$expiration);
        $channel->queue_declare($cache_queue_name,false,true,false,false,false,$tale);
        $channel->queue_bind($cache_queue_name, $cache_exchange_name,'');
        $channel->queue_declare('delay_queue',false,true,false,false,false);
        $channel->queue_bind('delay_queue', 'delay_exchange','delay_exchange');
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
        $channel->exchange_declare('delay_exchange', 'direct',false,false,false);
        $channel->exchange_declare('cache_exchange', 'direct',false,false,false);
        $channel->queue_declare('delay_queue',false,true,false,false,false);
        $channel->queue_bind('delay_queue', 'delay_exchange','delay_exchange');
        echo ' [*] Waiting for message. To exit press CTRL+C '.PHP_EOL;
        //只有consumer已经处理并确认了上一条message时queue才分派新的message给它
        $receiver = new self();
        $channel->basic_qos(null, 1, null);
        $channel->basic_consume('delay_queue','',false,false,false,false,[$receiver,'callMerchant']);
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
        /*Db::name('rabbitmq')->insert([
            'username'=>'a'.$content['id'],
            'phone'=>'138'.$content['id'],
            'date' => $nowDate
        ]);*/
        echo date('Y-m-d H:i:s')." [x] Received",$content['id'],PHP_EOL;
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']); //不加这句会中断
    }
}
