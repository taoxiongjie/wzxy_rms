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
namespace app\user\controller;

use think\Db;
use cmf\controller\AdminBaseController;

class AdminAssetController extends AdminBaseController
{
    /**
     * 资源管理列表
     * @adminMenu(
     *     'name'   => '资源管理',
     *     'parent' => '',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => 'file',
     *     'remark' => '资源管理列表',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $join   = [
            ['__USER__ u', 'a.user_id = u.id']
        ];
        $result = Db::name('asset')->field('a.*,u.user_login,u.user_email,u.user_nickname')
            ->alias('a')->join($join)
            ->order('create_time', 'DESC')
            ->paginate(10);
        $this->assign('assets', $result->items());
        $this->assign('page', $result->render());
        return $this->fetch();
    }

    /**
     * 删除文件
     * @adminMenu(
     *     'name'   => '删除文件',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '删除文件',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $id            = $this->request->param('id');
        $file_filePath = Db::name('asset')->where('id', $id)->value('file_path');
        $file          = 'upload/' . $file_filePath;
        $res = true;
        if (file_exists($file)) {
            $res = unlink($file);
        }
        if ($res) {
            Db::name('asset')->where('id', $id)->delete();
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }


    //文件下载
    public function download(){
        $id            = $this->request->param('id');
        $file_filePath = Db::name('asset')->where('id', $id)->field('file_path,filename')->find();
        $filePath=$file_filePath['file_path'];
        $file_url=$_SERVER['DOCUMENT_ROOT'].'/upload/'.$filePath;
        $file_url = iconv("utf-8", "gb2312", $file_url);
        $file_name=$file_filePath['filename'];
        if(file_exists($file_url)){
            ob_end_clean();
            ob_start();
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header("Content-Disposition: attachment; filename=\"$file_name\"");
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file_url));
            readfile($file_url);

        }else{
            $this->error("下载失败！");
        }
    }



}