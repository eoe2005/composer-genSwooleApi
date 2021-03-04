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
    public static function __callStatic($name, $arguments)
    {
        self::save(strtoupper($name),...$arguments);
    }

    private static function save($type,$format){
        static $dir = '';
        $ars = func_get_args();
        array_shift($ars);
        $data = sprintf("[%s] %s %s\n",date("Y-m-d H:i:s"),$type,call_user_func_array('sprintf',$ars));
        if(!$dir){
            $dir = Conf::Ins()->get('Aapp.log.dir',APP_ROOT.DS.'logs');
            if(!is_dir($dir)){
                mkdir($dir,0777,true);
            }
        }
        file_put_contents($dir.DS.date("Ymd.").strtolower($type),$data,FILE_APPEND|LOCK_EX);
    }
}