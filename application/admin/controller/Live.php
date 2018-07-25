<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/17
 * Time: 15:40
 */
namespace app\admin\controller;
use think\Controller;
use app\common\lib\redis\Predis;
use think\Db;
use app\common\lib\Util;
/**
 * 直播管理类
 * Class Image
 */
class Live extends Controller {
    /**
     * 向客户端推送消息
     */
    public function push(){
        if(empty($_GET)){
            return Util::show(config('code.error'),'data is empty');
        }

        //球队
        $teams = [
            1=>[
                'name'=>'马刺',
                'logo'=>'/live/imgs/team1.png',
            ],

            4=>[
                'name'=>'火箭',
                'logo'=>'/live/imgs/team2.png'
            ]
        ];
        $data = [
            'type' => intval($_GET['type']),
            'title' => !empty($teams[$_GET['team_id']]['name']) ?$teams[$_GET['team_id']]['name'] : '直播员',
            'logo' => !empty($teams[$_GET['team_id']]['logo']) ?$teams[$_GET['team_id']]['logo'] : '',
            'content' => !empty($_GET['content']) ? $_GET['content'] : '',
            'image' => 'http://baner.tstmobile.com/upload/image/20180706/75c3d50f0499aaf8af73598c3895f658.jpeg',
        ];

        //进行投放任务 数据组织好 push直播页面

        /**
         * 获取在线用户
         */
        //1.redis 获取连接的用户数量
        //$clients = Predis::getInstance()->sMembers(config("redis.live_game_key"));

        //2.mysql 获取连接的用户数量
        $clients = DB::name('key')->field('live_game_key')->select();
        if(!empty($clients) && count($clients)>0){
            foreach ($clients as $fds){
                //获取超全局变量swoole
                $_POST['http_server']->push($fds['live_game_key'],json_encode($data));
            }
        }
    }
}