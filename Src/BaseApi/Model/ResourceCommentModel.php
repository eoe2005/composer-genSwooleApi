<?php
/**
 *
 * @author 耿鸿飞<genghongfei@soyoung.com>
 * @link
 * @Date: 2021/2/19 22:37
 */


namespace Gen\BaseApi\Model;


use Gen\BaseApi\Service\ConfSvc;
use Gen\GDb;
use Gen\Model;

class ResourceCommentModel extends Model
{
    protected $tableName = 'resource_comment';
    const STATUS_NOR = 1;
    const STATUS_DEL = 2;

    function getHotCommentList($targetType,$targetId,$start,$limit = 20){
        $ids = GDb::Ins($this->connectName)
            ->fetchAll("SELECT target_id FROM t_resource_stat WHERE target_type=%d AND target_id IN (SELECT id FROM %s WHERE target_type=%d AND target_id=%d AND pid=0 AND status=%d) ORDER by comments DESC LIMIT %d,%s",
                ConfSvc::RESOURCE_COMMENT,
                $this->getTable(),
                $targetType,
                $targetId,
                ResourceCommentModel::STATUS_NOR,
                $start,$limit
            );
        if($ids){
            $data = $this->findMapByPk(array_column($ids,'target_id'));
            return array_values($data);
        }
        return [];
    }
}