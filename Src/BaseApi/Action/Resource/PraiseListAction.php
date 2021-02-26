<?php


namespace Gen\BaseApi\Action\Resource;


use Gen\BaseApi\Model\ResourcePraiseModel;
use Gen\BaseApi\Service\UserInfoSvc;
use Gen\Action;

/**
 * 点赞列表
 * @author 耿鸿飞 <15911185633>
 * @date 2021/2/20
 * @like
 * Class PraiseListAction
 * @package Gen\BaseApi\Action\Resource
 */
class PraiseListAction extends Action
{

    function handle()
    {
        $start = $this->getParam('start');
        $targetType = $this->getParam('targetType','required|int');
        $targetId = $this->getParam('targetId','required|int');

        $start = intval($start);
        $list = (new ResourcePraiseModel())->createQuery()
            ->where([
                'target_type' => $targetType,
                'target_id' => $targetId,])->limit(100,$start)
            ->getAll("uid,create_at");
        return UserInfoSvc::AppendUserInfo($list,['uid' => 'userinfo']);
    }
}