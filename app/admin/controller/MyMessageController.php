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

namespace app\admin\controller;
use cmf\controller\AdminBaseController;
use app\portal\model\PortalPostModel;
use app\portal\service\PostService;
use app\portal\model\PortalCategoryModel;
use think\Db;
use app\admin\model\ThemeModel;

class MyMessageController extends AdminBaseController
{
    /**
     * 我的消息列表
     */
    public function index()
    {

        $user_id=cmf_get_current_admin_id();
        $where['s.user_id']=$user_id;
        $field = 's.s_id';
        $articles = Db::table('fn_station_info_relation')
            ->alias('s')
            ->where($where)
            ->field($field)
            ->order("s_id desc")
          /*  ->paginate(10);*/
            ->paginate(10,false,['query'=>request()->param()])->each(function($item, $key){
                $station_info_data = Db::name('station_info') ->alias('si')->where(array('si.id'=>$item['s_id'],'si.post_status'=>1))->join('__USER__ u', 'si.user_id = u.id')->field('si.is_read,si.id,si.create_time,si.post_title,u.user_nickname')->find();
                $item['teacher_name'] = $station_info_data['user_nickname'];
                $item['post_title'] = $station_info_data['post_title'];
                $item['create_time'] = $station_info_data['create_time'];
                $item['is_read'] = $station_info_data['is_read'];
                $item['id'] = $station_info_data['id'];
                 return $item;
            });
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
