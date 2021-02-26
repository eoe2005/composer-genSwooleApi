<?php
/**
 * 添加或者删除黑名单
 * @author 耿鸿飞<genghongfei@soyoung.com>
 * @link
 * @Date: 2021/2/19 22:46
 */


namespace Gen\BaseApi\Action\Relation;


use Gen\BaseApi\Model\UserBlackListModel;
use Gen\Action;
use Gen\App;

class BlackListAddAction extends Action
{
    function checkoutLogin(){
        return true;
    }
    function handle()
    {
        $touid = $this->getParam('touid','required');
        $act = $this->getParam('act','required');
        if($act == 'add'){
            $bmodel = new UserBlackListModel();
            $row = $bmodel->createQuery()->where([
                'uid' => $this->uid,
                'black_uid' => $touid
            ])->get();
            if(!$row){
                $bmodel->insert([
                    'uid' => $this->uid,
                    'black_uid' => $touid,
                    'create_ip' => $this->ip,
                    'update_ip' => $this->ip
                ]);
            }
            App::Redis()->set("bl:".$this->uid.':'.$touid,1);
            return 'ok';
        }else{
            $bmodel = new UserBlackListModel();
            $row = $bmodel->createQuery()->where([
                'uid' => $this->uid,
                'black_uid' => $touid
            ])->get();
            if($row){
                $bmodel->createQuery()->where([
                    'uid' => $this->uid,
                    'black_uid' => $touid
                ])->delete();
            }
            App::Redis()->del("bl:".$this->uid.':'.$touid);
            return 'ok';
        }
    }
}