<?php
/**
 *
 * @author 耿鸿飞<genghongfei@soyoung.com>
 * @link
 * @Date: 2021/2/24 23:18
 */


namespace Gen;

define('DS',DIRECTORY_SEPARATOR);
define('APP_ROOT',dirname(realpath($_SERVER['SCRIPT_FILENAME'])));
class Cli
{
    static function Run(){
        $args = $_SERVER['argv'];
        $name = $args[1] ?? '';
        if(!$name){
            self::error();
        }
        switch (strtolower($name)){
            case 'restart':
                Log::ShowDebug("重启服务");
                system(sprintf("sudo systemctl restart %s",basename(APP_ROOT)));
                break;
            case 'pull':
                Log::ShowDebug("更新代码");
                system("git pull;composer update");
                Log::ShowDebug("重启服务");
                system(sprintf("sudo systemctl restart %s",basename(APP_ROOT)));
                die();
                break;

        }
        $claName = '\\App\\Command\\'.$name.'Command';
        if(!class_exists($claName)){
            self::error();
        }
        if('daemon' == ($args[2] ?? '')){
            Log::ServerDebug("程序要一直运行");
            while (true){
                $pid = pcntl_fork();
                if($pid == 0){
                    (new $claName)->handel();

                    Log::ServerDebug("子进程已经退出 -> %s",$claName);
                    exit(0);
                }else{
                    pcntl_wait($status);
                    Log::ServerDebug("子进程重启中 -> %s",$claName);
                    sleep(5);
                }
            }
        }else{
            (new $claName)->handel();
        }

    }
    private static function error(){
        echo "参数错误\n";
        echo sprintf("%s %s CMD [daemon]\n\nCMD:\n",
            $_SERVER['_'] ?? 'php',
            $_SERVER['SCRIPT_FILENAME']
        );
        self::showCommand();
        die();
    }
    private static function showCommand($path = ''){
        $path = trim($path,'/');
        $dir = APP_ROOT.DS.'Command';
        if($path){
            $dir .= DS.$path;
        }
        $d = dir($dir);
        while($name = $d->read()){
            if($name == '.' || $name == '..'){
                continue;
            }
            if(is_dir($dir.'/'.$name)){
                self::showCommand($path.'/'.$name);
            }else{
                $name = str_replace('Command.php','',$name);
                $cName = $path.'\\\\'.$name;
                echo sprintf("\t%s\n",trim($cName,'\\'));
            }
        }
    }
}