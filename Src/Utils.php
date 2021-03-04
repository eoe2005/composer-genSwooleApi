<?php


namespace Gen;


class Utils
{
    //获取文件后缀
    static function GetFileExt($file){
        if(strpos($file,'?') !== false){
            $file = strstr($file,'?',true);
        }
        $index = strrpos($file,'.');
        if($index != false){
            return substr($file,$index);
        }
        return '';
    }
}