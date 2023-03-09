<?php

namespace app\common\lib;

/**
 * @desc
 * @author wyh
 * @time 2023-03-13
 * @use app\common\lib\RedisPool
 */
class RedisPool
{
    // 定义连接池
    private static $connections = [];
    private static $servers = [
        'redis' => [REDIS_HOST,REDIS_PORT,REDIS_PASSWORD], // host port password
    ];

    public static function addServer($config)
    {
        foreach ($config as $alias=>$data){
            self::$servers[$alias] = $data;
        }
    }

    public static function getRedis($alias,$select=0)
    {
        if (!array_key_exists($alias,self::$connections)){
            $redis = new \Redis();
            $redis->connect(self::$servers[$alias][0],self::$servers[$alias][1]);
            if (isset(self::$servers[$alias][2]) && !empty(self::$servers[$alias][2])){
                $redis->auth(self::$servers[$alias][2]);
            }
            self::$connections[$alias]=$redis;
        }
        self::$connections[$alias]->select($select);

        return self::$connections[$alias];
    }
}
