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

    /**
     * 获取到尽头的最后时间戳
     * @return false|int
     * @author 耿鸿飞 <15911185633>
     * @date 2021/3/8
     * @like
     */
    static function TodayExpireTime(){
        return strtotime(date("H:m:d 23:59:59")) - time();
    }
}