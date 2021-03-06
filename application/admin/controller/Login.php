<?php
/**
 * 新闻app后端登录控制器
 * Created by PhpStorm.
 * User: 王晶旭
 * Date: 2018/4/1
 * Time: 23:20
 */

namespace app\admin\controller;


use app\common\lib\IAuth;
use think\captcha\Captcha;
use think\Exception;
use think\Request;
use think\Session;

class Login extends Base
{
    /**
     * 登录页面
     * @return mixed
     */
    public function login()
    {
        $this->alreadyLogin();
        return $this->fetch('login');
    }

    /**
     * 设置验证码
     *
     * 参数	        描述	    默认
     * codeSet	验证码字符集合	略
     * expire	验证码过期时间（s）	1800
     * useZh	使用中文验证码	false
     * zhSet	中文验证码字符串	略
     * useImgBg	使用背景图片	false
     * fontSize	验证码字体大小(px)	25
     * useCurve	是否画混淆曲线	true
     * useNoise	是否添加杂点	true
     * imageH	验证码图片高度，设置为0为自动计算	0
     * imageW	验证码图片宽度，设置为0为自动计算	0
     * length	验证码位数	5
     * fontttf	验证码字体，不设置是随机获取	空
     * bg	背景颜色	[243, 251, 254]
     * reset	验证成功后是否重置	true
     * @return \think\Response
     */
    public function show_captcha()
    {
        $captcha = new Captcha();
        $captcha->fontSize = 25;
        $captcha->length = 4;
        $captcha->imageH = 0;
        $captcha->imageW = 0;
        $captcha->expire = 30;
        $captcha->useNoise = true;
        $captcha->reset = true;
        return $captcha->entry();
    }

    /**
     * 验证登录信息
     * @param Request $request
     * @return \think\response\Json
     */
    public function check(Request $request)
    {
        //初始化json返回参数
        $status = config("code.error");
        $result = '';
        $data = $request->param();

        //验证
        if ($request->isPost()) {
            $validate = validate('LoginUser');
            if ($validate->check($data)) {
                try {
                    $user = model('AdminUser')->get(['username'=>$data['username']]);
                } catch (Exception $exception) {
                    $result = $exception->getMessage();
                    return show_json($status, $result, $data);
                }

                if ($user == null || $user->status != config("code.status_normal")) {
                    $result = '该用户不存在';
                } elseif (IAuth::setPassword($data["password"]) != $user["password"]) {
                    $result = '密码不正确';
                } else {
                    $status = config("code.success");
                    $result = '登录成功!';
                    Session::set(config("admin.session_user"), $user->getData());
                    Session::set(config("admin.session_user_id"), $user->id);
                }
            } else {
                $result = $validate->getError();
            }
        }else{
            $result = "请求不合法!";
        }

        return show_json($status, $result, $data);
    }

    /**
     *退出登录的逻辑
     */
    public function logout()
    {
        Session::delete(config("admin.session_user"));
        Session::delete(config("admin.session_user_id"));

        $this->redirect("admin/login/login");
    }
}