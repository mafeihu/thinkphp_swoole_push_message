<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/13
 * Time: 13:53
 */
namespace  app\common\lib;

class Util{
    /**
     * ApI 输出格式
     * @param $status
     * @param string $message
     * @param $data
     * @return string
     */
    public static function show($status,$message='',$data =[]){
        $result = [
            'status'=>$status,
            'message'=>$message,
            'data'=>$data,
        ];
        echo json_encode($result);
    }
}