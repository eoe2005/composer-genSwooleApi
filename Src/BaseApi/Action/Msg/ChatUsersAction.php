<?php

namespace Gen\BaseApi\Action\Msg;


use Gen\BaseApi\Model\ChatUserModel;
use Gen\BaseApi\Service\UserInfoSvc;
use Gen\Action;
use Gen\App;

/**
 * 获取消息列表
 * @author 耿鸿飞 <15911185633>
 * @date 2021/2/20
 * @like
 * Class ChatUsersAction
 * @package Gen\BaseApi\Action\Msg
 */
class ChatUsersAction extends Action
{
    function checkoutLogin(){
        return true;
    }
    function apiLimitTime(){
        return 1;
    }
    function handle()
    {
        $lastTime = intval(App::Redis()->getSet('chat:lastime:'.$this->uid,time()));
        $list = (new ChatUserModel())->createQuery()
            ->where(['uid' => $this->uid])
            ->where(['last_time' , '>' , $lastTime])
            ->order('last_time','DESC')
            ->getAll('to_uid,last_uid,last_msg,last_time,ubn_read_msgs');

        return UserInfoSvc::AppendUserInfo($list,[
            'to_uid' => 'userinfo',
            'last_uid' => 'lastUserinfo'
        ]);

    }
}