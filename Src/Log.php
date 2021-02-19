<?php


namespace Gen;


class Log
{
    static function ServerDebug($format){
        echo sprintf("[%s] %s\n",date("Y-m-d H:i:s"),call_user_func_array('sprintf',func_get_args()));
    }

    static function Error($format){
        $data = func_get_args();
        self::save('ERROR',...$data);
    }
    static function Debug($format){
        $data = func_get_args();
        self::save('DEBUG',...$data);
    }
    static function Sql($format){
        $data = func_get_args();
        self::save('SQL',...$data);
    }
    static function Wasd(){}
    private static function save($type,$format){
        echo sprintf("[%s] %s %s\n",date("Y-m-d H:i:s"),$type,call_user_func_array('sprintf',func_get_args()));
    }
}