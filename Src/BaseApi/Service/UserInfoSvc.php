<?php
/**
 *
 * @author 耿鸿飞<genghongfei@soyoung.com>
 * @link
 * @Date: 2021/2/19 22:47
 */


namespace Gen\BaseApi\Service;


use Gen\BaseApi\Model\UserInfoModel;
use Gen\App;

class UserInfoSvc
{
    /**
     * 添加用户的基本资料
     * @param array $list
     * @param array $opt
     * @return array
     * @author 耿鸿飞 <15911185633>
     * @date 2021/2/20
     * @like
     */
    static function AppendUserInfo(array $list,array $opt){
        $uids = [];
        foreach ($opt as $k => $v){
            $uids = array_merge($uids,array_column($list,$k));
        }
        $uids = array_unique($uids);
        $useInfoMap = [];
        if($uids){
            $redis = App::Redis();
            $userList = $redis->mget($uids);
            $len = count($uids);
            $unFindUids = [];
            for($i = 0 ; $i < $len ; $i++){
                $uf = $userList[$i];
                if(!$uf){
                    $uf = [];
                    $unFindUids[] = $uids[$i];
                }
                $useInfoMap[$uids[$i]] = $uf;
            }
            if($unFindUids){
                $dbUserInfo = (new UserInfoModel())->findMapByPk($unFindUids);
                foreach ($dbUserInfo as $u => $v){
                    $useInfoMap[$u] = $v;
                }
                $redis->mset($dbUserInfo);
            }
        }
        foreach($list as &$item){
            foreach($opt as $k => $v){
                $item[$v] = $useInfoMap[$item[$k]] ?? [];
            }
        }
        return $list;
    }
}