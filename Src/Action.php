<?php


namespace Gen;


abstract class Action
{

    private $_new_uid = 0;


    protected $uid;
    protected $params;
    protected $ip;
    abstract function handle();

    function apiLimitTime(){
        return 1;
    }
    function checkoutLogin(){
        return false;
    }

    public function execute($uid,$params,$ip){
        if($this->checkoutLogin() && !$uid){
            return App::Error(405,'账号没有登录');
        }
        if($uid && !App::ReqLimit(__CLASS__.$uid,$this->apiLimitTime())){
            return App::Error(403,'你的请求过快');
        }
        $this->uid = $uid;
        $this->params = $params;
        $this->ip = $ip;
        return $this->handle();
    }
    protected function setUid($uid){
        $this->_new_uid = $uid;
    }

    function getNewUid(){
        return $this->_new_uid;
    }

    protected function getParam($key,$rouleString = "",$error = ""){
        $ret = $this->params['data'][$key] ?? "";
        if(!$rouleString){
            return $ret;
        }

        $rules = explode("|",$rouleString);
        foreach ($rules as $k){
            $isError = false;
            switch ($k){
                case 'required':
                    if(!$ret || !isset($this->params['data'][$key])){
                        $isError = true;
                    }
                    break;
                case "int":
                    if(!preg_match("/\d+/",$ret)){
                        $isError = true;
                    }
                    break;
                case 'mobile':
                    if(!preg_match("/1(35789)\d{9}/",$ret)){
                        $isError = true;
                    }
                    break;
                case 'email':
                    if(!preg_match("/(.)+@.+\..{2}/",$ret)){
                        $isError = true;
                    }
                    break;
                case 'date':
                    if(!preg_match("/\d{4}-\d{2}-\d{2}/",$ret)){
                        $isError = true;
                    }
                    break;
                case 'datetime':
                    if(!preg_match("/\d{4}-\d{2}-\d{2} (0|1)\d:(0-5)\d:(0-5)\d/",$ret)){
                        $isError = true;
                    }
                    break;
                case 'time':
                    if(!preg_match("/(0|1)\d:(0-5)\d:(0-5)\d/",$ret)){
                        $isError = true;
                    }
                    break;
            }
            if($isError){
                throw new \Exception("参数错误",303);
            }
        }
        return $ret;
    }

}