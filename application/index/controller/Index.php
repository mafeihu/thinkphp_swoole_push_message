<?php
namespace app\index\controller;
use think\Db;
use think\Exception;

class Index
{
    public function index()
    {

        return 'hellow swoole';
    }

    public function hello($name = 'ThinkPHP5')
    {

        return 'hello,' . $name;

    }

    public function test(){
        return 'ceshiphp';
    }



}
