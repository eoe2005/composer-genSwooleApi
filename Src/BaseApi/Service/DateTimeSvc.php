<?php


namespace Gen\BaseApi\Service;


class DateTimeSvc
{
    //当前日期
    static function CurrentDateTime(){
        return date("Y-m-d H:i:s");
    }
}