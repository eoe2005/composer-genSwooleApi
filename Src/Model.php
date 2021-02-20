<?php


namespace Gen;


/**
 * 数据Model
 * @author 耿鸿飞 <15911185633>
 * @date 2020/11/10
 * @like
 * Class GModel
 */
class Model
{
    private $_table_pre = '';
    protected $connectName = 'default';
    protected $tableName = '';
    protected $pk = 'id';
    function __construct(){
        $this->_table_pre = Conf::Ins()->get(sprintf('mysql.%s.tablePrefix',$this->connectName),'');
    }

    function getTable(){
        return $this->_table_pre . $this->tableName;
    }



    /**
     * 查询记录
     * @auth 耿鸿飞 <15911185633>
     * @date 2020/8/24 19:30
     * @param $id
     * @param string $key
     * @return mixed
     */
    public function find($id,$key = ''){
        return $this->createQuery()->where($key ?: $this->pk,$id)->get();
    }

    /**
     * 查询列表
     * @auth 耿鸿飞 <15911185633>
     * @date 2020/8/24 19:41
     * @param $ids
     * @param string $key
     * @return array
     */
    public function findMapByPk($ids,$key = ''){
        $list = $this->createQuery()->where($key ?: $this->pk,'in',$ids)->getAll();
        if(!$list){
            return [];
        }
        return array_column($list,null,$key ?: $this->pk);
    }

    /**
     * 删除记录
     * @auth 耿鸿飞 <15911185633>
     * @date 2020/8/24 19:29
     * @param $id
     * @param string $key
     * @return int
     */
    public function delete($id,$key = ''){
        return $this->createQuery()->where($key ?: $this->pk,$id)->delete();
    }


    /**
     * 更新记录
     * @auth 耿鸿飞 <15911185633>
     * @date 2020/8/24 19:29
     * @param $id
     * @param $data
     * @param string $key
     * @return int
     */
    public function updateByPk($id,$data,$key = ''){
        return $this->createQuery()->where($key ?: $this->pk,$id)->update($data);
    }

    /**
     * 出埃及查询
     * @auth 耿鸿飞 <15911185633>
     * @date 2020/8/24 19:41
     * @return DbQuery
     */
    public function createQuery(){
        return new DbQuery(GDb::Ins($this->connectName),$this->getTable());
    }

    public function begin($func){
        return GDb::Ins($this->connectName)->begin($func);
    }

    /**
     * 插入数据
     * @auth 耿鸿飞 <15911185633>
     * @date 2020/8/25 14:18
     * @param $data
     * @return int|string
     */
    public function insert($data){
        $keys = [];
        $args = [];
        foreach ($data as $k=>$v){
            $key = ':'.$k;
            $keys[] = $key;
            $args[$key] = $v;
        }
        $sql = sprintf('INSERT INTO `%s`(`%s`) VALUES(%s)',$this->getTable(),implode('`,`',array_keys($data)),implode(',',$keys));
        return GDb::Ins($this->connectName)->insert($sql,$args);
    }
}

/**
 * 查询器
 * @author 耿鸿飞 <15911185633>
 * @date 2020/11/10
 * @like
 * Class DbQuery
 */
class DbQuery
{
    private $tableName;
    private $where = '1=1';
    private $limit = '';
    private $args = [];
    private $order = '';
    private $group = '';
    private $having = '';
    private $db;
    private $index = 0;
    public function __construct(GDb $db,$tableName)
    {
        $this->tableName = $tableName;
        $this->db = $db;
    }

    public function where($k){
        $args = func_get_args();
        if(!$this->where){
            $this->where = $this->buildWhere(...$args);
        }else{
            $this->where .= ' AND '.$this->buildWhere(...$args);
        }
        return $this;
    }
    public function orWhere($k){
        $args = func_get_args();
        if(!$this->where){
            $this->where = $this->buildWhere(...$args);
        }else{
            $this->where .= sprintf("(%s) OR (%s)",$this->where,$this->buildWhere(...$args));
        }
        return $this;
    }
    public function order($key,$sort = 'ASC'){
        if(!$this->order){
            $this->order = sprintf('ORDER BY `%s` %s',$key,$sort);
        }else{
            $this->order .= sprintf(',`%s` %s',$key,$sort);
        }
        return $this;
    }

