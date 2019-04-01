<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------

namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;

class AdminStuController extends AdminBaseController
{

    /**
     * 学生列表
     */
    public function index()
    {
        $where   = [];
        $request = input('request.');
        if (!empty($request['user_login'])) {
            $where['user_login'] =intval($request['user_login']);
        }
        $keywordComplex = [];
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];
            $keywordComplex['user_login|user_nickname|user_email|mobile']    = ['like', "%$keyword%"];
        }
        $usersQuery = Db::name('user');
        $where['user_type'] = 4; //学生
        $list = $usersQuery->whereOr($keywordComplex)->where($where)->order("create_time DESC")->paginate(10);
        // 获取分页显示
        $page = $list->render();
        $this->assign('list', $list);
        $this->assign('page', $page);
        // 渲染模板输出
        return $this->fetch();
    }

    /**
     * 用户拉黑
     */
    public function ban()
    {
        $id = input('param.id', 0, 'intval');
        if ($id) {
            $result = Db::name("user")->where(["id" => $id, "user_type" => 4])->setField('user_status', 0);
            if ($result) {
                $this->success("学生拉黑成功！", "adminStu/index");
            } else {
                $this->error('学生拉黑失败,会员不存在,或者是管理员！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    /**
     * 用户启用
     */
    public function cancelBan()
    {
        $id = input('param.id', 0, 'intval');
        if ($id) {
            Db::name("user")->where(["id" => $id, "user_type" => 4])->setField('user_status', 1);
            $this->success("学生启用成功！", '');
        } else {
            $this->error('数据传入失败！');
        }
    }
    public function add()
    {

        return $this->fetch();
    }

    public function addPost()
    {
        if ($this->request->isPost()) {

            $result = $this->validate($this->request->param(), 'User');
            if ($result !== true) {
                $this->error($result);
            } else {
                $_POST['user_pass'] = cmf_password($_POST['user_pass']);
                $_POST['create_time'] = time();
                $_POST['user_type'] = 4;
                $result             = DB::name('user')->insertGetId($_POST);
                if ($result !== false) {

                    $this->success("添加成功！", url("adminTea/index"));
                } else {
                    $this->error("添加失败！");
                }
            }
        }
    }
    /**
     * 编辑
     */
    public function edit()
    {
        $id    = $this->request->param('id', 0, 'intval');
        $user = DB::name('user')->where(["id" => $id])->find();

        $this->assign($user);
        return $this->fetch();
    }

    /**
     * 编辑提交
     */
    public function editPost()
    {
        if ($this->request->isPost()) {
            $_POST['user_login']=trim($_POST['user_login']);
            $_POST['user_nickname']=trim($_POST['user_nickname']);
            $_POST['mobile']=trim($_POST['mobile']);
            $_POST['user_email']=trim($_POST['user_email']);
            $data['avatar_old']=$_POST['avatar_old'];
            $data['student_card_old']=$_POST['student_card_old'];
            if ($_POST['user_login']=="" || $_POST['user_nickname']=="" || $_POST['mobile']=="" || $_POST['user_email']=="") {
                // 验证失败 输出错误信息
                $this->error('请完善信息');
            } else {
                unset($_POST['avatar_old']);
                unset($_POST['student_card_old']);
                $result = DB::name('user')->update($_POST);
                if ($result !== false) {

                    if($_POST['avatar'] != $data['avatar_old'] ){
                        $filePath=$_SERVER['DOCUMENT_ROOT'].'/upload/'.$data['avatar_old'];
                        if(file_exists($filePath))	 { //检查图片文件是否存在
                            unlink($filePath);
                            Db::name('asset')->where(array('file_path'=>$data['avatar_old']))->delete();
                        }
                    }
                    if($_POST['student_card'] != $data['student_card_old'] ){
                        $filePath=$_SERVER['DOCUMENT_ROOT'].'/upload/'.$data['student_card_old'];
                        if(file_exists($filePath))	 { //检查图片文件是否存在
                            unlink($filePath);
                            Db::name('asset')->where(array('file_path'=>$data['student_card_old']))->delete();
                        }
                    }
                    $this->success("保存成功！", "AdminStu/index");
                } else {
                    $this->error("保存失败！");
                }
            }
        }
    }


    public function delete()
    {
        $id = $this->request->param('id', 0, 'intval');

        $userInfo=Db::name('user')->find($id);
        if (Db::name('user')->delete($id) !== false) {
            if($userInfo['avatar']){
                $filePath=$_SERVER['DOCUMENT_ROOT'].'/upload/'.$userInfo['avatar'];
                if(file_exists($filePath))	 { //检查图片文件是否存在
                    unlink($filePath);
                    Db::name('asset')->where(array('file_path'=>$userInfo['avatar']))->delete();
                }
            }
            $this->success("删除成功","AdminStu/index");
        } else {
            $this->error("删除失败！");
        }
    }










}
