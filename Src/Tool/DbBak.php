<?php


namespace Gen\Tool;


use Gen\GDb;

class DbBak
{

    //同步数据库
    public function dbSync($srcName,$descName){
        $src = GDb::Ins($srcName);
        $desc = GDb::Ins($descName);
        $tables = $this->getTables($src);
        foreach ($tables as $table => $dd){
            $start = 0;
            while ($start >= 0){
                $list = $src->fetchAll("SELECT * FROM %s LIMIT %d,100",$table,$start);

                if(!$list){
                    $start = -1;
                    break;
                }
                $start += count($list);
                foreach($list as $item){
                    $desc->insert("INSERT INTO `%s`(`%s`) VALUES('%s')",$table,implode("`,`",array_keys($item)),implode("','",array_values($item)));
                }
            }
        }
    }
    //同步数据库到新的库
    public function dbBakNewDb($srcName,$descName){
        $src = GDb::Ins($srcName);
        $desc = GDb::Ins($descName);
        $sts = $this->getTables($src);
        $dts = $this->getTables($desc);
        foreach ($dts as $t=>$v){
            $desc->exec("DROP TABLE `%s`",$t);
        }
        foreach ($sts as $k => $sql){
            $desc->exec($sql);
        }
        $this->dbSync($srcName,$descName);
    }

    private function getTables($con){
        $tables = $con->fetchAll("SHOW TABLES %s",'');
        $ret = [];
        foreach ($tables as $table){
            foreach ($table as $k => $t){
                $data = $con->fetchRow("SHOW CREATE TABLE %s",$t);
                $ret[$t] = $data['Create Table'];
                break;
            }


        }
        return $ret ;
    }

}