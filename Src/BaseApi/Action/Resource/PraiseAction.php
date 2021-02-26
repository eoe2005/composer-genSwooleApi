<?php


namespace Gen\BaseApi\Action\Resource;


use Gen\BaseApi\Model\ResourcePraiseModel;
use Gen\BaseApi\Service\ResourceSvc;
use Gen\Action;
use Gen\App;

/**
 * 点赞
 * @author 耿鸿飞 <15911185633>
 * @date 2021/2/20
 * @like
 * Class PraiseAction
 * @package Gen\BaseApi\Action\Resource
 */
class PraiseAction extends Action
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
            $id = ResourceSvc::IsPraise($this->uid,$targetType,$targetId);
            if($id){
                return "ok";
            }

            if((new ResourcePraiseModel())->insert([
                'target_type' => $targetType,
                'uid' => $this->uid,
                'target_id' => $targetId,
                'create_ip' => $this->ip,
                'update_ip' => $this->ip
            ])){
                App::Redis()->del('praise:'.$this->uid,':'.$targetType.':'.$targetId);
                ResourceSvc::UpdateStat($targetType,$targetId,'praises','+1');
                return "ok";
            }else{
                return App::Error(500,'系统异常');
            }
        }else{
            $id = ResourceSvc::IsPraise($this->uid,$targetType,$targetId);
            if(!$id){
                return "ok";
            }
            if((new ResourcePraiseModel())->delete($id)){
                App::Redis()->del('praise:'.$this->uid,':'.$targetType.':'.$targetId);
                ResourceSvc::UpdateStat($targetType,$targetId,'praises','-1');
                return "ok";
            }else{
                return App::Error(500,'系统异常');
            }
        }

    }
}