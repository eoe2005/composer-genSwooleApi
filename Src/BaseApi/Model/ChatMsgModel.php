<?php
/**
 *
 * @author 耿鸿飞<genghongfei@soyoung.com>
 * @link
 * @Date: 2021/2/19 22:40
 */


namespace Gen\BaseApi\Model;


use Gen\Model;

class ChatMsgModel extends Model
{
    const READ_OK = 1;//已读
    const READ_WAIT = 0;//未读
    protected $tableName = 'chat_msg';
}