<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/13
 * Time: 16:28
 */
namespace  app\common\lib;

class Redis{
    /**
     * 验证码 reids前缀
     * @var string
     */
    public static $pre = 'sms_';

    /**
     * 存储验证码 reids key
     * @param $phone
     * @return string
     */
    public static function smsKey($phone){
        return self::$pre.$phone;
    }
}