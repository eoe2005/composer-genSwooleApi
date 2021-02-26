<?php
/**
 * 修好好友名字
 * @author 耿鸿飞<genghongfei@soyoung.com>
 * @link
 * @Date: 2021/2/19 22:45
 */


namespace Gen\BaseApi\Action\Relation;


use Gen\BaseApi\Service\RelationSvc;
use Gen\Action;
use Gen\App;

class FriendMarkAction extends Action
{
    function checkoutLogin(){
        return true;
    }
    function handle()
    {
        $touid = $this->getParam('touid','required');
        $nickname = $this->getParam('nickname');

        if(RelationSvc::FriendReName($this->uid,$touid,$nickname,$this->ip)){
            return 'ok';
        }
        return App::Error(500,'修改失败');
    }
}