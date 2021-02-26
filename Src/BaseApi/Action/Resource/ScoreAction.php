<?php


namespace Gen\BaseApi\Action\Resource;


use Gen\BaseApi\Model\ResourceScoreModel;
use Gen\BaseApi\Service\ResourceSvc;
use Gen\Action;

/**
 * 评分
 * @author 耿鸿飞 <15911185633>
 * @date 2021/2/20
 * @like
 * Class ScoreAction
 * @package Gen\BaseApi\Action\Resource
 */
class ScoreAction extends Action
{
    function checkoutLogin(){
        return true;
    }
    function handle()
    {
        $targetType = $this->getParam('targetType','required|int');
        $targetId = $this->getParam('targetId','required|int');
        $score = $this->getParam('score',"required|int");

        $id = ResourceSvc::IsScore($this->uid,$targetType,$targetId);

        if($id){
            return 'ok';
        }
        if((new ResourceScoreModel())->insert([
            'target_type' => $targetType,
            'uid' => $this->uid,
            'target_id' => $targetId,
            'score' => $score,
            'create_ip' => $this->ip,
            'update_ip' => $this->ip
        ])){
            $row = (new ResourceScoreModel())->createQuery()->where([
                'target_type' => $targetType,
                'target_id' => $targetId,
            ])->get('sum(score)/count(*) as num');
            $avg = intval($row['num'] ?? 50);
            ResourceSvc::UpdateStat($targetType,$targetId,'score',$avg);
            return 'ok';
        }
        return App::Error(500,'系统异常');
    }
}