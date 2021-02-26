<?php


namespace Gen\BaseApi\Service;


use Gen\BaseApi\Model\ResourceFollowModel;
use Gen\BaseApi\Model\ResourcePraiseModel;
use Gen\BaseApi\Model\ResourceScoreModel;
use Gen\BaseApi\Model\ResourceStatModel;
use Gen\App;

class ResourceSvc
{
    /**
     * 是否赞过
     * @param $uid
     * @param $targetType
     * @param $targetId
     * @return mixed
     * @author 耿鸿飞 <15911185633>
     * @date 2021/2/20
     * @like
     */
    static function IsPraise($uid,$targetType,$targetId){
        return App::GetCache('praise:'.$uid,':'.$targetType.':'.$targetId,function ()use($uid,$targetType,$targetId) {
            $row = (new ResourcePraiseModel())->createQuery()->where([
                'uid' => $this->uid,
                'target_type' => $targetType,
                'target_id' => $targetId
            ])->get('id');
            return $row['id'] ?? 0;
        });
    }

    /**
     * 是否关注
     * @param $uid
     * @param $targetType
     * @param $targetId
     * @return mixed
     * @author 耿鸿飞 <15911185633>
     * @date 2021/2/20
     * @like
     */
    static function IsFollow($uid,$targetType,$targetId){
        return App::GetCache('follow:'.$uid,':'.$targetType.':'.$targetId,function ()use($uid,$targetType,$targetId) {
            $row = (new ResourceFollowModel())->createQuery()->where([
                'uid' => $this->uid,
                'target_type' => $targetType,
                'target_id' => $targetId
            ])->get('id');
            return $row['id'] ?? 0;
        });
    }

    /**
     * 是否评分
     * @param $uid
     * @param $targetType
     * @param $targetId
     * @return mixed
     * @author 耿鸿飞 <15911185633>
     * @date 2021/2/20
     * @like
     */
    static function IsScore($uid,$targetType,$targetId){
        return App::GetCache('score:'.$uid,':'.$targetType.':'.$targetId,function ()use($uid,$targetType,$targetId) {
            $row = (new ResourceScoreModel())->createQuery()->where([
                'uid' => $this->uid,
                'target_type' => $targetType,
                'target_id' => $targetId
            ])->get('id');
            return $row['id'] ?? 0;
        });
    }
    static function GetStat($targetType,$targetId){
        return App::GetCache('r:stat:'.$targetType.':'.$targetId,function ()use($targetType,$targetId) {
            $row = (new ResourceStatModel())->createQuery()->where([
                'target_type' => $targetType,
                'target_id' => $targetId
            ])->get('praises,comments,replays,follows,score');
            if($row){
                return $row;
            }
            return [
                'praises' => 0,
                'comments' => 0,
                'replays' => 0,
                'follows' => 0,
                'score' => 0,
            ];
        });
    }
    static function UpdateStat($targetType,$targetId,$k ,$v){
        App::Redis()->del('r:stat:'.$targetType.':'.$targetId);
        if(
            (new ResourceStatModel())->createQuery()->where([
                'target_type' => $targetType,
                'target_id' => $targetId
            ])->update([$k => $v])
        ){
            return;
        }
        return (new ResourceStatModel())->insert([
            'target_type' => $targetType,
            'target_id' => $targetId,
            $k => 1
        ]);
    }

    /**
     * 添加统计汇总信息
     * @param $targetType
     * @param array $list
     * @param $userKey
     * @param string $showKey
     * @return array
     * @author 耿鸿飞 <15911185633>
     * @date 2021/2/20
     * @like
     */
    static function AppendResourceStat($targetType,array $list,$userKey,$showKey = 'status'){
        $ids = array_column($list,$userKey);
        if($ids){
            $dataMap = ToolsSvc::GetAllKey($ids,'r:stat:'.$targetType.':');
            foreach ($list as &$item){
                $item[$showKey] = $dataMap[$item[$userKey]] ?? [];
            }
        }
        return $list;
    }


}