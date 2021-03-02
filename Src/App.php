<?php


namespace Gen;


class App
{
    static function AppName(){
        return defined(APP_NAME) ? APP_NAME : strtolower(basename(APP_ROOT));
    }
    /**
     * @param $conName
     * @return \Redis
     * @author 耿鸿飞 <15911185633>
     * @date 2021/2/19
     * @like
     */
    static function Redis($conName = 'default'){
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
        return self::AesEncode($data,Conf::Ins()->get('app.token.key',"1234568901234567"));
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
        return self::AesDecode($data,Conf::Ins()->get('app.token.key',"1234568901234567"));
    }

    static function Error($code,$msg){
        return self::Success("",$code,$msg);
    }
    static function Success($data,$code = 0 ,$msg = ""){
        return ['code' => $code,'msg' => $msg,'data' => $data];
    }

    static function GetCache($key,$func,$timeout = null){
        $redis = self::Redis();
        $data = $redis->get($key);
        if(!$data){
            $data = $func();
            $redis->set($key,json_encode($data),$timeout);
            return $data;
        }
        return json_decode($data,true);
    }

    // 处理的代码要求Golang也能处理
    static function AesEncode($str,$key){
        $method = 'aes-128-cbc';
        $len = openssl_cipher_iv_length($method);
        $iv = substr($key,0,$len);
        $key = substr($key,0,$len);
        $ret = openssl_encrypt($str,$method,$key,0,$iv);
        return $ret;
        //return base64_encode();
    }
    // 处理Golang加密的内容
    static function AesDecode($data,$key){
        $method = 'aes-128-cbc';
        $len = openssl_cipher_iv_length($method);
        $iv = substr($key,0,$len);
        $key = substr($key,0,$len);
        return openssl_decrypt($data,$method,$key,0,$iv);
    }
}