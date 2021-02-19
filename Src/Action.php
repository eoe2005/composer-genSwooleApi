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

}