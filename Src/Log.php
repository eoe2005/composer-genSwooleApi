<?php


namespace Gen;


class Log
{
    static function ServerDebug($format){
        echo sprintf("[%s] %s\n",date("Y-m-d H:i:s"),call_user_func_array('sprintf',func_get_args()));
    }

    static function Error($format){
        call_user_func_array('\\Gen\\Log::ServerDebug',func_get_args());

    }
    static function Debug($format){}
    static function Sql($format){}
    static function Wasd(){}
    private static function save($data){

    }
}