<?php


namespace Gen\BaseApi\Action\Msg;


use Gen\BaseApi\Model\ChatMsgModel;
use Gen\BaseApi\Model\ChatUserModel;
use Gen\Action;
use Gen\App;

/**
 * 发送消息
 * @author 耿鸿飞 <15911185633>
 * @date 2021/2/20
 * @like
 * Class ChatSendMsgAction
 * @package Gen\BaseApi\Action\Msg
 */
class ChatSendMsgAction extends Action
{
    function checkoutLogin(){
        return true;
    }
    function handle()
    {
        $touid = $this->getParam('touid','required');
        $msg = $this->getParam('msg','required');

        $model = new ChatMsgModel();

        $ret = $model->begin(function() use($model,$touid,$msg){
            $time = time();
            $uk = sprintf('%d-%d',min($this->uid,$touid),max($this->uid,$touid));
            if(
                $model->insert([
                    'ukey' => $uk,
                    'from_uid' => $this->uid,
                    'to_uid' => $touid,
                    'msg' => $msg,
                    'is_read' => ChatMsgModel::READ_WAIT,
                    'create_ip' => $this->ip,
                    'update_ip' => $this->ip
                ]) && (
                    (new ChatUserModel())->createQuery()
                    ->where([
                        'uid' => $this->uid,
                        'to_uid' => $touid
                    ])->update([
                        'last_uid' => $this->uid,
                            'last_time' => $time,
                            'last_msg' => mb_substr($msg,0,20)
                        ])
                    || (new ChatUserModel())->insert([
                        'uid' => $this->uid,
                        'to_uid' => $touid,
                        'last_uid' => $this->uid,
                        'last_time' => $time,
                        'last_msg' => mb_substr($msg,0,20)
                    ])
                ) && (
                    (new ChatUserModel())->createQuery()
                        ->where([
                            'uid' => $touid,
                            'to_uid' => $this->uid
                        ])->update([
                            'last_uid' => $this->uid,
                            'last_time' => $time,
                            'ubn_read_msgs' => '+1',
                            'last_msg' => mb_substr($msg,0,20)
                        ])
                    || (new ChatUserModel())->insert([
                        'uid' => $touid,
                        'to_uid' => $this->uid,
                        'last_uid' => $this->uid,
                        'last_time' => $time,
                        'ubn_read_msgs' => '+1',
                        'last_msg' => mb_substr($msg,0,20)
                    ])
                )
            ){
                return ;
            }
            throw new \Exception('系统异常');
        });

        if($ret){
            return 'ok';
        }
        return App::Error(500,'系统异常');

    }
}