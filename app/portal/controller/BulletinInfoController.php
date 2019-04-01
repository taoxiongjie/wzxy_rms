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


class BulletinInfoController extends HomeBaseController
{
    /**
     * 文章列表
     */
    public function index()
    {

        $where = [];
        /**搜索条件**/
        $name =  trim($this->request->param('name'));
        if ($name) {
            $where['post_title'] = ['like', "%$name%"];
        }
        $where['post_status']=1;
        $articles = Db::name('bulletin_info')->where($where)->order("is_top DESC,list_order AES,update_time DESC")->paginate(5);
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
            Db::name('bulletin_info')->insertGetId($data);
            $this->success('添加成功!', url('BulletinInfo/index'));
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
            $data['post_content']=cmf_replace_content_file_url(htmlspecialchars_decode($data['post_content']));
            $data['update_time']=time();
            $post= Db::name('bulletin_info')->update($data);
            if($post){
            $this->success('保存成功!');
            }else{
                $this->error("保存失败！");
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
           $top= Db::name('bulletin_info')->where(['id' => ['in', $ids]])->update(['is_top' => 1]);

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

}
