<?php


namespace Gen\BaseApi\Service;


use Gen\App;

class ToolsSvc
{
    //批量获取KEY
    static function GetAllKey($keys,$pre = ''){
        $newKey = $keys;
        if($pre){
            $newKey = array_map(function ($v) use($pre){
                return $pre.$v;
            },$keys);
        }
        $data = App::Redis()->mget($newKey);
        $len = count($keys);
        $ret = [];
        for($i = 0 ; $i < $len ; $i++){
            $ret[$keys[$i]] = $data[$i] ?? '';
        }
        return $ret;
    }
}