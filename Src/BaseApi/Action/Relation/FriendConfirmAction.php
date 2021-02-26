<?php
/**
 * 确认好友
 * @author 耿鸿飞<genghongfei@soyoung.com>
 * @link
 * @Date: 2021/2/19 22:44
 */


namespace Gen\BaseApi\Action\Relation;


use Gen\BaseApi\Model\UserFriendModel;
use Gen\BaseApi\Service\RelationSvc;
use Gen\Action;
use Gen\App;

class FriendConfirmAction extends Action
{
    function checkoutLogin(){
        return true;
    }
    function handle()
    {
        $touid = $this->getParam('touid','required');
        $msg = $this->getParam('msg');
        $nickname = $this->getParam('nickname');
        $act = $this->getParam('act');

        if($act == 'ok'){
            if(RelationSvc::FriendOK($this->uid,$touid,$this->ip)){
                if($nickname){
                    RelationSvc::FriendReName($this->uid,$touid,$nickname,$this->ip);
                }
                return 'ok';
            }
            return App::Error(500,'系统异常');
        }else{
            $model = new UserFriendModel();
            if($model->begin(function () use($model,$touid,$msg){
                if(
                    $model->createQuery()->where([
                        'uid' => $this->uid,
                        'friend_uid' => $touid,
                    ])->update([
                        'status' => UserFriendModel::STATUS_DEL,
                        'update_ip' => $this->ip
                    ]) &&
                    $model->createQuery()->where([
                        'uid' => $touid,
                        'friend_uid' => $this->uid,
                    ])->update([
                        'status' => UserFriendModel::STATUS_DEL,
                        'msg' => $msg,
                        'update_ip' => $this->ip
                    ])
                ){
                    App::Redis()->mset([
                        "friend:".$this->uid.':'.$touid => UserFriendModel::STATUS_DEL,
                        "friend:".$touid.':'.$this->uid => UserFriendModel::STATUS_DEL
                    ]);
                }else{
                    throw new \Exception('系统异常');
                }
            })){
                return 'ok';
            }else{
                return App::Error(500,'系统异常');
            }
        }
    }
}