    public function limit($size,$offset = 0){
        $this->limit = sprintf('LIMIT %d,%d',$offset,$size);
        return $this;
    }
    public function get($select = '*'){
        $sql = sprintf('SELECT %s FROM `%s` WHERE %s %s %s %s',
            is_string($select) ? $select : implode(',',$select),
            $this->tableName,
            $this->where,
            $this->group,
            $this->having,
            $this->order,
            'LIMIT 1');
        $st = $this->db->query($sql,$this->args);
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        $st->closeCursor();
        return $row;
    }
    public function getAll($select = '*'){
        $sql = sprintf('SELECT %s FROM `%s` WHERE %s %s %s %s %s',
            is_string($select) ? $select : implode(',',$select),
            $this->tableName,
            $this->where,
            $this->group,
            $this->having,
            $this->order,
            $this->limit);
        $st = $this->db->query($sql,$this->args);
        $list = $st->fetchAll(\PDO::FETCH_ASSOC);
        $st->closeCursor();
        return $list;
    }
    public function delete(){
        if(!$this->where){
            throw new Exception('没有WHERE，禁止删除',1001);
        }
        $sql = sprintf('DELETE FROM `%s` WHERE %s',$this->tableName,$this->where);
        $st = $this->db->query($sql,$this->args);
        return $st->rowCount();
    }
    public function update($data){
        if(!$this->where){
            throw new Exception('没有WHERE，禁止更新',1001);
        }
        $sets = [];
        foreach ($data as $k => $v){
            if(strstr($v,'-') === 0){
                $sets[] = sprintf('`%s`=`%s` - %s',$k,$this->buildKey($k,substr($v,1)));
            }elseif(strstr($v,'+') === 0){
                $sets[] = sprintf('`%s`=`%s` + %s',$k,$this->buildKey($k,substr($v,1)));
            }else{
                $sets[] = sprintf('`%s`=>%s',$k,$this->buildKey($k,$v));
            }

        }
        $sql = sprintf('UPDATE `%s` SET %s WHERE %s %s %s',$this->tableName,implode(',',$sets),$this->where,$this->order,$this->limit);
        $st = $this->db->query($sql,$this->args);
        return $st->rowCount();
    }



    private function buildKey($k,$v){
        $k = ':'.$k.$this->index;
        $this->index += 1;
        $this->args[$k] = $v;
        return $k;
    }

    private function buildWhere(){
        $args = func_get_args();
        $len = count($args);
        $ret = [];
        switch ($len){
            case 3:
                $args[1] = strtolower($args[1]);
                if($args[1] == 'in'){
                    $ret[] = sprintf("`%s` IN ('%s')",$args[0],implode("','",$args[2]));
                }elseif($args[1] == 'like'){
                    $ret[] = sprintf('`%s` LIKE %%%s%%',$args[0],$args[2]);
                }else{
                    $ret[] = sprintf('`%s`%s%s',$args[0],$args[1],$this->buildKey($args[0],$args[2]));
                }
                break;
            case 2:
                $args[1] = strtolower($args[1]);
                $ret[] = sprintf('`%s`=%s',$args[0],$this->buildKey($args[0],$args[1]));
                break;
            case 1:
                $args = $args[0];
                foreach($args as $k => $v){
                    if(strtolower($k) == 'or'){
                        !is_array($v) && Error::errorMsg(1202,'WHERE 参数错误');
                        $ret[] = str_replace(' AND ',' OR ',$this->buildWhere(...$v));
                    }elseif(strtolower($k) == 'and'){
                        !is_array($v) && Error::errorMsg(1202,'WHERE 参数错误');
                        $ret[] = $this->buildWhere(...$v);
                    }elseif(!is_numeric($k)){
                        $ret[] = sprintf("`%s`=%s",$k,$this->buildKey($k,$v));
                    }elseif(is_array($v)){
                        $ret[] = $this->buildWhere(...$v);
                    }else{
                        $len = count($args);
                        if($len == 2){
                            $ret[] = sprintf("`%s`=%s",$args[0],$this->buildKey(...$args));
                        }elseif($len == 3){
                            if($args[1] == 'in'){
                                $ret[] = sprintf("`%s` IN ('%s')",$args[0],implode("','",$args[2]));
                            }else{
                                $ret[] = sprintf("`%s`%s('%s')",$args[0],$args[1],$this->buildKey($args[0],$args[2]));
                            }
                        }

                    }
                }
                break;
        }
        return implode(' AND ',$ret);
    }


}
class Error{
    static function errorMsg($code,$msg){
        throw new \Exception($msg,$code);
    }
}
/**
 * 数据库连接
 * @author 耿鸿飞 <15911185633>
 * @date 2020/11/10
 * @like
 * Class GDb
 */
class GDb
{
    protected $pdo = false;
    private $conName = 'default';
    private static $self = [];

    public function __construct($conName = 'default',$useDbName = true){
        $this->conName = $conName;
    }

    /**
     * 获取实例
     * @param string $name
     * @return GDb|mixed
     * @author 耿鸿飞 <15911185633>
     * @date 2020/11/10
     * @like
     */
    public static function Ins($name = 'default'){
        if(!isset(self::$self[$name])){
            self::$self[$name] = new self($name);
        }
        return self::$self[$name];
    }

