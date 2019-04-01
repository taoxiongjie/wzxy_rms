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

class StationInfoController extends AdminBaseController
{
    /**
     * 站内消息列表
     * @adminMenu(
     *     'name'   => '站内消息',
     *     'parent' => 'portal/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '站内消息列表',
     *     'param'  => ''
     * )
     */
    public function index()
    {

        $where=[];
        $param = $this->request->param();
        $startTime = empty($param['start_time']) ? 0 : strtotime($param['start_time']);
        $endTime   = empty($param['end_time']) ? 0 : strtotime($param['end_time']);
        if (!empty($startTime) && !empty($endTime)) {
            $where['a.create_time'] = [['>= time', $startTime], ['<= time', $endTime]];
        } else {
            if (!empty($startTime)) {
                $where['a.create_time'] = ['>= time', $startTime];
            }
            if (!empty($endTime)) {
                $where['a.create_time'] = ['<= time', $endTime];
            }
        }
        $keyword = empty($param['keyword']) ? '' : $param['keyword'];
        if (!empty($keyword)) {
            $where['a.post_title'] = ['like', "%$keyword%"];
        }
        $join = [  ['__USER__ u', 'a.user_id = u.id']  ];
     /*   $join1 = [  ['__USER__ us', 'a.student_id = us.id']  ];*/

        $field = 'a.*,u.user_nickname as teacher_name ';
        $articles = Db::table('fn_station_info')
            ->alias('a')
            ->field($field)
            ->join($join)
           /* ->join($join1)*/
            ->where($where)
            ->order("create_time desc")
          /*  ->paginate(10);*/
            ->paginate(10,false,['query'=>request()->param()])->each(function($item, $key){
                $nameArr="";
                $name = Db::name('station_info_relation') ->alias('s')->where(array('s.s_id'=>$item['id']))->join('__USER__ u', 's.user_id = u.id')->field('u.user_nickname')->select();
                foreach ($name as $value){
                    if($nameArr){
                        $nameArr.=','.$value['user_nickname'];
                    }else{
                        $nameArr.=$value['user_nickname'];
                    }
                }
                $item['student_name'] = $nameArr;
                 return $item;
            });

        $this->assign('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('end_time', isset($param['end_time']) ? $param['end_time'] : '');
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');
        $this->assign('page', $articles->render());
        $this->assign('articles',$articles);

        return $this->fetch();
    }

    /**
     * 添加站内消息

     */
    public function add()
    {

        $where['user_type'] = 4; //学生
        $list = Db::name('user') ->where($where)->order("create_time DESC")->select();
        $this->assign('list',$list);
        return $this->fetch();
    }
    /**
     * 学生列表
     */
    public function stu_list()
    {
        $where   = [];
        $request = input('request.');
        if (!empty($request['user_login'])) {
            $user_login=$request['user_login'];
            $where['user_login'] =['like', "%$user_login%"];
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
     * 添加站内消息提交
     */
    public function addPost()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();

            $data['user_id']=cmf_get_current_admin_id();
            $data['create_time']=time();
            $data['post_content']=htmlspecialchars_decode($data['post_content']);
            $student_ids= $data['student_ids'];
            unset($data['student_ids']);
            $station_info_id= Db::name('station_info')->insertGetId($data);
            if($station_info_id){
                $ids=explode(',',$student_ids);
                foreach ($ids as $val){
                    $user_id['user_id']=$val;
                    $user_id['s_id']=$station_info_id;
                    Db::name('station_info_relation')->insertGetId($user_id);
                }
                $this->success('添加成功!', url('StationInfo/index'));
            }else{
                $this->success('添加失败!');
            }

        }
    }

    /**
     * 查看站内消息

     * )
     */
    public function view()
    {
        $id = $this->request->param('id', 0, 'intval');
        $article= Db::name('station_info')->where(array('id'=>$id))->find();
        $article['post_content']=cmf_replace_content_file_url(htmlspecialchars_decode($article['post_content']));
        $this->assign('article', $article);
        return $this->fetch();
    }

    /**
     * 编辑站内消息

     * )
     */
    public function edit()
    {
        $id = $this->request->param('id', 0, 'intval');
        $post= Db::name('station_info')->where(array('id'=>$id))->find();
        $post['post_content']=cmf_replace_content_file_url(htmlspecialchars_decode($post['post_content']));
        $name = Db::name('station_info_relation') ->alias('s')->where(array('s.s_id'=>$id))->join('__USER__ u', 's.user_id = u.id')->field('u.user_nickname,s.user_id')->select()->toArray();
        $names=array_column($name,'user_nickname');
        $user_ids=array_column($name,'user_id');
        $user_ids = implode(',', array_values($user_ids));
        $this->assign('names', $names);
        $this->assign('user_ids', $user_ids);
        $this->assign( $post);
        $this->assign('post', $post);
        return $this->fetch();
    }

    /**
     * 编辑站内消息提交

     */
    public function editPost()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $data['post_content']=htmlspecialchars(cmf_replace_content_file_url(htmlspecialchars_decode($data['post_content']), true));
            $student_ids= $data['student_ids'];
            $id=$data['id'];
            unset($data['student_ids']);
            $data['is_read']=0;
            $post= Db::name('station_info')->update($data);
            $user_ids= Db::name('station_info_relation')->where(array('s_id'=>$id))->column('user_id');
            if($post !==false){
                $ids=explode(',',$student_ids);
                $stu_ids1=array_diff($ids,$user_ids); //新增
                $stu_ids2=array_diff($user_ids,$ids); //减少
                if($stu_ids1){
                    foreach ($stu_ids1 as $val1){
                        $user_id['user_id']=$val1;
                        $user_id['s_id']=$id;
                        Db::name('station_info_relation')->insertGetId($user_id);
                    }
                }
                if($stu_ids2){
                    foreach ($stu_ids2 as $val2){
                        $user_id['user_id']=$val2;
                        $user_id['s_id']=$id;
                        Db::name('station_info_relation')->where($user_id)->delete();
                    }
                }
                $this->success('保存成功!');
            }else{
                $this->error("保存失败！");
            }
        }
    }

    /**
     * 站内消息删除
     * @adminMenu(
     *     'name'   => '站内消息删除',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '站内消息删除',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $param           = $this->request->param();
        if (isset($param['id'])) {
            $id           = $this->request->param('id', 0, 'intval');
            if (Db::name('station_info')->delete($id) !== false) {
                $this->success("删除成功！", '');
            }
        }
        if (isset($param['ids'])) {
            $ids     = $this->request->param('ids/a');
            if (Db::name('station_info')->where(['id' => ['in', $ids]])->delete() !== false) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }

        }
    }

    /**
     * 站内消息置顶
     */
    public function top()
    {
        $param           = $this->request->param();
        if (isset($param['ids']) && isset($param["yes"])) {
            $ids = $this->request->param('ids/a');
            Db::name('station_info')->where(['id' => ['in', $ids]])->update(['is_top' => 1]);
            $this->success("置顶成功！", '');
        }
        if (isset($_POST['ids']) && isset($param["no"])) {
            $ids = $this->request->param('ids/a');
            Db::name('station_info')->where(['id' => ['in', $ids]])->update(['is_top' => 0]);
            $this->success("取消置顶成功！", '');
        }
    }

    /**
     * 站内消息排序
     */
    public function listOrder()
    {
        parent::listOrders(Db::name('station_info'));
        $this->success("排序更新成功！", '');
    }

}
