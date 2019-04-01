<?php
/**
 * Created by PhpStorm.
 * User: X烦恼 <384576861@qq.com>
 * DateTime: 2018/8/19 13:49
 */
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;

class LogController extends AdminBaseController
{

    /**
     * 日志首页
     * @author X烦恼 <384576861@qq.com>
     */
    public function index() {
        $where=[];
        /**搜索条件**/
        $user_id = $this->request->param('user_id');
        if($user_id){
            $where['a.user_id'] = $user_id;
        }
        $where['user_status'] = 1;
        $where_user['user_status'] = 1;
        $users = Db::name('user')
            ->alias("c")
            ->where($where_user)
            ->order("id DESC")
            ->field("id,user_nickname")
            ->select();

        $data=Db::table("fn_log_manage")
            ->alias("a")
            ->where($where)
            ->field("a.*,b.user_nickname")
            ->join("fn_user b","a.user_id = b.id",'left')
            ->order("action_time DESC,a.listorder AES")
            ->paginate(10);
        $page = $data->render();
        $this->assign("page", $page);
        $this->assign("user_id",$user_id);
        $this->assign("users", $users);
        $this->assign("data", $data);
        return $this->fetch();
    }

    // 删除日志管理
    public function delete() {
        $param           = $this->request->param();
        if (isset($param['id'])) {
            cmf_log_record(null);  //添加日志 $id=对象ID
            $id= $this->request->param('id', 0, 'intval');
            if (Db::name('log_manage')->delete($id) !== false) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
        if (isset($param['ids'])) {
            cmf_log_record(null);  //添加日志 $id=对象ID
            $ids     = $this->request->param('ids/a');
            if (Db::name('log_manage')->where(['id' => ['in', $ids]])->delete() !== false) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }
}