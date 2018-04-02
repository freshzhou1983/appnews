<?php
/**
 * 新闻app后端admin模块基础控制器
 * Created by PhpStorm.
 * User: 王晶旭
 * Date: 2018/3/31
 * Time: 0:43
 */

namespace app\admin\controller;


use think\Controller;
use think\Session;

class Base extends Controller
{
    //page 获取页数
    protected $page = '';
    //size 获取每页多少条数据
    protected $size = '';
    //from 查询条件中的新闻起始值
    protected $from = 0;

    protected function _initialize()
    {
        parent::_initialize(); // TODO: Change the autogenerated stub
        //定义用户
        define('USER_ID', Session::get(config("admin.session_user_id")));
    }

    //如果是未登录的用户  跳转回登录页面
    protected function isLogin()
    {
        if (empty(USER_ID)) {
            $this->error('用户未登录，无权访问', url('admin/login/login'));
        }
    }

    //对于已经登录的用户  不允许重复登录
    protected function alreadyLogin()
    {
        if (!empty(USER_ID)) {
            $this->error('用户已经登录，请勿重复登录', url('admin/index/index'));
        }
    }

    //获取新闻分页的 page size from 的值
    protected function getPageAndSize($data = [])
    {
        $this->page = empty($data['page']) ? 1 : $data['page'];
        $this->size = empty($data['size']) ? config("paginate.list_rows") : $data['size'];
        $this->from = ($this->page - 1) * $this->size;
    }
}