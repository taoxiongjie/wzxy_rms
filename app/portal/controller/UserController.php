<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\portal\controller;

use cmf\controller\HomeBaseController;
use think\Db;
class UserController extends HomeBaseController
{

    public function _initialize()
    {
        parent::_initialize();
    }

    //个人中心
    public function index()
    {
        $id   = cmf_get_current_user_id();
        $user = Db::name('user')->where(['id' => $id])->find();
        $this->assign($user);
        $this->assign('user',$user);
        return $this->fetch();
    }

    //个人信息
    public function info()
    {
        $id   = cmf_get_current_user_id();
        $user = Db::name('user')->where(['id' => $id])->find();
        $this->assign($user);
        $this->assign('user',$user);
        return $this->fetch();
    }
    //个人信息提交
    public function userinfopost()
    {
        if ($this->request->isPost()) {
            $result = $this->validate($this->request->param(), 'User.edit');
            if ($result !== true) {
                $this->error($result);
            } else {
                $data = $this->request->post();
                $id   = cmf_get_current_user_id();
                unset($data['avatar_old']);
                $create_result = Db::name('user')->where(['id' => $id])->update($data);
                if ($create_result !== false) {
                    $this->success("保存成功！",url("portal/user/index") );
                } else {
                    $this->error("保存失败！");
                }
            }
        }
    }
    //密码修改
    public function change_password(){

        return $this->fetch();
    }
    //密码修改
    public function change_password_post(){
        if ($this->request->isPost()) {
            $result = $this->validate($this->request->param(), 'UserPass');
            if ($result !== true) {
                $this->error($result);
            } else {
                $id   = cmf_get_current_user_id();
                $data = $this->request->post();
                $result = Db::name('user')->where(['id' => $id])->find();

                $comparePasswordResult = cmf_compare_password($data['user_pass'], $result['user_pass']);
                if ($comparePasswordResult) {
                    $data['user_pass'] = cmf_password($data['new_user_pass']);
                    unset($data['new_user_pass'],$data['new_user_pass_old']);
                }
                $create_result = Db::name('user')->where(['id' => $id])->update($data);
                if ($create_result !== false) {
                    $this->success("修改成功！",url("portal/user/index"));
                } else {
                    $this->error("修改失败！");
                }
            }
        }
    }
    //用户列表
    public function user_list(){
            $content = hook_one('admin_user_index_view');

            if (!empty($content)) {
                return $content;
            }
            $where=[];
            //$admin_id = session('ADMIN_INFO');
            //$where = ["user_type" => 1];
            $request = input('request.');
            $keywordComplex = [];
            if (!empty($request['keyword'])) {
                $keyword = $request['keyword'];
                $keywordComplex['user_login|user_nickname|user_email|mobile|college']    = ['like', "%$keyword%"];
            }
            /**搜索条件**/
            $userLogin = $this->request->param('user_login');
            $userEmail = trim($this->request->param('user_email'));

            if ($userLogin) {
                $where['user_login'] = ['like', "%$userLogin%"];
            }

            if ($userEmail) {
                $where['user_email'] = ['like', "%$userEmail%"];;
            }
            $where['id']=array('neq',1);
            $users = Db::name('user')
                ->whereOr($keywordComplex)
                ->where($where)
                ->order("id DESC")
                ->paginate(10);
            $users->appends(['user_login' => $userLogin, 'user_email' => $userEmail]);
            // 获取分页显示
            $page = $users->render();

            $rolesSrc = Db::name('role')->select();
            $roles    = [];
            foreach ($rolesSrc as $r) {
                $roleId           = $r['id'];
                $roles["$roleId"] = $r;
            }
            $this->assign("page", $page);
            $this->assign("roles", $roles);
            $this->assign("users", $users);
            return $this->fetch();
    }
    //用户添加
    public function add()
    {
        return $this->fetch();
    }
    public function addPost()
    {
        if ($this->request->isPost()) {

            if (!empty($_POST['role_id'])) {
                $role_id = $_POST['role_id'];
                unset($_POST['role_id']);
                $result = $this->validate($this->request->param(), 'UserPost');
                if ($result !== true) {
                    $this->error($result);
                } else {
                    $_POST['user_pass'] = cmf_password($_POST['user_pass']);
                    $_POST['create_time'] =time();
                    $result             = DB::name('user')->insertGetId($_POST);
                    if ($result !== false) {
                            Db::name('RoleUser')->insert(["role_id" => $role_id, "user_id" => $result]);
                        $this->success("添加成功！", url("user/user_list"));
                    } else {
                        $this->error("添加失败！");
                    }
                }
            } else {
                $this->error("请为此用户指定角色！");
            }

        }
    }
    //编辑
    public function edit()
    {
        $id    = $this->request->param('id', 0, 'intval');
        $role_ids = DB::name('RoleUser')->where(["user_id" => $id])->find();
        $this->assign("role_ids", $role_ids);
        $user = DB::name('user')->where(["id" => $id])->find();
        $this->assign($user);
        $this->assign('user',$user);
        return $this->fetch();
    }

