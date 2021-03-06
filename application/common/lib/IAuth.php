<?php
/**
 * 公共调用接口类库
 * Created by PhpStorm.
 * User: 王晶旭
 * Date: 2018/4/1
 * Time: 23:49
 */

namespace app\common\lib;


use think\Cache;
use think\queue\job\Redis;

class IAuth
{

    /**
     * 设置密码
     * @param string $data
     * @return string
     */
    public static function setPassword($data = "")
    {
        return md5($data . config("app.password_pre_halt"));
    }

    /**
     * 生成每次请求时的 sign
     * @param array $data
     * @return string
     */
    public static function setSign($data = [])
    {
        ksort($data);
        $string = http_build_query($data);
        $string = (new Aes())->encrypt($string);
        return $string;
    }

    /**
     * 检查sign是否正常
     * @param array $data
     * @return bool
     */
    public static function checkSignPass($data = [])
    {
        $str =(new Aes())->decrypt($data['sign']);
        if (empty($str)) {
            return false;
        }
        parse_str($str, $arr);
        if (!is_array($arr) || empty($arr['did']) || $arr['did'] != $data['did']) {
            return false;
        }
        if(!config("app_debug")){
            if ((time() - ceil($arr['time'] / 1000)) > config("app.app_sign_time")) {
                return false;
            }
            $redis = new \Redis();
            $redis->connect("139.224.117.254", 7200);
//            if (Cache::get($data['sign'])) {
//                return false;
//            }
            if ($redis->get($data['sign'])){
                return false;
            }
        }
        return true;
    }
}