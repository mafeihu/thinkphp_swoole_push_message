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
use app\common\lib\redis\Predis;
class Login extends Controller{

    /**
     *
     */
    public function index(){
        //获取 phone code
        $phoneNum = intval($_GET['phone_num']);
        $code = intval($_GET['code']);
        if(empty($phoneNum) || empty($code)){
            return Util::show(config('code.error','phone or code error'));
        }
        //连接Redis


        //根据手机号获取redis code
        try{
            $redisCode = Predis::getInstance()->get(Redis::smsKey($phoneNum));
        }catch (\Exception $re){
            echo $re->getMessage();
        }
        //验证码
        if($code != $redisCode){
            return Util::show(config('code.error'),'验证码不正确');
        }

        //redis 





    }
}