<?php


namespace Gen\BaseApi\Action\Resource;


use Gen\BaseApi\Model\ResourceScoreModel;
use Gen\BaseApi\Service\UserInfoSvc;
use Gen\Action;

/**
 * 评分列表
 * @author 耿鸿飞 <15911185633>
 * @date 2021/2/20
 * @like
 * Class ScoreListAction
 * @package Gen\BaseApi\Action\Resource
 */
class ScoreListAction extends Action
{

    function handle()
    {
        $start = $this->getParam('start');
        $targetType = $this->getParam('targetType','required|int');
        $targetId = $this->getParam('targetId','required|int');

        $start = intval($start);
        $list = (new ResourceScoreModel())->createQuery()
            ->where([
                'target_type' => $targetType,
                'target_id' => $targetId,])->limit(100,$start)
            ->getAll("uid,score,create_at");
        return UserInfoSvc::AppendUserInfo($list,['uid' => 'userinfo']);
    }
}