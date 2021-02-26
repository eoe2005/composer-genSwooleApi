<?php
/**
 * 删除好友
 * @author 耿鸿飞<genghongfei@soyoung.com>
 * @link
 * @Date: 2021/2/19 22:44
 */


namespace Gen\BaseApi\Action\Relation;


use Gen\BaseApi\Model\UserFriendModel;
use Gen\Action;
use Gen\App;

class FriendDelAction extends Action
{
    function checkoutLogin(){
        return true;
    }
    function handle()
    {
        $touid = $this->getParam('touid','required');
        $model = new UserFriendModel();
        if($model->begin(function () use($model,$touid){
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