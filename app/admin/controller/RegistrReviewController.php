<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;

/**
 * Class UserController
 * @package app\admin\controller
 */
class RegistrReviewController extends AdminBaseController
{

    /**
     * 学生审核列表
     */
    public function index()
    {
        $where   = [];
        $request = input('request.');
        /**搜索条件**/
        $user_login = $this->request->param('user_login');
        if ($user_login) {
            $where['user_login'] = $user_login;
        }
        $keywordComplex = [];
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];
            $keywordComplex['user_login|user_nickname|user_email|mobile']    = ['like', "%$keyword%"];
        }
        $reviews = Db::name('review')
            ->whereOr($keywordComplex)
            ->where($where)
            ->order("create_time DESC")
            ->paginate(10);
        // 获取分页显示
        $page = $reviews->render();
        $this->assign("reviews", $reviews);
        $this->assign('page', $page);
        return $this->fetch();
    }


    /**
     * 管理员编辑
     */
    public function edit()
    {
        $id    = $this->request->param('id', 0, 'intval');
        $user = DB::name('review')->where(["id" => $id])->find();
        $this->assign($user);
        return $this->fetch();
    }

    /**
     * 管理员编辑提交
     */
    public function editPost()
    {
        if ($this->request->isPost()) {
            if ($_POST['user_status'] == 1) {
                $data = Db::name('review')->where(['id' => $_POST['id']])->field('id,review_details', true)->find();
                $userInfo = Db::name("user")->where(['user_login' => $data['user_login']])->find();
                if ($userInfo) {
                    $data['user_type']=4;
                    $data['user_status']=1;
                    $user_result = Db::name("user")->where(['user_login' => $data['user_login']])->update($data);
                } else {
                    $data['user_type']=4;
                    $data['user_status']=1;
                    $user_result = Db::name("user")->insertGetId($data);
                    $RoleUser['user_id']=$user_result;
                    $RoleUser['role_id']=4;
                    Db::name('RoleUser')->insertGetId($RoleUser);
                }
                if ($user_result !== false) {
                    $result = DB::name('review')->update($_POST);
                    if ($result !== false) {
                        $this->success("保存成功！", url('RegistrReview/index'));
                    }else {
                        $this->error("保存失败！", '');
                    }
                }
            }else{
                if(empty($_POST['review_details'])){
                    $this->error("请输入审核说明！", '');
                }
                $data = Db::name('review')->where(['id' => $_POST['id']])->field('id', true)->find();
                $userInfo = Db::name("user")->where(['user_login' => $data['user_login']])->find();
                if ($userInfo) {
                    Db::name("user")->where(['user_login' => $data['user_login']])->update(array('user_status'=>0));
                }
                $result = DB::name('review')->update($_POST);
                if ($result !== false) {
                    $this->success("保存成功！", url('RegistrReview/index'));
                }else {
                    $this->error("保存失败！", '');
                }
            }

        }
    }

    /**
     * 学生审核删除
     */
    public function delete()
    {
        $param = $this->request->param();
        if (isset($param['ids'])) {
            $ids = $this->request->param('ids/a');

            $userInfo = Db::name('review')->where(['id' => ['in', $ids]])->select();
            if (Db::name('review')->where(['id' => ['in', $ids]])->delete() !== false) {
                $this->success("删除成功！");
                foreach ($userInfo as $value ){
                    if ($value['avatar']) {
                        $filePath = $_SERVER['DOCUMENT_ROOT'] . '/upload/' . $value['avatar'];
                        if (file_exists($filePath)) { //检查图片文件是否存在
                            unlink($filePath);
                            Db::name('asset')->where(array('file_path' => $value['avatar']))->delete();
                        }
                    }
                    if ($value['student_card']) {
                        $filePath = $_SERVER['DOCUMENT_ROOT'] . '/upload/' . $value['student_card'];
                        if (file_exists($filePath)) { //检查图片文件是否存在
                            unlink($filePath);
                            Db::name('asset')->where(array('file_path' => $value['student_card']))->delete();
                        }
                    }
                }
            } else {
                $this->error("删除失败！");
            }
        }

        if (isset($param['id'])) {
            $id = $this->request->param('id', 0, 'intval');
            $userInfo = Db::name('review')->find($id);
            if (Db::name('review')->delete($id) !== false) {
                $this->success("删除成功！");
                if ($userInfo['avatar']) {
                    $filePath = $_SERVER['DOCUMENT_ROOT'] . '/upload/' . $userInfo['avatar'];
                    if (file_exists($filePath)) { //检查图片文件是否存在
                        unlink($filePath);
                        Db::name('asset')->where(array('file_path' => $userInfo['avatar']))->delete();
                    }
                }
                if ($userInfo['student_card']) {
                    $filePath = $_SERVER['DOCUMENT_ROOT'] . '/upload/' . $userInfo['student_card'];
                    if (file_exists($filePath)) { //检查图片文件是否存在
                        unlink($filePath);
                        Db::name('asset')->where(array('file_path' => $userInfo['student_card']))->delete();
                    }
                }
            } else {
                $this->error("删除失败！");
            }
        }
    }

    /**
     * 学生批量审核
     */
    public function review()
    {
        $param = $this->request->param();

        if (isset($param['ids'])) {
            $ids = $this->request->param('ids/a');
            $result = Db::name('review')->where(['id' => ['in', $ids]])->update(['user_status' => 1]);
            $this->success("审核成功！", '');
        }

    }


}