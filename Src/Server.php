<?php


namespace Gen;

define('DS',DIRECTORY_SEPARATOR);
define('APP_ROOT',dirname(realpath($_SERVER['SCRIPT_FILENAME'])));

class Server
{
    static function Run(){
        self::startBaseApi();
        $httpServer = new \Swoole\Http\Server('0.0.0.0', Conf::Ins()->getInt('app.port',8080));
        $httpServer->on("start",function($server){
            Log::ServerDebug("程序启动");
        });
        $httpServer->on("request",function($r,$w){
            $url = trim($r->server['request_uri'], '/');
            Log::Time("%s start",$url);
            $w->header('Access-Control-Allow-Origin','*');
            $data = self::apiCall($r,$w);
            $w->header('content-type', 'application/json', true);
            $ret = json_encode($data);
            $w->end($ret);
            Log::Time("%s end",$url);
        });
        $httpServer->start();
    }

    private static function apiCall($r,$w){
        $ip = self::getClientIp($r);
        $time = microtime(true);
        $url = trim($r->server['request_uri'], '/');
        $actionName = trim($url,'.api');
        $actionName = str_replace('/','\\',$actionName);
        if(!$actionName){
            Log::Error('接口不存在 %s',$actionName);
            return App::Error(404,'接口不存在');
        }
        $actionName = '\\App\\Action\\'.ucfirst($actionName).'Action';
        if(!class_exists($actionName)){
            Log::Error('接口不存在 %s',$actionName);
            return App::Error(404,'接口不存在');
        }
        try{
            $obj = new $actionName();
            $params = self::parseParam($r);
            $uid = self::getUid($r);
            $data = $obj->execute($uid,$params,$ip);
            $newUid = $obj->getNewUid();
            self::setUid($w,$newUid,$ip);
            if(isset($data['code'])){
                return $data;
            }
            return App::Success($data);
        }catch (\Exception $e){
            Log::Error($e->getTraceAsString());
            return App::Error(500,'系统异常');
        }
    }

    private static function setUid($w,$uid,$ip){
        if($uid){
            $data = [
                'uid' => $uid,
                'ip' => $ip,
                'create_at' => time()
            ];
            $w->header(strtolower(Conf::Ins()->get('app.token.name','token')),
                App::SecureEncode(json_encode($data))
            );
        }
    }
    private static function getUid($r){
        $token = $r->header[strtolower(Conf::Ins()->get('app.token.name','token'))] ?? "";
        if (!$token){
            return 0;
        }
        $data = json_decode(App::SecureDecode($token),true);

        return $data['uid'] ?? 0;
    }
    /**
     * 解析数据提交
     * @param $r
     * @return mixed
     * @author 耿鸿飞 <15911185633>
     * @date 2021/2/19
     * @like
     */
    private static function parseParam($r){
        $data = $r->rawContent();
        $jsonData = json_decode($data,true);
        return $jsonData;
    }

    /**
     * 获取客户端IP
     * @param $r
     * @return mixed
     * @author 耿鸿飞 <15911185633>
     * @date 2021/2/19
     * @like
     */
    private static function getClientIp($r){
        $ip = $r->server['remote_addr'];
        if(!$ip || $ip == '127.0.0.1'){
            $ip = $r->header['x-real-ip'] ?? '0.0.0.0';
        }
        return $ip;
    }
    //判断是否开启基础的api接口
    private static function startBaseApi(){
        if('on' == Conf::Ins()->get('app.base','on')){
            class_alias(\Gen\BaseApi\Action\Msg\ChatMsgListAction::class,'\\App\\Action\\Msg\\ChatMsgListAction');
            class_alias(\Gen\BaseApi\Action\Msg\ChatUsersAction::class,'\\App\\Action\\Msg\\ChatUsersAction');
            class_alias(\Gen\BaseApi\Action\Msg\ChatSendMsgAction::class,'\\App\\Action\\Msg\\ChatSendMsgAction');
            class_alias(\Gen\BaseApi\Action\Relation\BlackListAddAction::class,'\\App\\Action\\Relation\\BlackListAddAction');
            class_alias(\Gen\BaseApi\Action\Relation\BlackListAction::class,'\\App\\Action\\Relation\\BlackListAction');
            class_alias(\Gen\BaseApi\Action\Relation\FriendApplyAction::class,'\\App\\Action\\Relation\\FriendApplyAction:');
            class_alias(\Gen\BaseApi\Action\Relation\FriendDelAction::class,'\\App\\Action\\Relation\\FriendDelAction');
            class_alias(\Gen\BaseApi\Action\Relation\FriendConfirmAction::class,'\\App\\Action\\Relation\\FriendConfirmAction');
            class_alias(\Gen\BaseApi\Action\Relation\FriendListAction::class,'\\App\\Action\\Relation\\FriendListAction');
            class_alias(\Gen\BaseApi\Action\Relation\FriendMarkAction::class,'\\App\\Action\\Relation\\FriendMarkAction');
            class_alias(\Gen\BaseApi\Action\Resource\CommentAction::class,'\\App\\Action\\Resource\\CommentAction');
            class_alias(\Gen\BaseApi\Action\Resource\CommentListAction::class,'\\App\\Action\\Resource\\CommentListAction',);
            class_alias(\Gen\BaseApi\Action\Resource\FollowAction::class,'\\App\\Action\\Resource\\FollowAction');
            class_alias(\Gen\BaseApi\Action\Resource\FollowListAction::class,'\\App\\Action\\Resource\\FollowListAction');
            class_alias(\Gen\BaseApi\Action\Resource\PraiseAction::class,'\\App\\Action\\Resource\\PraiseAction');
            class_alias(\Gen\BaseApi\Action\Resource\PraiseListAction::class,'\\App\\Action\\Resource\\PraiseListAction');
            class_alias(\Gen\BaseApi\Action\Resource\ScoreAction::class,'\\App\\Action\\Resource\\ScoreAction');
            class_alias(\Gen\BaseApi\Action\Resource\ScoreListAction::class,'\\App\\Action\\Resource\\ScoreListAction');
            //(new \App\Action\Resource\ScoreListAction)->execute();

        }
    }

}



