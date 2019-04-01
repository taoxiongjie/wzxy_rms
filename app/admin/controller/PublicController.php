<?php
// +----------------------------------------------------------------------
// | Author:X烦恼 <384576861@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;

class PublicController extends AdminBaseController
{
    public function _initialize()
    {
    }

    /**
     * 后台登陆界面
     */
    public function login()
    {
        $loginAllowed = session("__LOGIN_BY_CMF_ADMIN_PW__");
        if (empty($loginAllowed)) {
            //$this->error('非法登录!', cmf_get_root() . '/');
            return redirect(cmf_get_root() . "/");
        }

        $admin_id = session('ADMIN_ID_RMS');
        cmf_log_record($admin_id);  //添加日志 $id=对象ID
        if (!empty($admin_id)) {//已经登录
            return redirect(url("admin/Index/index"));
        } else {
            $site_admin_url_password = config("cmf_SITE_ADMIN_URL_PASSWORD");
            $upw                     = session("__CMF_UPW__");
            if (!empty($site_admin_url_password) && $upw != $site_admin_url_password) {
                return redirect(cmf_get_root() . "/");
            } else {
                session("__SP_ADMIN_LOGIN_PAGE_SHOWED_SUCCESS__", true);
                $result = hook_one('admin_login');
                if (!empty($result)) {
                    return $result;
                }
                $config    = cmf_get_option('admin_login');
                $this->assign($config);
                return $this->fetch();
            }
        }
    }
    /**
     * 跳转页面
     */
    public function jump()
    {
        $time = $this->request->param("time");
        session('ADMIN_ID_RMS',null);
        $data=array('name'=>'登录超时','text'=>'由于您超过'.$time.'未操作,请您重新登录！');
        $this->assign('data',$data);
         return $this->fetch(":404");
    }
    /**
     * 跳转页面
     */
    public function jump_pass()
    {
        session('ADMIN_ID_RMS',null);
        $data=array('name'=>'登录异常','text'=>'已经有用户在另一台计算机使用该账户进行登录,如果密码泄露请尽快修改！');
        $this->assign('data',$data);
        return $this->fetch(":404");
    }


    /**
     * 登录验证
     */
    public function doLogin()
    {
        $loginAllowed = session("__LOGIN_BY_CMF_ADMIN_PW__");
        if (empty($loginAllowed)) {
            $this->error('非法登录!', cmf_get_root() . '/');
        }
        $captcha = $this->request->param('captcha');
        if (empty($captcha)) {
            $this->error(lang('CAPTCHA_REQUIRED'));
        }
        //验证码
        if (!cmf_captcha_check($captcha)) {
            $this->error(lang('CAPTCHA_NOT_RIGHT'));
        }

        $name = $this->request->param("username");
        if (empty($name)) {
            $this->error(lang('USERNAME_OR_EMAIL_EMPTY'));
        }
        $pass = $this->request->param("password");
        if (empty($pass)) {
            $this->error(lang('PASSWORD_REQUIRED'));
        }
        if (strpos($name, "@") > 0) {//邮箱登陆
            $where['user_email'] = $name;
        } else {
            $where['user_login'] = $name;
        }

        $result = Db::name('user')->where($where)->find();
        session('ADMIN_INFO',$result);
        cmf_log_record(null);  //添加日志 $id=对象ID
        if (!empty($result)) {
            if (cmf_compare_password($pass, $result['user_pass'])) {
                $groups = Db::name('RoleUser')
                    ->alias("a")
                    ->join('__ROLE__ b', 'a.role_id =b.id')
                    ->where(["user_id" => $result["id"], "status" => 1])
                    ->value("role_id");
                if ($result["id"] != 1 && (empty($groups) || empty($result['user_status']))) {
                    $this->error(lang('USE_DISABLED'));
                }
                //登入成功页面跳转
                session('ADMIN_ID_RMS', $result["id"]);
                session('name', $result["user_login"]);
                $result['last_login_ip']   = get_client_ip(0, true);
                $result['last_login_time'] = time();
                $result['last_access_time']=time();
                $result['last_session_id']=date("YmdHis").rand(1000,99999);
                session('LAST_SESSION_ID_RMS',$result['last_session_id']);
                $token                     = cmf_generate_user_token($result["id"], 'web');
                if (!empty($token)) {
                    session('token', $token);
                }
                Db::name('user')->update($result);
                cookie("admin_username", $name, 3600 * 24 * 30);
                session("__LOGIN_BY_CMF_ADMIN_PW__", null);
                $this->success(lang('LOGIN_SUCCESS'), url("admin/Index/index"));
            } else {
                $this->error(lang('PASSWORD_NOT_RIGHT'));
            }
        } else {
            $this->error(lang('USERNAME_NOT_EXIST'));
        }
    }

    /**
     * 后台管理员退出
     */
    public function logout()
    {
        session('ADMIN_ID_RMS', null);
        cmf_log_record(null);  //添加日志 $id=对象ID
        $site_info = cmf_get_option('site_info');
        $site_url=$site_info['site_url']?$site_info['site_url']:"/admin/index";
        return redirect($site_url);
    }
}