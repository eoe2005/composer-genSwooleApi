<?php


namespace Gen;


class Conf
{
    private static $_self = false;

    private $_data = [];

    private function __construct(){
        $file = APP_ROOT.DS.'.env';
        if(is_file($file)){
            $this->_data = parse_ini_file($file);
        }
    }

    static function Ins(){
        if(self::$_self === false){
            self::$_self = new self();
        }
        return self::$_self;
    }
    public function get($key,$defval = ""){
        return $this->_data[$key] ?? $defval;
    }
    public function getInt($key,$defval = 0){
        return intval($this->get($key,"0"));
    }
    public function getArr($preKey,$keys = []){
        foreach ($keys as $k => $v){
            $keys[$k] = $this->get($preKey.'.'.$k,$v);
        }
        return $keys;
    }
}