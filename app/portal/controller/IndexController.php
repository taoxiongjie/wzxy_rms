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
class IndexController extends HomeBaseController
{
    public function _initialize()
    {

    }
    //前端首页
    public function index()
    {
        $request=$this->is_mobile();
        if($request){
            $id   = cmf_get_current_user_id();
            if($id){


            $role_ids = DB::name('RoleUser')->where(["user_id" => $id])->find();
            $this->assign("role_ids", $role_ids);

            $where['post_status']=1;
            $bulletin_info = Db::name('bulletin_info')->where($where)
                ->order("is_top DESC,list_order AES,update_time DESC")
                ->paginate(3);
            $this->assign("bulletin_info", $bulletin_info);

            $my_where['s.user_id']=$id;
            $articles = Db::table('fn_station_info_relation')
                ->alias('s')
                ->where($my_where)
                ->join('__STATION_INFO__ si', 's.s_id = si.id')
                ->order("si.is_read asc")
                ->field('si.is_read,si.id,si.create_time,si.post_title')
                ->paginate(6);
            $this->assign('articles',$articles);

            return $this->fetch();
            }else{
                return redirect(url("portal/login/index"));
            }
        }else{
            $admin_id = session('ADMIN_ID');
            if (!empty($admin_id)) {//已经登录
                return redirect(url("admin/Index/index"));
            } else {
                $config    = cmf_get_option('admin_login');
                $this->assign($config);
                return redirect(url("admin/Index/index"));
            }
        }
    }
    function is_mobile()
    {
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        $is_pc = (strpos($agent, 'windows nt')) ? true : false;
        $is_mac = (strpos($agent, 'mac os')) ? true : false;
        $is_iphone = (strpos($agent, 'iphone')) ? true : false;
        $is_android = (strpos($agent, 'android')) ? true : false;
        $is_ipad = (strpos($agent, 'ipad')) ? true : false;

        if($is_iphone){
            return  true;
        }
        if($is_android){
            return  true;
        }
        if($is_ipad){
            return  true;
        }
        if($is_pc){
            return  false;
        }
        if($is_mac){
            return  false;
        }
    }
    //前端首页
    public function content()
    {

        return $this->fetch(':content');
    }
    /**
     * 退出登录
     */
    public function logout()
    {
        session("user", null);//只有前台用户退出
        return redirect($this->request->root() . "/");
    }


}
