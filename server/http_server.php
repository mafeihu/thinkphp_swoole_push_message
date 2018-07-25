<?php
//创建服务
$http = new swoole_http_server("0.0.0.0", 8801);

//设置请求地址静态页面
$http->set(
    [
       'enable_static_handler' => true,
        'document_root' => '/www/swoole/thinkphp_swoole/public/static',
        'worker_num' =>1,
    ]
);

/**
 * 此事件在Worker进程/Task进程启动时发生。这里创建的对象可以在进程生命周期内使用。
 *加载thinkphp相关文件
 */
$http->on('WorkerStart',function (swoole_server $server,$worker_id){
    //1.加载thinkphp核心框架;ThinkPHP 引导文件
    // 加载基础文件
    require __DIR__ . '/../thinkphp/base.php';

    //require __DIR__ . '/../thinkphp/start.php';
    //2. 定义应用目录
    define('APP_PATH', __DIR__ . '/../application/'); //会执行thinkPHP默认加载文件
});


//接收请求，响应并且返回
$http->on('request', function ($request, $response) use ($http){
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
    $_SERVER = [];
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
    //
    //$http->close($http,$request->fd);
});
$http->start();