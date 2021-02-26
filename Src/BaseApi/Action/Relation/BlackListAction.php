<?php
/**
 * 黑名单列表
 * @author 耿鸿飞<genghongfei@soyoung.com>
 * @link
 * @Date: 2021/2/19 22:45
 */


namespace Gen\BaseApi\Action\Relation;


use Gen\BaseApi\Model\UserBlackListModel;
use Gen\BaseApi\Service\UserInfoSvc;
use Gen\Action;

class BlackListAction extends Action
{
    function checkoutLogin(){
        return true;
    }
    function handle()
    {
        $start = $this->getParam('start');
        $start = intval($start);
        $list = (new UserBlackListModel())->createQuery()
            ->where(['uid' => $this->uid])->limit(100,$start)
            ->getAll("black_uid,create_at");
        return UserInfoSvc::AppendUserInfo($list,['black_uid' => 'userinfo']);
    }
}