<?php
/**
 *
 * @author 耿鸿飞<genghongfei@soyoung.com>
 * @link
 * @Date: 2021/2/28 23:56
 */


namespace Gen;
/**
 * 增加消息队列的处理
 * @author 耿鸿飞<genghongfei@soyoung.com>
 * @link
 * @Date: 2021/3/1 00:05
 * Class Queue
 * @package Gen
 */
abstract class QueueRedis implements Command
{
    protected $redisConName = 'default';
    protected $maxExecuteTimes = 5000;
    abstract function getQueueName();
    function handel()
    {
        $redis = App::Redis($this->redisConName);
        $runTime = 0;
        while(true && $runTime < $this->maxExecuteTimes){
            $data = $redis->blPop($this->getQueueName(),10);
            if(!$data){
                continue;
            }
            $runTime++;
            Log::Queue('%s %s %s -> %s',static::class,$this->redisConName,$this->getQueueName(),$data);
            try{
                $ret = $this->execQueueData($data);
                if(!$ret){
                    $redis->rPush($this->getQueueName(),$data);
                }
            }catch (\Exception $e){
                Log::Queue('%s %s %s -> %s (%s)',static::class,$this->redisConName,$this->getQueueName(),$data,$e->getTraceAsString());
                $redis->rPush($this->getQueueName(),$data);
            }
        }
    }
    abstract function execQueueData($data);
}