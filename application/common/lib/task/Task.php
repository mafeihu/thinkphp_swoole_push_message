<?php
/**
 * 代表的是  swoole里面 后续 所有  task异步 任务 都放这里来
 * Date: 18/3/27
 * Time: 上午1:20
 */
namespace app\common\lib\task;
use think\Db;
class Task{
    /**
     * 通过task机制发送赛况实时数据给客户端
     * @param $data
     * @param $serv swoole server对象
     */
    public function pushLive($data, $serv) {
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
                $serv->push($fds['live_game_key'],json_encode($data));
            }
        }
    }
}
