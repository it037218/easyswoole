<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/7/5
 * Time: 下午5:20
 */

namespace Core\Component;


use Core\Swoole\SwooleHttpServer;

class Barrier
{
    private $tasks = array();
    private $maps = array();
    private $results = array();

    function add($taskName,$callable){
        if($callable instanceof \Closure){
            try{
                $callable = new SuperClosure($callable);
            }catch (\Exception $exception){
                trigger_error("async task serialize fail ");
                return false;
            }
        }
        $this->tasks[$taskName] = $callable;
        return true;
    }
    function run($timeout = 0.5){
        $temp = array();
        foreach ($this->tasks as $name => $task){
            $temp[] = $task;
            $this->maps[] = $name;
        }
        if(!empty($temp)){
            $ret = SwooleHttpServer::getInstance()->getServer()->taskWaitMulti($temp,$timeout);
            foreach ($ret as $index => $result){
                $this->results[$this->maps[$index]] = $result;
            }
        }

    }
    function getResults(){
        return $this->results;
    }
    function getResult($taskName){
        if(isset($this->results[$taskName])){
            return $this->results[$taskName];
        }else{
            return null;
        }
    }
}