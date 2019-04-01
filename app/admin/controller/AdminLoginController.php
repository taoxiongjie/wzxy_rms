<?php
// +----------------------------------------------------------------------
// | Author:X烦恼 <384576861@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\RouteModel;
use cmf\controller\AdminBaseController;

use think\Db;

/**
 * Class AdminLoginController
 * @package app\admin\controller
 */
class AdminLoginController extends AdminBaseController
{

    /**
     * 登录界面信息
     * @author X烦恼 <384576861@qq.com>
     */
    public function index()
    {
         $admin_login_info    = cmf_get_option('admin_login');
        $this->assign($admin_login_info);
        $this->assign('admin_login_info',$admin_login_info);
        return $this->fetch();
    }

    /**
     * 登录界面信息设置提交
     * @author X烦恼 <384576861@qq.com>
     */
    public function InfoPost()
    {
        if ($this->request->isPost()) {
            cmf_log_record(null);  //添加日志 $id=对象ID
            $options = $this->request->param('options/a');
            cmf_set_option('admin_login', $options);
            $this->success("保存成功！", '');

        }
    }

}