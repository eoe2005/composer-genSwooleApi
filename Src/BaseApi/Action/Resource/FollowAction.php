<?php


namespace Gen\BaseApi\Action\Resource;


use Gen\BaseApi\Model\ResourceFollowModel;
use Gen\BaseApi\Service\ResourceSvc;
use Gen\Action;

/**
 * 关注
 * @author 耿鸿飞 <15911185633>
 * @date 2021/2/20
 * @like
 * Class FollowAction
 * @package Gen\BaseApi\Action\Resource
 */
class FollowAction extends Action
{
    function checkoutLogin(){
        return true;
    }
    function handle()
    {
        $targetType = $this->getParam('targetType','required|int');
        $targetId = $this->getParam('targetId','required|int');
        $act = $this->getParam('act');

        if($act == 'praise'){
            $id = ResourceSvc::IsFollow($this->uid,$targetType,$targetId);
            if($id){
                return "ok";
            }

            if((new ResourceFollowModel())->insert([
                'target_type' => $targetType,
                'uid' => $this->uid,
                'target_id' => $targetId,
                'create_ip' => $this->ip,
                'update_ip' => $this->ip
            ])){
                App::Redis()->del('follows:'.$this->uid,':'.$targetType.':'.$targetId);
                ResourceSvc::UpdateStat($targetType,$targetId,'follows','+1');
                return "ok";
            }else{
                return App::Error(500,'系统异常');
            }
        }else{
            $id = ResourceSvc::IsFollow($this->uid,$targetType,$targetId);
            if(!$id){
                return "ok";
            }
            if((new ResourceFollowModel())->delete($id)){
                App::Redis()->del('follows:'.$this->uid,':'.$targetType.':'.$targetId);
                ResourceSvc::UpdateStat($targetType,$targetId,'follows','-1');
                return "ok";
            }else{
                return App::Error(500,'系统异常');
            }
        }
    }
}