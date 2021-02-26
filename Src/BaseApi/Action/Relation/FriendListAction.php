<?php
/**
 * 好友列表
 * @author 耿鸿飞<genghongfei@soyoung.com>
 * @link
 * @Date: 2021/2/19 22:42
 */


namespace Gen\BaseApi\Action\Relation;


use Gen\BaseApi\Model\UserFriendModel;
use Gen\BaseApi\Service\UserInfoSvc;
use Gen\Action;

class FriendListAction extends Action
{
    function checkoutLogin(){
        return true;
    }
    function handle()
    {
        $start = $this->getParam('start');
        $start = intval($start);

        $list = (new UserFriendModel())->createQuery()
            ->where([
                'uid' => $this->uid,
            ])->where(['status','in',[UserFriendModel::STATUS_WAIT,UserFriendModel::STATUS_FRIEND]])
            ->limit(100,$start)
            ->getAll("friend_uid,status,msg,nickname,create_at");
        return UserInfoSvc::AppendUserInfo($list,['friend_uid' => 'userinfo']);
    }
}