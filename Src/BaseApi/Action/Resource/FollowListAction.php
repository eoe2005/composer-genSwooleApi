<?php


namespace Gen\BaseApi\Action\Resource;


use Gen\BaseApi\Model\ResourceFollowModel;
use Gen\BaseApi\Service\UserInfoSvc;
use Gen\Action;

/**
 * 关注列表
 * @author 耿鸿飞 <15911185633>
 * @date 2021/2/20
 * @like
 * Class FollowListAction
 * @package Gen\BaseApi\Action\Resource
 */
class FollowListAction extends Action
{

    function handle()
    {
        $start = $this->getParam('start');
        $targetType = $this->getParam('targetType','required|int');
        $targetId = $this->getParam('targetId','required|int');

        $start = intval($start);
        $list = (new ResourceFollowModel())->createQuery()
            ->where([
                'target_type' => $targetType,
                'target_id' => $targetId,])->limit(100,$start)
            ->getAll("uid,create_at");
        return UserInfoSvc::AppendUserInfo($list,['uid' => 'userinfo']);
    }
}