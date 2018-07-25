<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/16
 * Time: 14:19
 */
/**
 * 实现同步实现同步redis
 */
namespace  app\common\lib\redis;
class Predis{
    /**
     * 单例模式的变量
     * @var null
     */
    private static $_instance=null;

    public $redis = '';


    /**
     * 单例模式应用防止多次连接redis，提高性能
     * @return Predis|null
     */
    public static function getInstance(){
        if(is_null(self::$_instance) || empty(self::$_instance)){
            self::$_instance = new self();
        }
        return self::$_instance;
    }


    /**
     *连接redis
     */
    private function __construct()
    {
        $this->redis = new \Redis();
        $result = $this->redis->connect(config('redis.host'), config('redis.port'));
        if($result==false){
            throw new \Exception('redis connect fail');
        }

    }

    /**
     *redis set方法的应用
     * @param $key
     * @param $value
     * @param int $time
     * @return bool|string
     */
    public function set($key,$value,$time=0){
        if(!$key){
            return '';
        }

        if(is_array($value)){
            $value = json_encode($value);
        }

        if(!$time){
            return $this->redis->set($key,$value);
        }
        return $this->redis->setex($key,$time,$value);
    }

    /**
     * redis get方法
     * @param $key
     * @return string
     */
    public function get($key){
        if(!$key){
            return '';
        }
        return $this->redis->get($key);
    }

    /**
     * 获取有序列表的结合
     * @param $key
     * @return array
     */
    public function sMembers($key) {
        return $this->redis->sMembers($key);
    }


    /**
     * 获取list的元素值结合
     */
    public function lRange($key){

        return $this->redis->lRange($key,0,-1);
    }

    /**
     * 魔术方法__call
     */
    public function __call($name, $arguments) {
//        echo $name.PHP_EOL;
//        print_r($arguments);
        if(count($arguments) != 2) {
            return '';
        }
        $this->redis->$name($arguments[0], $arguments[1]);
    }

}

