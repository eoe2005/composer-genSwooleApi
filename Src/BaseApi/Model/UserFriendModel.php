<?php
/**
 *
 * @author 耿鸿飞<genghongfei@soyoung.com>
 * @link
 * @Date: 2021/2/19 22:32
 */


namespace Gen\BaseApi\Model;


namespace Gen\BaseApi\Model;

class UserFriendModel extends Model
{
    const STATUS_APPLY = 1;
    const STATUS_FRIEND = 3;
    const STATUS_WAIT = 2;
    const STATUS_DEL = 4;
    const STATUS_NOROW = 0;
    protected $tableName = 'user_friend';
}