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

class MyMessageController extends HomeBaseController
{
    /**
     * 我的消息列表
     */
    public function index()
    {

        $where = [];
        /**搜索条件**/
        $name =  trim($this->request->param('name'));
        if ($name) {
            $where['si.post_title'] = ['like', "%$name%"];
        }
        $user_id   = cmf_get_current_user_id();
        $where['s.user_id']=$user_id;
        $articles = Db::table('fn_station_info_relation')
            ->alias('s')
            ->where($where)
            ->join('__STATION_INFO__ si', 's.s_id = si.id')
            ->join('__USER__ u', 's.user_id = u.id')
            ->order("si.is_read asc")
            ->field('si.is_read,si.id,si.create_time,si.post_title,u.user_nickname')
            ->paginate(5);

        $this->assign('page', $articles->render());
        $this->assign('articles',$articles);

        return $this->fetch();
    }


    /**
     * 查看我的消息

     * )
     */
    public function view()
    {
        $id = $this->request->param('id', 0, 'intval');
        $article= Db::name('station_info')->where(array('id'=>$id))->find();
        if($article){
            $article['post_content']=cmf_replace_content_file_url(htmlspecialchars_decode($article['post_content']));
            Db::name('station_info')->where(array('id'=>$id))->update(['is_read'=>1]);
        }
        $this->assign('article', $article);
        return $this->fetch();
    }


}
