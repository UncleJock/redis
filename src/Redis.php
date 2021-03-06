<?php

namespace bangmoo\redis;

use Exception;
use RuntimeException;

class Redis
{
    /**
     * @var self
     */
    private static $_instance;
    /**
     * @var \Redis
     */
    private $redis;

    /**
     * @param string $host
     * @param int $port
     * @param string $password 密码
     */
    private function __construct($host = '127.0.0.1', $port = 6379,string $password = '')
    {
        // 检测php环境
        if (!extension_loaded('redis')) {
            throw new RuntimeException('not support:redis');
        }
        try {
            $this->redis = new \Redis();
            $this->redis->connect($host, $port);
            $this->redis->auth($password);
        } catch (Exception $e) {
            throw new RuntimeException('Redis connect error:' . $e->getMessage());
        }
    }

    /**
     * redis 实例生成
     * @param array $config
     * @return Redis
     */
    public static function getInstance(array $config = []): Redis
    {
        $host = $config['host']??'127.0.0.1';
        $port = $config['port']??6379;
        $password = $config['password']??'';
        if (!(self::$_instance instanceof self)) {
            try {
                self::$_instance = new self($host,$port,$password);
            } catch (Exception $e) {
                throw new RuntimeException($e->getMessage());
            }
        }
        return self::$_instance;
    }

    /**
     * 设置值  构建一个字符串
     * @param string $key KEY名称
     * @param string $value 设置值
     * @param int $timeOut 时间  0表示无过期时间
     * @return boolean
     */
    public function set($key, $value, $timeOut = 600)
    {
        return $this->redis->setex($key, $timeOut, $value);
    }

    /*
     * 构建一个集合(无序集合)
     * @param string $key 集合Y名称
     * @param string|array $value  值
     */
    public function sadd($key, $value)
    {
        return $this->redis->sadd($key, $value);
    }

    /*
     * 构建一个集合(有序集合)
     * @param string $key 集合名称
     * @param string|array $value  值
     * @return mixed
     */
    public function zadd($key, $score, $value,$value1)
    {
        return $this->redis->zadd($key, $score, $value,$value1);
    }

    /**
     * 取集合对应元素
     * @param string $setName 集合名字
     * @return mixed
     */
    public function smembers($setName)
    {
        return $this->redis->smembers($setName);
    }

    /**
     * 构建一个列表(先进后去，类似栈)
     * @param string $key KEY名称
     * @param string $value 值
     * @return mixed
     */
    public function lpush($key, $value)
    {
        echo "$key - $value \n";
        return $this->redis->LPUSH($key, $value);
    }

    /**
     * 构建一个列表(先进先去，类似队列)
     * @param string $key KEY名称
     * @param string $value 值
     * @return bool|int
     */
    public function rpush($key, $value)
    {
        return $this->redis->rpush($key, $value);
    }

    /**
     * 获取所有列表数据（从头到尾取）
     * @param string $key KEY名称
     * @param int $head 开始
     * @param int $tail 结束
     * @return array
     */
    public function lranges($key, $head, $tail)
    {
        return $this->redis->lrange($key, $head, $tail);
    }

    /**
     * HASH类型
     * @param string $tableName 表名字key
     * @param $field
     * @param string $value 值
     * @return bool|int
     */
    public function hset($tableName, $field, $value)
    {
        return $this->redis->hset($tableName, $field, $value);
    }

    public function hget($tableName, $field)
    {
        return $this->redis->hget($tableName, $field);
    }


    /**
     * 设置多个值
     * @param array $keyArray KEY名称
     * @param $timeout
     * @return bool|string
     */
    public function sets($keyArray, $timeout)
    {
        if (is_array($keyArray)) {
            $retRes = $this->redis->mset($keyArray);
            if ($timeout > 0) {
                foreach ($keyArray as $key => $value) {
                    $this->redis->expire($key, $timeout);
                }
            }
            return $retRes;
        }
        return 'Call  ' . __FUNCTION__ . ' method  parameter  Error !';
    }

    /**
     * 通过key获取数据
     * @param string $key KEY名称
     * @return mixed
     */
    public function get($key)
    {
        return $this->redis->get($key);
    }

    /**
     * 同时获取多个值
     * @param array $keyArray 获key数值
     * @return mixed
     */
    public function gets($keyArray)
    {
        if (is_array($keyArray)) {
            return $this->redis->mget($keyArray);
        }
        return 'Call  ' . __FUNCTION__ . ' method  parameter  Error !';
    }

    /**
     * 获取所有key名，不是值
     * @return mixed
     */
    public function keyAll()
    {
        return $this->redis->keys('*');
    }

    /**
     * 删除一条数据key
     * @param string $key 删除KEY的名称
     * @return mixed
     */
    public function del($key)
    {
        return $this->redis->del($key);
    }

    /**
     * 同时删除多个key数据
     * @param array $keyArray KEY集合
     * @return mixed
     */
    public function dels($keyArray)
    {
        if (is_array($keyArray)) {
            return $this->redis->del($keyArray);
        }
        return 'Call  ' . __FUNCTION__ . ' method  parameter  Error !';
    }

    /**
     * 数据自增
     * @param string $key KEY名称
     * @return mixed
     */
    public function increment($key)
    {
        return $this->redis->incr($key);
    }

    /**
     * 数据自减
     * @param string $key KEY名称
     * @return mixed
     */
    public function decrement($key)
    {
        return $this->redis->decr($key);
    }


    /**
     * 判断key是否存在
     * @param string $key KEY名称
     * @return boolean
     */
    public function isExists($key)
    {
        return $this->redis->exists($key);
    }

    /**
     * 重命名- 当且仅当newkey不存在时，将key改为newkey ，当newkey存在时候会报错哦RENAME
     *  和 rename不一样，它是直接更新（存在的值也会直接更新）
     * @param $key
     * @param string $newKey 新key名称
     * @return mixed
     */
    public function updateName($key, $newKey)
    {
        return $this->redis->RENAMENX($key, $newKey);
    }

    /**
     * 获取KEY存储的值类型
     * none(key不存在) int(0)  string(字符串) int(1)   list(列表) int(3)  set(集合) int(2)   zset(有序集) int(4)    hash(哈希表) int(5)
     * @param string $key KEY名称
     * @return mixed
     */
    public function dataType($key)
    {
        return $this->redis->type($key);
    }


    /**
     * 清空数据
     */
    public function flushAll()
    {
        return $this->redis->flushAll();
    }

    public function __clone()
    {
        trigger_error('Clone is not allow!', E_USER_ERROR);
    }

    /**
     * 返回redis对象
     * redis有非常多的操作方法，这里只封装了一部分
     * 拿着这个对象就可以直接调用redis自身方法
     * eg:$redis->redisOtherMethods()->keys('*a*')   keys方法没封
     */
    public function redisOtherMethods()
    {
        return $this->redis;
    }
}