    /** 获取数据库连接
     * @return PDO
     * @author 耿鸿飞 <15911185633>
     * @date 2020/11/10
     * @like
     */
    private function getPdo(){
        if($this->pdo === false){
            $conf = Conf::Ins()->getArr("mysql.".$this->conName,[
                'host' => '127.0.0.1',
                'port' => 3306,
                'dbname' => 'test',
                'user' => 'root',
                'pass' => '',
                'charset' => 'utf8'
            ]);
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s',
                $conf['host'],$conf['port'],$conf['dbname']);
            $pdo = new \PDO($dsn,$conf['user'],$conf['pass'],
                [\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES '.$conf['charset']]);
            $this->pdo = $pdo;
        }
        return $this->pdo;

    }




    /**
     * 使用事务
     * @auth 耿鸿飞 <15911185633>
     * @date 2020/8/24 17:54
     * @param $func
     * @return bool
     */
    public function begin($func){
        if(!$this->getPdo()->inTransaction()){
            $this->getPdo()->beginTransaction();
        }
        try{
            $ret = $func();
            $this->getPdo()->commit();
            return $ret;
        }catch (\Exception $e){
            $this->getPdo()->rollBack();
            return false;
        }
    }

    /**
     * 执行SQL
     * @param $sql
     * @return bool
     * @author 耿鸿飞 <15911185633>
     * @date 2020/11/10
     * @like
     */
    public function exec($sql){
        return $this->getPdo()->exec(call_user_func_array('sprintf',func_get_args())) !== false;
    }

    /**
     * 执行SQL
     * @param $sql
     * @param array $args
     * @param int $isRetry
     * @return bool|PDOStatement
     * @throws Exception
     * @author 耿鸿飞 <15911185633>
     * @date 2020/11/10
     * @like
     */
    public function query($sql,$args = [],$isRetry = 0){
        try{
            $st = $this->getPdo()->prepare($sql);
            $st->execute($args);
            $this->debug('%s %s (%s) : %s',$st->errorCode(),$sql,json_encode($args),json_encode($st->errorInfo()));
            return $st;
        }catch (\Exception $e){
            throw $e;
        }

    }

    private function debug($smg){
        call_user_func_array('\\Gen\\Log::Sql',func_get_args());
    }

    /**
     * 获取一行
     * @param $sql
     * @param $args
     * @return mixed
     * @author 耿鸿飞 <15911185633>
     * @date 2020/11/10
     * @like
     */
    public function fetchRow($sql,$args){
        if(!is_array($args)){
            $sql = call_user_func_array('sprintf',func_get_args());
            $args = [];
        }
        $st = $this->query($sql,$args);
        $row = $st->fetch(\PDO::FETCH_ASSOC);
        $st->closeCursor();
        return $row;
    }

    /**
     * 获取列表
     * @param $sql
     * @param $args
     * @return array
     * @author 耿鸿飞 <15911185633>
     * @date 2020/11/10
     * @like
     */
    public function fetchAll($sql,$args){
        if(!is_array($args)){
            $sql = call_user_func_array('sprintf',func_get_args());
            $args = [];
        }
        $st = $this->query($sql,$args);
        $row = $st->fetchAll(\PDO::FETCH_ASSOC);
        $st->closeCursor();
        return $row;
    }

    /**
     * 更新数据
     * @param $sql
     * @param $args
     * @return int
     * @author 耿鸿飞 <15911185633>
     * @date 2020/11/10
     * @like
     */
    public function update($sql,$args){
        if(!is_array($args)){
            $sql = call_user_func_array('sprintf',func_get_args());
            $args = [];
        }
        $st = $this->query($sql,$args);
        $row = $st->rowCount();
        return $row;
    }

    /**
     * 数据删除功能
     * @param $sql
     * @param $args
     * @return int
     * @throws Exception
     * @author 耿鸿飞 <15911185633>
     * @date 2020/11/10
     * @like
     */
    public function delete($sql,$args){
        if(!is_array($args)){
            $sql = call_user_func_array('sprintf',func_get_args());
            $args = [];
        }
        $st = $this->query($sql,$args);
        $row = $st->rowCount();
        return $row;
    }

    /**
     * 插入数据
     * @param $sql
     * @param $args
     * @return string
     * @author 耿鸿飞 <15911185633>
     * @date 2020/11/10
     * @like
     */
    public function insert($sql,$args){
        if(!is_array($args)){
            $sql = call_user_func_array('sprintf',func_get_args());
            $args = [];
        }
        $st = $this->query($sql,$args);
        return $this->getPdo()->lastInsertId();
    }
}