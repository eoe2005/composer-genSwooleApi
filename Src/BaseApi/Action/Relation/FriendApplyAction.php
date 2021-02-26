<?php
/**
 * 申请添加好友
 * @author 耿鸿飞<genghongfei@soyoung.com>
 * @link
 * @Date: 2021/2/19 22:43
 */


namespace Gen\BaseApi\Action\Relation;


use Gen\BaseApi\Model\UserFriendModel;
use Gen\BaseApi\Service\RelationSvc;
use Gen\Action;
use Gen\App;

class FriendApplyAction extends Action
{
    function checkoutLogin(){
        return true;
    }
    function handle()
    {
        $touid = $this->getParam('touid','required');
        $msg = $this->getParam('msg');

        $status = RelationSvc::FriendStatus($this->uid,$touid);
        if(RelationSvc::IsBlack($touid,$this->uid)){
            return App::Error(500,'你在对方黑名单当中，禁止添加好友');
        }
        switch($status){
            case UserFriendModel::STATUS_APPLY:
                return App::Error(500,'你已经申请添加好友');
            case UserFriendModel::STATUS_FRIEND:
                return App::Error(500,'你们已经是好友');
            case UserFriendModel::STATUS_FRIEND://直接成为好友
                if(RelationSvc::FriendOK($this->uid,$touid,$this->ip)){
                    return "ok";
                }
                return App::Error(500,'系统异常');
            case UserFriendModel::STATUS_NOROW:
                $model = new UserFriendModel();
                if($model->begin(function () use($model,$touid,$msg){
                    if(
                        $model->insert([
                            'uid' => $this->uid,
                            'friend_uid' => $touid,
                            'status' => UserFriendModel::STATUS_APPLY,
                            'msg' => $msg,
                            'create_ip' => $this->ip,
                            'update_ip' => $this->ip
                        ]) &&
                        $model->insert([
                            'uid' => $touid,
                            'friend_uid' => $this->uid,
                            'status' => UserFriendModel::STATUS_WAIT,
                            'msg' => $msg,
                            'create_ip' => $this->ip,
                            'update_ip' => $this->ip
                        ])
                    ){
                        App::Redis()->mset([
                            "friend:".$this->uid.':'.$touid => UserFriendModel::STATUS_APPLY,
                            "friend:".$touid.':'.$this->uid => UserFriendModel::STATUS_WAIT
                        ]);
                    }else{
                        throw new \Exception('系统异常');
                    }
                })){
                    return 'ok';
                }else{
                    return App::Error(500,'系统异常');
                }
                break;
            case UserFriendModel::STATUS_DEL:
                $model = new UserFriendModel();
                if($model->begin(function () use($model,$touid,$msg){
                    if(
                        $model->createQuery()->where([
                            'uid' => $this->uid,
                            'friend_uid' => $touid,
                        ])->update([
                            'status' => UserFriendModel::STATUS_APPLY,
                            'msg' => $msg,
                            'update_ip' => $this->ip
                        ]) &&
                        $model->createQuery()->where([
                            'uid' => $touid,
                            'friend_uid' => $this->uid,
                        ])->update([
                            'status' => UserFriendModel::STATUS_WAIT,
                            'msg' => $msg,
                            'update_ip' => $this->ip
                        ])
                    ){
                        App::Redis()->mset([
                            "friend:".$this->uid.':'.$touid => UserFriendModel::STATUS_APPLY,
                            "friend:".$touid.':'.$this->uid => UserFriendModel::STATUS_WAIT
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
        return 'ok';

    }
}