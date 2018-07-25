<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/17
 * Time: 15:40
 */
namespace app\admin\controller;
use app\common\lib\Util;
use think\Controller;
use \think\Validate;
/**
 * 图片上传文件
 * Class Image
 */
class Image extends Controller {

    /**
     * 文件上传
     * @param string $dirname
     */
    public function index($dirname=''){
            $file = request()->post('file');
            print_r($_FILES);
            if(empty($file)){
                return Util::show(config("code.error"),'上传文件不能空',$file);
            }
            //移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->validate(
                [
                    'size'=>2000000,
                    'ext'=>'png,jpg,jpeg,gif',
                    'mine'=>"image"
                ]
            )->move(ROOT_PATH . 'public' . DS . 'upload'.DS.$dirname);
            if($info){
                // 成功上传后 获取上传信
                if($dirname){
                    $url = '/upload/'.DS.'/image/'.$dirname.'/'.$info->getSaveName();
                }else{
                    $url = '/upload/'.DS.'/image/'.$info->getSaveName();
                }
            }else{
                return Util::show(config("code.error"),$file->getError());
            }
            return Util::show(config("code.success"),'upload success',$url);
    }

}