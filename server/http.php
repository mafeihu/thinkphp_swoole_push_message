<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/16
 * Time: 16:39
 */
/**
 * 面向对象的http server 封装
 */
class http{
    CONST HOST = "0.0.0.0";
    CONST PORT = 8801;

    //swoole_http_server 对象
    public  $http = null;

    public function __construct()
    {
        //创建服务
        $this->http = new swoole_http_server(self::HOST, self::PORT);

        //设置请求地址静态页面
        $this->http->set(
            [
                'enable_static_handler' => true,
                'document_root' => '/www/swoole/thinkphp_swoole/public/static',
                'worker_num' =>1,
                'task_worker_num' => 4,
            ]
        );

        //回调方法
        $this->http->on("workerstart", [$this, 'onWorkerStart']);
        $this->http->on("request", [$this, 'onRequest']);
        $this->http->on("task", [$this, 'onTask']);
        $this->http->on("finish", [$this, 'onFinish']);
        $this->http->on("close", [$this, 'onClose']);


        //启动服务
        $this->http->start();
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
    public function OnTask( $serv, $task_id, $src_worker_id, $data){
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
        echo "clientid:{$fd}\n";
    }
}

new http();