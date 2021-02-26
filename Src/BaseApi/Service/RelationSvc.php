<?php


namespace Gen\BaseApi\Service;


use Gen\BaseApi\Model\UserBlackListModel;
use Gen\BaseApi\Model\UserFriendModel;
use Gen\App;

class RelationSvc
{
    /**
     * 是否在黑名单中
     * @param $uid
     * @param $touid
     * @return bool
     * @author 耿鸿飞 <15911185633>
     * @date 2021/2/20
     * @like
     */
    static function IsBlack($uid,$touid){
        $row = App::GetCache("bl:".$uid.':'.$touid,function ()use($uid,$touid){
            $row = (new UserBlackListModel())->createQuery()->where([
                'uid' => $this->uid,
                'black_uid' => $touid
            ])->get();
            if($row){
                return 1;
            }
            return 0;
        });
        return $row == 1;
    }

    /**
     * 是否是好友状态
     * @param $uid
     * @param $touid
     * @return bool
     * @author 耿鸿飞 <15911185633>
     * @date 2021/2/20
     * @like
     */
    static function FriendStatus($uid,$touid){
        $stat = App::GetCache("friend:".$uid.':'.$touid,function ()use($uid,$touid){
            $row = (new UserFriendModel())->createQuery()->where([
                'uid' => $this->uid,
                'friend_uid' => $touid,
                'status' => UserFriendModel::STATUS_FRIEND
            ])->get();
            return $row['status'] ?? UserFriendModel::STATUS_NOROW;
        });
        return $stat;
    }

    /**
     * 确认成为好友
     * @param $uid
     * @param $toUid
     * @param $ip
     * @return bool
     * @author 耿鸿飞 <15911185633>
     * @date 2021/2/20
     * @like
     */
    static function FriendOK($uid,$toUid,$ip){
        $model = new UserFriendModel();
        return $model->begin(function()use($model,$uid,$toUid,$ip){
            if(
                $model->createQuery()->where(['uid' => $uid,'friend_uid' => $toUid])
                ->update(['status' => UserFriendModel::STATUS_FRIEND,'update_ip' => $ip])
                &&
                $model->createQuery()->where(['uid' => $toUid,'friend_uid' => $uid])
                    ->update(['status' => UserFriendModel::STATUS_FRIEND,'update_ip' => $ip])
            ){
                App::Redis()->mset([
                    "friend:".$uid.':'.$toUid => UserFriendModel::STATUS_FRIEND,
                    "friend:".$toUid.':'.$uid => UserFriendModel::STATUS_FRIEND
                ]);

                return true;
            }
            throw new \Exception("确认好友失败");
        });
    }

    /**
     * 修改好友名字
     * @param $uid
     * @param $touid
     * @param $nickname
     * @param $ip
     * @return mixed
     * @author 耿鸿飞 <15911185633>
     * @date 2021/2/20
     * @like
     */
    static function FriendReName($uid,$touid,$nickname,$ip){
        return (new UserFriendModel())->createQuery()
            ->where(['uid' => $uid,'friend_uid' => $touid])
            ->update(['nickname' => $nickname,'update_ip' => $ip]);
    }
}