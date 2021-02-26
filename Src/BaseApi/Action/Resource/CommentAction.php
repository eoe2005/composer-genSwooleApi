<?php


namespace Gen\BaseApi\Action\Resource;


use Gen\BaseApi\Model\ResourceCommentModel;
use Gen\BaseApi\Service\ConfSvc;
use Gen\BaseApi\Service\ResourceSvc;
use Gen\Action;
use Gen\App;

/**
 * 评论
 * @author 耿鸿飞 <15911185633>
 * @date 2021/2/20
 * @like
 * Class CommentAction
 * @package Gen\BaseApi\Action\Resource
 */
class CommentAction extends Action
{
    function checkoutLogin(){
        return true;
    }
    function handle()
    {
        $targetType = $this->getParam('targetType','required|int');
        $targetId = $this->getParam('targetId','required|int');
        $content = $this->getParam('content',"required");
        $pid = intval($this->getParam('pid'));

        $id = (new ResourceCommentModel())->insert([
            'target_type' => $targetType,
            'uid' => $this->uid,
            'target_id' => $targetId,
            'content' => $content,
            'pid' => $pid,
            'status' => ResourceCommentModel::STATUS_NOR,
            'create_ip' => $this->ip,
            'update_ip' => $this->ip
        ]);
        if($id){
            ResourceSvc::UpdateStat($targetType,$targetId,'comments','+1');
            if($pid){
                ResourceSvc::UpdateStat(ConfSvc::RESOURCE_COMMENT,$pid,'comments','+1');
            }
            return 'ok';
        }
        return App::Error(500,'系统异常');
    }
}