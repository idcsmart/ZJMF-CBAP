<?php

namespace app\common\lib;

/**
 * @desc
 * @author wyh
 * @time 2023-02-13
 * @use app\common\lib\IdcsmartCache
 */
class IdcsmartCache
{
    public static function cache($key,$value='',$timeout=null)
    {
        // 判断是否安装redis扩展
        if (extension_loaded('redis')){
            $Redis = RedisPool::getRedis('redis');
            if (is_null($value)){
                return $Redis->del($key);
            }elseif($value===''){
                return $Redis->get($key);
            }else{
                return $Redis->set($key,$value,isset($timeout)?(float)$timeout:0);
            }
        }else{
            return cache($key,$value,$timeout);
        }
    }
}
