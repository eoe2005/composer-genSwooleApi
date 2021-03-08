<?php


namespace Gen;


use OSS\Core\OssException;
use OSS\OssClient;

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

    //上传到阿里云
    static function UploadAliOSS($name,$path){
        $conf = Conf::Ins();
        try{
            $ossClient = new OssClient($conf->get('app.ali.oss.accessKeyId'),
                $conf->get('app.ali.oss.accessKeySecret'),
                $conf->get('app.ali.oss.endpoint'));
            $ret = $ossClient->uploadFile($conf->get('app.ali.oss.bucket'), $name, $path);
            return $ret['oss-request-url'] ?? false;
        } catch(OssException $e) {
            Log::Error("上传阿里云失败 ： %s",$e->getMessage());
            return false;
        }
    }

    /**
     * 生成阿里云的授权连接
     * @param $list
     * @param $keys
     * @param int $timeOut
     * @return array
     * @author 耿鸿飞<genghongfei@soyoung.com>
     * @link
     * @Date: 2021/3/6 12:34
     */
    static function BuildAliCdnUrl($list,$keys,$timeOut = 1800){
        $time = time() + $timeOut;
        $host = Conf::Ins()->get('app.ali.cdn.host','http://ali.ggvjj.cn');
        $aliKey = Conf::Ins()->get('app.ali.cdn.key','');
        $formData = sprintf('%d-%d-0-',$time,rand(0,999));

        return array_map(function ($item) use($keys,$host,$aliKey,$formData){
            if(is_array($keys)){
                foreach ($keys as $k){
                    $base = $item[$k] ?? '';
                    if($base){
                        $base = '/'.$base;
                        $auth = md5(sprintf('%s-%s%s',$base,$formData,$aliKey));
                        $item[$k] = $host.$base.'?auth_key='.$formData.$auth;
                    }
                }
            }else{
                $base = $item[$keys] ?? '';
                if($base){
                    $base = '/'.$base;
                    $auth = md5(sprintf('%s-%s%s',$base,$formData,$aliKey));
                    $item[$keys] = $host.$base.'?auth_key='.$formData.$auth;
                }
            }
            return $item;
        },$list);
    }

    /**
     * 生成ALI云的URL
     * @param $url
     * @param int $timeOut
     * @return string
     * @author 耿鸿飞 <15911185633>
     * @date 2021/3/8
     * @like
     */
    static function BuildAliUrl($url,$timeOut = 1800){
        $time = time() + $timeOut;
        $host = Conf::Ins()->get('app.ali.cdn.host','http://ali.ggvjj.cn');
        $aliKey = Conf::Ins()->get('app.ali.cdn.key','');
        $formData = sprintf('%d-%d-0-',$time,rand(0,999));
        $base = '/'.$url;
        $auth = md5(sprintf('%s-%s%s',$base,$formData,$aliKey));
        return $host.$base.'?auth_key='.$formData.$auth;
    }
}