    /**
     * 管理员编辑提交
     */
    public function editPost()
    {
        if ($this->request->isPost()) {
            if (!empty($_POST['role_id'])) {
                $role_id = $this->request->param('role_id');
                $avatar_old= $_POST['avatar_old'];
                unset($_POST['role_id'],$_POST['avatar_old']);
                $result = $this->validate($this->request->param(), 'User.edit');
                if ($result !== true) {
                    // 验证失败 输出错误信息
                    $this->error($result);
                } else {
                    $result = DB::name('user')->update($_POST);
                    if ($result !== false) {
                        if($_POST['avatar']!==$avatar_old){
                            $filePath=$_SERVER['DOCUMENT_ROOT'].'/upload/'.$avatar_old;
                            if(file_exists($filePath))	 { //检查图片文件是否存在
                                unlink($filePath);
                                Db::name('asset')->where(array('file_path'=>$avatar_old))->delete();
                            }
                        }
                        $uid = $this->request->param('id', 0, 'intval');
                        DB::name("RoleUser")->where(["user_id" => $uid])->update(["role_id" => $role_id]);
                        $this->success("保存成功！");
                    } else {
                        $this->error("保存失败！");
                    }
                }
            } else {
                $this->error("请为此用户指定角色！");
            }

        }
    }
    /**
     * 重置管理员密码
     */
    public function reset()
    {
        $id = $this->request->param('id', 0, 'intval');
        if (!empty($id)) {
            $user_pass= cmf_password('123456');
            $result = Db::name('user')->where(["id" => $id])->setField('user_pass', $user_pass);
            if ($result !== false) {
                $this->success("重置密码成功！", url("user/user_list"));
            } else {
                $this->error('重置密码失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    /**
     * 管理员删除
     * @adminMenu(
     *     'name'   => '管理员删除',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员删除',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $param           = $this->request->param();
        if (isset($param['id'])) {
            $id = $this->request->param('id', 0, 'intval');
            if ($id == 1) {
                $this->error("最高管理员不能删除！");
            }
            $userInfo=Db::name('user')->find($id);
            if (Db::name('user')->delete($id) !== false) {
                Db::name("RoleUser")->where(["user_id" => $id])->delete();
                if($userInfo['avatar']){
                    $filePath=$_SERVER['DOCUMENT_ROOT'].'/upload/'.$userInfo['avatar'];
                    if(file_exists($filePath))	 { //检查图片文件是否存在
                        unlink($filePath);
                        Db::name('asset')->where(array('file_path'=>$userInfo['avatar']))->delete();
                    }
                }
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
        if (isset($param['ids'])) {
            $ids     = $this->request->param('ids/a');
            $userInfo = Db::name('user')->where(['id' => ['in', $ids]])->select();
            $result= Db::name('user')->where(['id' => ['in', $ids]])->delete();
            if ($result) {
                Db::name("RoleUser")->where(['id' => ['in', $ids]])->delete();
                foreach ($userInfo as $value) {
                    if($value['avatar']){
                        $filePath=$_SERVER['DOCUMENT_ROOT'].'/upload/'.$value['avatar'];
                        if(file_exists($filePath))	 { //检查图片文件是否存在
                            unlink($filePath);
                            Db::name('asset')->where(array('file_path'=>$value['avatar']))->delete();
                        }
                    }
                }
                $this->success("删除成功！", '');
            }
        }
    }
}
