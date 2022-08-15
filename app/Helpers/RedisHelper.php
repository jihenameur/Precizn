<?php


namespace App\Helpers;


use Illuminate\Support\Facades\Redis;

class RedisHelper
{

    public function preAssignDeliveryToCommand($delivery_id, $command_id)
    {
       return Redis::lpush('command_pre_assign_'.$command_id,$delivery_id);
    }

    public function getPreAssignedDeliveryToCommand($command_id)
    {
       return Redis::lindex('command_pre_assign_'.$command_id,-1);
    }

    public function getAllPreAssignedDeliveriesToCommand($command_id)
    {
       return Redis::lrange('command_pre_assign_'.$command_id,0,Redis::llen('command_pre_assign_'.$command_id));
    }

    public function setAssignDeliveriesLimit($limit = 5)
    {
       return Redis::set('ASSIGN_DELIVERY_LIMIT', $limit);
    }

    public function getAssignDeliveriesLimit($limit = 5)
    {
        return Redis::get('ASSIGN_DELIVERY_LIMIT') ?? 5;
    }

    public function setDeliveryAssignTrialsLimit($limit = 3)
    {
        return Redis::set('ASSIGN_DELIVERY_TRIALS_LIMIT', $limit);
    }

    public function getDeliveryAssignTrialsLimit()
    {
        return Redis::get('ASSIGN_DELIVERY_TRIALS_LIMIT') ?? 3;
    }

    public function incrDeliveryAssignTrials($command_id)
    {
        Redis::incr('ASSIGN_DELIVERY_TRIALS-'.$command_id);
    }

    public function getDeliveryAssignTrials($command_id)
    {
        return Redis::get('ASSIGN_DELIVERY_TRIALS-'.$command_id) ?? 0;
    }

    public function setPreAssignedTimeLimit($minutes = 5)
    {
        return Redis::set('PRE_ASSIGNED_TIME_LIMIT',$minutes);
    }

    public function getPreAssignedTimeLimit($minutes = 5)
    {
        return Redis::get('PRE_ASSIGNED_TIME_LIMIT') ?? $minutes;
    }

}
