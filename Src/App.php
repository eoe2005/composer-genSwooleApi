<?php


namespace Gen;


class App
{
    static function AppName(){
        return defined(APP_NAME) ? APP_NAME : "app";
    }
    /**
     * @param $conName
     * @return \Redis
     * @author 耿鸿飞 <15911185633>
     * @date 2021/2/19
     * @like
     */
    static function Redis($conName){
        static $cons = [];
        if(!isset($cons[$conName]) || !$cons[$conName]->ping()){
            $redis = new \Redis();
            $redis->pconnect(Conf::Ins()->get("redis.".$conName.'.host'));
            $redis->setOption(\Redis::OPT_PREFIX,self::AppName().':'.$conName.":");
            $redis->select(Conf::Ins()->get("redis.".$conName.'.db'));
            $cons[$conName] = $redis;
        }
        return $cons[$conName];
    }

    /**
     * 接口请求限制
     * @param $key
     * @param int $timeout
     * @return bool
     * @author 耿鸿飞 <15911185633>
     * @date 2021/2/19
     * @like
     */
    static function ReqLimit($key,$timeout = 1){
        $redis = self::Redis('limit');
        if ($redis->setnx($key,$timeout)){
            $redis->expire($key,$timeout);
            return true;
        }
        return false;
    }

    /**
     * 加密
     * @param $data
     * @return string
     * @author 耿鸿飞 <15911185633>
     * @date 2021/2/19
     * @like
     */
    static function SecureEncode($data){
        if(!$data){
            return '';
        }
        return base64_encode(openssl_encrypt($data,Conf::Ins()->get('app.token.encrypt_type',"aes-128-cbc"),Conf::Ins()->get('app.token.key',"123456")));
    }

    /**
     * 解密
     * @param $data
     * @return false|string
     * @author 耿鸿飞 <15911185633>
     * @date 2021/2/19
     * @like
     */
    static function SecureDecode($data){
        if(!$data){
            return '';
        }
        return openssl_decrypt(base64_decode($data),Conf::Ins()->get('app.token.encrypt_type',"aes-128-cbc"),Conf::Ins()->get('app.token.key',"123456"));
    }

    static function Error($code,$msg){
        return self::Success("",$code,$msg);
    }
    static function Success($data,$code = 0 ,$msg = ""){
        return ['code' => $code,'msg' => $msg,'data' => $data];
    }
}