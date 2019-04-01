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

class BulletinManageController extends AdminBaseController
{
    /**
     * 文章列表
     * @adminMenu(
     *     'name'   => '文章管理',
     *     'parent' => 'portal/AdminIndex/default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '文章列表',
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

        $field = 'a.*,u.user_nickname';
        $articles = Db::table('fn_bulletin_info')
            ->alias('a')
            ->field($field)
            ->join($join)
            ->where($where)
            ->order("is_top DESC,list_order AES,update_time desc")
            ->paginate(10);


        $this->assign('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('end_time', isset($param['end_time']) ? $param['end_time'] : '');
        $this->assign('keyword', isset($param['keyword']) ? $param['keyword'] : '');
        $this->assign('page', $articles->render());
        $this->assign('articles',$articles);

        return $this->fetch();
    }

    /**
     * 添加文章

     */
    public function add()
    {
        return $this->fetch();
    }

    /**
     * 添加文章提交
     */
    public function addPost()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $data['user_id']=cmf_get_current_admin_id();
            $data['create_time']=time();
            $data['update_time']=time();
            //$data['post_content']=htmlspecialchars_decode($data['post_content']);
            $data['post_content']=htmlspecialchars(cmf_replace_content_file_url(htmlspecialchars_decode($data['post_content']), true));

            $id=Db::name('bulletin_info')->insertGetId($data);
            if($id){
                $html_url = $this->toHtml($data['post_content'],$id);
                $html['file_url']=$html_url;
                $html['id']=$id;
                $post= Db::name('bulletin_info')->update($html);
                $this->success('添加成功!', url('BulletinManage/index'));
            }else{
                $this->error("添加失败！");
            }

        }

    }

    /**
     * 查看文章

     * )
     */
    public function view()
    {
        $id = $this->request->param('id', 0, 'intval');
        $article= Db::name('bulletin_info')->where(array('id'=>$id))->find();
        $article['post_content']=cmf_replace_content_file_url(htmlspecialchars_decode($article['post_content']));
        $this->assign('article', $article);
        return $this->fetch();
    }

    /**
     * 编辑文章

     * )
     */
    public function edit()
    {
        $id = $this->request->param('id', 0, 'intval');
        $post= Db::name('bulletin_info')->where(array('id'=>$id))->find();
         $post['post_content']=cmf_replace_content_file_url(htmlspecialchars_decode($post['post_content']));



        $this->assign('post', $post);
        return $this->fetch();
    }

    /**
     * 编辑文章提交

     */
    public function editPost()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();

            $html_url = $this->toHtml(htmlspecialchars_decode($data['post_content']),$data['id']);
            $data['file_url']=$html_url;
            $data['update_time']=time();
            if($data['is_top']=="1"&&$data['post_status']=="0"){
                $this->error("保存失败！,顶置消息不能直接下架。");
            }else{
                $data['post_content']=htmlspecialchars(cmf_replace_content_file_url(htmlspecialchars_decode($data['post_content']), true));
                $post= Db::name('bulletin_info')->update($data);

                if($post){
                    if($data['is_top']=="1"){
                        $where['is_top']=1;
                        $where['id']=array('neq',$data['id']);
                        $top_post= Db::name('bulletin_info')->where($where)->update(['is_top'=>0]);

                        if($top_post !==false) {
                            $this->success('保存成功!', url('BulletinManage/index'));
                        }else{
                            $this->error("保存失败！");
                        }
                    }else{
                        $this->success('保存成功!', url('BulletinManage/index'));
                    }
                }else{
                    $this->error("保存失败！");
                }
            }
        }
    }

    /**
     * 文章删除
     * @adminMenu(
     *     'name'   => '文章删除',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '文章删除',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $param           = $this->request->param();
        if (isset($param['id'])) {
            $id           = $this->request->param('id', 0, 'intval');
            if (Db::name('bulletin_info')->delete($id) !== false) {
                $this->success("删除成功！", '');
            }
        }
        if (isset($param['ids'])) {
            $ids     = $this->request->param('ids/a');
            if (Db::name('bulletin_info')->where(['id' => ['in', $ids]])->delete() !== false) {
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }

        }
    }

    /**
     * 文章置顶
     */
    public function top()
    {
        $param           = $this->request->param();
        if (isset($param['ids']) && isset($param["yes"])) {
            $ids = $this->request->param('ids/a');
            Db::name('bulletin_info')->where(['id' => ['in', $ids]])->update(['is_top' => 1]);
            $this->success("置顶成功！", '');
        }
        if (isset($_POST['ids']) && isset($param["no"])) {
            $ids = $this->request->param('ids/a');
            Db::name('bulletin_info')->where(['id' => ['in', $ids]])->update(['is_top' => 0]);
            $this->success("取消置顶成功！", '');
        }
    }

    /**
     * 文章排序
     */
    public function listOrder()
    {
        parent::listOrders(Db::name('bulletin_info'));
        $this->success("排序更新成功！", '');
    }

    public function toHtml($html_content,$id){
        //生成HTML文件

        $app="html";
        $htmlDir=$_SERVER['DOCUMENT_ROOT'].'/'.$app.'/';

        $htmlName="bulletin".$id;
        $filedir=$htmlDir.$htmlName.'.html';
        $dir = dirname($filedir);
        if(!is_dir($dir)){
            mkdir($dir,0777,true);
        }
        $htmlContnt='<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<head>
				<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
				<title>机房公约</title>
				<style type="text/css">
				 
               .main{
                    
                    background-color: #fff;
                    border-radius: 20px;
                    width: 1100px;
                    height: 100%;
                    margin: auto;
                    position: absolute;
                    
                    left: 0;
                    right: 0;
                     
                }
 
				</style>
				</head>
				<body>
				<div class="main">';
        $htmlContnt.=$html_content;
        $htmlContnt.='
                 </div>
				</body>
				</html>';
        $file_pointer = fopen($filedir,"w");
        fwrite($file_pointer,$htmlContnt);
        fclose($file_pointer);

        $html_url =$app.'/'.$htmlName.'.html';
        return $html_url;
    }


}
