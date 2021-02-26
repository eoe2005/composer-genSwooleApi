<?php


namespace Gen\BaseApi\Action\Resource;


use Gen\BaseApi\Model\ResourceCommentModel;
use Gen\BaseApi\Service\ConfSvc;
use Gen\BaseApi\Service\ResourceSvc;
use Gen\BaseApi\Service\UserInfoSvc;
use Gen\Action;

/**
 * 获取评论列表，或者子评论列表
 * @author 耿鸿飞 <15911185633>
 * @date 2021/2/20
 * @like
 * Class CommentListAction
 * @package Gen\BaseApi\Action\Resource
 */
class CommentListAction extends Action
{

    function handle()
    {
        $start = $this->getParam('start');
        $targetType = $this->getParam('targetType','required|int');
        $targetId = $this->getParam('targetId','required|int');
        $start = intval($start);
        $list = [];
        if($targetType == ConfSvc::RESOURCE_COMMENT){
            $list = (new ResourceCommentModel())->createQuery()->where([
                'target_type' => $targetType,
                'pid' => $targetId
            ])->order('create_at','DESC')
                ->limit($start,20)
                ->getAll();
        }else{
            $list = (new ResourceCommentModel())->getHotCommentList($targetType,$targetId,$start,20);
        }
        $list = UserInfoSvc::AppendUserInfo($list,['uid' => 'userinfo']);
        $list = ResourceSvc::AppendResourceStat(ConfSvc::RESOURCE_COMMENT,$list,'id','stat');
        return $list;
    }
}