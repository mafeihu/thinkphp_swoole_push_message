<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/13
 * Time: 13:46
 */
namespace app\index\controller;
use think\Controller;
use app\common\lib\Util;
use app\common\lib\Redis;
class Send extends Controller{
    /**
     * 发送验证码
     */
    public function index(){
        $phoneNum = '';
        $phoneNum = $_GET['phone_num'];
        if(empty($phoneNum) || !isset($phoneNum)){
            return Util::show(config('code.success'),$phoneNum);
        }
        //todo
        //生成一个随机
        $code = rand(1000,9999);
        //调用阿里大于
        //todo  这里就不写了

        //redis数据存储
        $redis = new \Swoole\Coroutine\Redis();
        //连接redis
        try{
            $redis->connect(config('redis.host'),config('redis.port'),config('redis.out_time'));
        }catch (\Exception $e){
            return Util::show(config('code.success'),'redis连接失败');
        }
        //保存短信验证码数据
        $redis->set(Redis::smsKey($phoneNum),4567);

        //获取短信验证码
        $code = $redis->get(Redis::smsKey($phoneNum));
        return Util::show(config('code.success'),$code);

    }
}