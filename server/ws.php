<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/16
 * Time: 16:39
 */
use app\common\lib\redis\Predis;
use think\Db;
/**
 * 面向对象的web_socket封装 因为web_socket基于http_server
 */
class Ws{
    CONST HOST = "0.0.0.0";
    CONST PORT = 8801;

    //swoole_websoket_server 对象
    public  $ws = null;

    public function __construct()
    {
//        每次启动服务清空redis里面的连接用户的唯一标识
//        获取redis用户fd列表
//        $fdList = Predis::getInstance()->get(config('redis.live_game_key'));
//        var_dump($fdList);
//        if(!empty($fdList)){
//            foreach ($fdList as $fd){
//                //进行删除
//                Predis::getInstance()->sRem(config("redis.live_game_key"),$fd);
//            }
//        }
        //创建服务
        $this->ws = new swoole_websocket_server(self::HOST, self::PORT);

        //设置请求地址静态页面
        $this->ws->set(
            [
                'enable_static_handler' => true,
                'document_root' => '/www/swoole/thinkphp_swoole/public/static',
                'worker_num' =>1,
                'task_worker_num' => 4,
            ]
        );

        //websoke的回调方法
        //注册Server的事件回调函数
        $this->ws->on('open',[$this,'onOpen']);
        $this->ws->on('message',[$this,'onMessage']);

        //回调方法
        $this->ws->on("workerstart", [$this, 'onWorkerStart']);
        $this->ws->on("request", [$this, 'onRequest']);
        $this->ws->on("task", [$this, 'onTask']);
        $this->ws->on("finish", [$this, 'onFinish']);
        $this->ws->on("close", [$this, 'onClose']);

        //启动服务
        $this->ws->start();
    }

    /**
     * 监听ws连接事件
     * @param $ws
     * @param $request
     */
    public function onOpen($ws,$request)
    {
        //mysql数据库的操作
        DB::name('key')->insert(['live_game_key'=>$request->fd]);
        //获取连接用户的唯一标识($fd)保存到redis里面
        Predis::getInstance()->sAdd(config("redis.live_game_key"),$request->fd);
        print_r($request->fd);

    }


    /**
     * 监听ws消息事件(消息)
     * @param $ws
     * @param $frame
     */
    public function onMessage($ws,$frame)
    {
        echo "ser-push-message:{$frame->data}\n";
        //客户端会马上收到以下信息
        $ws->push($frame->fd,'server-push'.date("Y-m-d H:i:s"));
    }


    /**
     * 此事件在Worker进程/Task进程启动时发生。这里创建的对象可以在进程生命周期内使用。
     *加载thinkphp相关文件
     */
    public function onWorkerStart(swoole_server $server,$worker_id){
        //1. 定义应用目录
        define('APP_PATH', __DIR__ . '/../application/'); //会执行thinkPHP默认加载文件

        //2.加载thinkphp核心框架;ThinkPHP 引导文件
        // 加载基础文件
        require __DIR__ . '/../thinkphp/base.php';
        //require __DIR__ . '/../thinkphp/start.php';


    }

    //接收请求，响应并且返回
    public function onRequest($request, $response){
        /**
         * 无需修改框架
         */
        // 加载基础文件
        //require_once __DIR__ . '/../thinkphp/base.php';
        //require __DIR__ . '/../thinkphp/start.php';
        //2. 定义应用目录
        //define('APP_PATH', __DIR__ . '/../application/'); //会执行thinkPHP默认加载文件

        //设置请求方法
        $_SERVER = [];
        if(isset($request->server)){
            foreach ($request->server as $k=>$v){
                $_SERVER[strtoupper($k)] = $v;
            }
        }
        if(isset($request->header)){
            foreach ($request->header as $k=>$v){
                $_SERVER[strtoupper($k)] = $v;
            }
        }


        $_GET = [];
        if(isset($request->get)){
            foreach ($request->get as $k=>$v){
                $_GET[$k] = $v;
            }
        }

        $_POST = [];
        if(isset($request->post)){
            foreach ($request->post as $k=>$v){
                $_POST[$k] = $v;
            }
        }

        $_FILES = [];
        if(isset($request->files)){
            foreach ($request->files as $k=>$v){
                $_FILES[$k] = $v;
            }
        }
        //设置一个swoole对象的超全局变量
        $_POST['http_server'] = $this->ws;
        //打开输出控制缓冲
        ob_start();

        // 执行应用并响应
        try{
            think\Container::get('app', [APP_PATH])
                ->run()
                ->send();
        }catch (\Exception $e){
            //todo 获取异常
        }

        //返回输出缓冲区的内容
        $res = ob_get_contents();
        //清空（擦除）缓冲区并关闭输出缓冲
        ob_end_clean();

        $response->end($res);

        //$this->http->close($request->fd);
    }


    /**
     * @param $serv
     * @param $taskId
     * @param $workerId
     * @param $data
     * @return string
     */
    public function onTask( $serv, $task_id, $src_worker_id, $data){
        // 分发 task 任务机制，让不同的任务 走不同的逻辑
        //DB::name('key')->insert(['live_game_key'=>->fd]);
        //获取投放的数据
        print_r($data);
        // 耗时场景 10s
        sleep(10);
        return "on task finish"; // 告诉worker，并返回给onFinish的$data

    }


    /**
     * @param $serv
     * @param $task_id
     * @param $data
     */
    public function OnFinish( $serv, $task_id, $data){
        echo "taskId:{$task_id}\n";
        echo "finish-data-sucess:{$data}\n";
    }

    /**
     * close
     * @param $ws
     * @param $fd
     */
    public function onClose($ws, $fd) {
        //mysql数据库的操作
        DB::name('key')->where('live_game_key',$fd)->delete();
        //关闭连接用户的唯一标识($fd)从redis里面删除
        Predis::getInstance()->sRem(config("redis.live_game_key"),$fd);
        echo "clientid:{$fd}\n";
    }
}
new Ws();