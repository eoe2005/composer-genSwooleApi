<?php


namespace Gen\BaseApi\Action\Msg;


use Gen\BaseApi\Model\ChatMsgModel;
use Gen\BaseApi\Service\UserInfoSvc;
use Gen\Action;

/**
 * 消息列表
 * @author 耿鸿飞 <15911185633>
 * @date 2021/2/20
 * @like
 * Class ChatMsgListAction
 * @package Gen\BaseApi\Action\Msg
 */
class ChatMsgListAction extends Action
{

    function checkoutLogin(){
        return true;
    }
    function handle()
    {
        $touid = $this->getParam('touid','required');
        $act = $this->getParam('act');
        $id = intval($this->getParam('id'));

        $uk = sprintf('%d-%d',min($this->uid,$touid),max($this->uid,$touid));
        $list = [];
        if($act == 'old'){
            $list = (new ChatMsgModel())->createQuery()->where(['ukey' => $uk])
                ->where('id','<',$id)
                ->limit(100)->order('id','DESC');
            $list = array_reverse($list);
        }else{
            if($id > 0){
                $list = (new ChatMsgModel())->createQuery()->where(['ukey' => $uk])
                    ->where('id','>',$id)
                    ->limit(100)->order('id','ASC');
            }else{
                $list = (new ChatMsgModel())->createQuery()->where(['ukey' => $uk])->limit(100)->order('id','DESC');
                $list = array_reverse($list);
            }
        }

        return UserInfoSvc::AppendUserInfo($list,[
            'from_uid' => 'from_userinfo',
            'to_uid' => 'to_userinfo'
        ]);

    }
}