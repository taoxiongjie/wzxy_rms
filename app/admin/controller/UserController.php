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
 * @adminMenuRoot(
 *     'name'   => '管理组',
 *     'action' => 'default',
 *     'parent' => 'user/AdminIndex/default',
 *     'display'=> true,
 *     'order'  => 10000,
 *     'icon'   => '',
 *     'remark' => '管理组'
 * )
 */
class UserController extends AdminBaseController
{

    /**
     * 管理员列表
     * @adminMenu(
     *     'name'   => '管理员',
     *     'parent' => 'default',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员管理',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $content = hook_one('admin_user_index_view');

        if (!empty($content)) {
            return $content;
        }
        $where=[];
        //$admin_id = session('ADMIN_INFO');
        //$where = ["user_type" => 1];
        $request = input('request.');
        $keywordComplex = [];
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];
            $keywordComplex['user_login|user_nickname|user_email|mobile|college']    = ['like', "%$keyword%"];
        }
        /**搜索条件**/
        $userLogin = $this->request->param('user_login');
        $userEmail = trim($this->request->param('user_email'));

        if ($userLogin) {
            $where['user_login'] = ['like', "%$userLogin%"];
        }

        if ($userEmail) {
            $where['user_email'] = ['like', "%$userEmail%"];;
        }
        $where['id']=array('neq',1);
        $where['user_type']=array('eq',2);
        $users = Db::name('user')
            ->whereOr($keywordComplex)
            ->where($where)
            ->order("id DESC")
            ->paginate(10);
        $users->appends(['user_login' => $userLogin, 'user_email' => $userEmail]);
        // 获取分页显示
        $page = $users->render();

        $rolesSrc = Db::name('role')->select();
        $roles    = [];
        foreach ($rolesSrc as $r) {
            $roleId           = $r['id'];
            $roles["$roleId"] = $r;
        }
        $this->assign("page", $page);
        $this->assign("roles", $roles);
        $this->assign("users", $users);
        return $this->fetch();
    }

    /**
     * 管理员添加
     * @adminMenu(
     *     'name'   => '管理员添加',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员添加',
     *     'param'  => ''
     * )
     */
    public function add()
    {
        $content = hook_one('admin_user_add_view');

        if (!empty($content)) {
            return $content;
        }
        $admin_id = session('ADMIN_INFO');
        $role_id=Db::name('role_user')->where(array('user_id'=>$admin_id['id']))->find();
        if($role_id['role_id']=='2'){
            $where['id']=['neq',1];
        }
        $where['status']=1;
        $roles = Db::name('role')->where($where)->order("id DESC")->select();
        $this->assign("roles", $roles);
        return $this->fetch();
    }

    /**
     * 管理员添加提交
     * @adminMenu(
     *     'name'   => '管理员添加提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员添加提交',
     *     'param'  => ''
     * )
     */
    public function addPost()
    {
        if ($this->request->isPost()) {
            /* if (!empty($_POST['role_id']) && is_array($_POST['role_id'])) {*/
            /* $role_ids = $_POST['role_id'];
             unset($_POST['role_id']);*/
            $result = $this->validate($this->request->param(), 'User.add');
            if ($result !== true) {
                $this->error($result);
            } else {
                $_POST['user_type']=2;
                $_POST['user_pass'] = cmf_password($_POST['user_pass']);
                $_POST['create_time'] = time();
                $result             = DB::name('user')->insertGetId($_POST);
                if ($result !== false) {
                    //$role_user_model=M("RoleUser");
                    /*foreach ($role_ids as $role_id) {
                        if (cmf_get_current_admin_id() != 1 && $role_id == 1) {
                            $this->error("为了网站的安全，非网站创建者不可创建超级管理员！");
                        }

                    }*/
                    Db::name('RoleUser')->insert(["role_id" => 2, "user_id" => $result]);
                    $this->success("添加成功！", url("user/index"));
                } else {
                    $this->error("添加失败！");
                }
            }
            /* } else {
                 $this->error("请为此用户指定角色！");
             }*/

        }
    }

    /**
     * 管理员编辑
     * @adminMenu(
     *     'name'   => '管理员编辑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员编辑',
     *     'param'  => ''
     * )
     */
    public function edit()
    {
        $content = hook_one('admin_user_edit_view');

        if (!empty($content)) {
            return $content;
        }

        $id    = $this->request->param('id', 0, 'intval');
        $role_id=Db::name('role_user')->where(array('user_id'=>$id))->find();
        if($role_id['role_id']=='2'){
            $where['id']=['neq',1];
        }
        $where['status']=1;
        $roles = DB::name('role')->where($where)->order("id DESC")->select();
        $this->assign("roles", $roles);
        $role_ids = DB::name('RoleUser')->where(["user_id" => $id])->column("role_id");
        $this->assign("role_ids", $role_ids);

        $user = DB::name('user')->where(["id" => $id])->find();
        $this->assign($user);
        return $this->fetch();
    }

    /**
     * 管理员编辑提交
     * @adminMenu(
     *     'name'   => '管理员编辑提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员编辑提交',
     *     'param'  => ''
     * )
     */
    public function editPost()
    {
        if ($this->request->isPost()) {
       /*     if (!empty($_POST['role_id']) && is_array($_POST['role_id'])) {
                if (empty($_POST['user_pass'])) {
                    unset($_POST['user_pass']);
                } else {
                    $_POST['user_pass'] = cmf_password($_POST['user_pass']);
                }
                $role_ids = $this->request->param('role_id/a');
                unset($_POST['role_id']);*/
                $data['avatar_old']=$_POST['avatar_old'];
                $result = $this->validate($this->request->param(), 'User.edit');

                if ($result !== true) {
                    // 验证失败 输出错误信息
                    $this->error($result);
                } else {
                    unset($_POST['avatar_old']);
                    $result = DB::name('user')->update($_POST);
                    if ($result !== false) {
                       /* $uid = $this->request->param('id', 0, 'intval');
                        DB::name("RoleUser")->where(["user_id" => $uid])->delete();
                        foreach ($role_ids as $role_id) {
                            if (cmf_get_current_admin_id() != 1 && $role_id == 1) {
                                $this->error("为了网站的安全，非网站创建者不可创建超级管理员！");
                            }
                            DB::name("RoleUser")->insert(["role_id" => $role_id, "user_id" => $uid]);
                        }*/
                        if(!empty($data['avatar_old']) &&$_POST['avatar'] != $data['avatar_old'] ){
                            $filePath=$_SERVER['DOCUMENT_ROOT'].'/upload/'.$data['avatar_old'];
                            if(file_exists($filePath))	 { //检查图片文件是否存在
                                unlink($filePath);
                                Db::name('asset')->where(array('file_path'=>$data['avatar_old']))->delete();
                            }
                        }
                        $this->success("保存成功");
                    } else {
                        $this->error("保存失败！");
                    }
                }
            /*} else {
                $this->error("请为此用户指定角色！");
            }*/

        }
    }

    /**
     * 管理员个人信息修改
     * @adminMenu(
     *     'name'   => '个人信息',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员个人信息修改',
     *     'param'  => ''
     * )
     */
    public function userInfo()
    {
        $id   = cmf_get_current_admin_id();
        $user = Db::name('user')->where(["id" => $id])->find();
        $this->assign($user);
        $this->assign('user',$user);
        return $this->fetch();
    }

    /**
     * 管理员个人信息修改提交
     * @adminMenu(
     *     'name'   => '管理员个人信息修改提交',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员个人信息修改提交',
     *     'param'  => ''
     * )
     */
    public function userInfoPost()
    {
        if ($this->request->isPost()) {

            $data             = $this->request->post();

            //$data['birthday'] = strtotime($data['birthday']);
            $data['id']       = cmf_get_current_admin_id();
            $create_result    = Db::name('user')->update($data);;
            if ($create_result !== false) {
                $this->success("保存成功！");
            } else {
                $this->error("保存失败！");
            }
        }
    }

    /**
     * 管理员删除
     * @adminMenu(
     *     'name'   => '管理员删除',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '管理员删除',
     *     'param'  => ''
     * )
     */
    public function delete()
    {
        $param           = $this->request->param();
        if (isset($param['id'])) {
            $id = $this->request->param('id', 0, 'intval');
            if ($id == 1) {
                $this->error("最高管理员不能删除！");
            }
            $userInfo=Db::name('user')->find($id);
            if (Db::name('user')->delete($id) !== false) {
                Db::name("RoleUser")->where(["user_id" => $id])->delete();
                if($userInfo['avatar']){
                    $filePath=$_SERVER['DOCUMENT_ROOT'].'/upload/'.$userInfo['avatar'];
                    if(file_exists($filePath))	 { //检查图片文件是否存在
                        unlink($filePath);
                        Db::name('asset')->where(array('file_path'=>$userInfo['avatar']))->delete();
                    }
                }
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
        if (isset($param['ids'])) {
            $ids     = $this->request->param('ids/a');
            $userInfo = Db::name('user')->where(['id' => ['in', $ids]])->select();
            $result= Db::name('user')->where(['id' => ['in', $ids]])->delete();
            if ($result) {
                Db::name("RoleUser")->where(['id' => ['in', $ids]])->delete();
                foreach ($userInfo as $value) {
                    if($value['avatar']){
                        $filePath=$_SERVER['DOCUMENT_ROOT'].'/upload/'.$value['avatar'];
                        if(file_exists($filePath))	 { //检查图片文件是否存在
                            unlink($filePath);
                            Db::name('asset')->where(array('file_path'=>$value['avatar']))->delete();
                        }
                    }
                }
                $this->success("删除成功！", '');
            }
        }
    }

    /**
     * 停用管理员
     * @adminMenu(
     *     'name'   => '停用管理员',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '停用管理员',
     *     'param'  => ''
     * )
     */
    public function ban()
    {
        $id = $this->request->param('id', 0, 'intval');
        if (!empty($id)) {
            $result = Db::name('user')->where(["id" => $id, "user_type" => 1])->setField('user_status', '0');
            if ($result !== false) {
                $this->success("管理员停用成功！", url("user/index"));
            } else {
                $this->error('管理员停用失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    /**
     * 启用管理员
     * @adminMenu(
     *     'name'   => '启用管理员',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '启用管理员',
     *     'param'  => ''
     * )
     */
    public function cancelBan()
    {
        $id = $this->request->param('id', 0, 'intval');
        if (!empty($id)) {
            $result = Db::name('user')->where(["id" => $id, "user_type" => 1])->setField('user_status', '1');
            if ($result !== false) {
                $this->success("管理员启用成功！", url("user/index"));
            } else {
                $this->error('管理员启用失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    /**
     * 重置管理员密码
     */
    public function reset()
    {
        $id = $this->request->param('id', 0, 'intval');
        if (!empty($id)) {
            $user_pass= cmf_password('123456');

            $result = Db::name('user')->where(["id" => $id])->setField('user_pass', $user_pass);
            if ($result !== false) {
                $this->success("重置密码成功！", url("user/index"));
            } else {
                $this->error('重置密码失败！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    //提交EXECL表格
    public function upload_field_excel_post(){
        //获取上传命令
        $Upload=$_POST['upload'];
        $file_url=$_POST['file_url'];	 //文件路径
        if($Upload){
            if(!empty($file_url)){
                $filePath=$_SERVER['DOCUMENT_ROOT'].'/upload/'.$file_url;
                //如果文件存在
                if(file_exists($filePath))	 { //检查文件是否存在
                    //导入PHPExcel类库，因为PHPExcel没有用命名空间，只能import导入
                    import("Vendor.PHPExcel.PHPExcel");
                    import("Vendor.PHPExcel.PHPExcel.IOFactory.php");
                    $exts=pathinfo($filePath, PATHINFO_EXTENSION);  //文件类型
                    if ($exts == 'xls') {
                        import("Vendor.PHPExcel.PHPExcel.Writer.Excel5");
                        $objReader=\PHPExcel_IOFactory::createReader('Excel5'); //读取
                    } else if ($exts == 'xlsx') {
                        import("Vendor.PHPExcel.PHPExcel.Writer.Excel2007");
                        $objReader=\PHPExcel_IOFactory::createReader('Excel2007'); //读取
                    }
                    //打开文件
                    $objPHPExcel = $objReader->load($filePath,$encode='utf-8');
                    $sheet = $objPHPExcel->getSheet(0);  //Sheet1
                    $highestRow = $sheet->getHighestRow(); // 取得总行数
                    $highestColumn=$sheet->getHighestColumn();   //获取总列数
                    $a=0;
                    $b=0;
                    //从第二行开始
                    for($i=2;$i<=$highestRow;$i++){
                        $id=trim($objPHPExcel->getActiveSheet()->getCell('A'.$i)->getValue());
                        $user_login=trim($objPHPExcel->getActiveSheet()->getCell('B'.$i)->getValue());
                        $user_nickname=trim($objPHPExcel->getActiveSheet()->getCell('C'.$i)->getValue());
                        $sex=trim($objPHPExcel->getActiveSheet()->getCell('D'.$i)->getValue());
                        $college=trim($objPHPExcel->getActiveSheet()->getCell('E'.$i)->getValue());
                        $class=trim($objPHPExcel->getActiveSheet()->getCell('F'.$i)->getValue());
                        $position=trim($objPHPExcel->getActiveSheet()->getCell('G'.$i)->getValue());
                        $job_title=trim($objPHPExcel->getActiveSheet()->getCell('H'.$i)->getValue());
                        $mobile=trim($objPHPExcel->getActiveSheet()->getCell('I'.$i)->getValue());
                        $user_email=trim($objPHPExcel->getActiveSheet()->getCell('J'.$i)->getValue());
                        $user_type=2;
                        if($sex){
                            switch ($sex){
                                case "男";
                                $sex=1;
                                break;
                                case "女";
                                $sex=2;
                                break;
                                case "保密";
                                $sex=0;
                                break;
                            }
                        }
                        $data=[];
                        if($user_login&&$user_type){
                            if(DB::name('user')->where(array("id"=>$id))->count()) {
                                $data['id']     = $id;
                                $data['user_type']     = $user_type;
                                $data['user_login']    = $user_login;
                                $data['user_nickname'] = $user_nickname;
                                $data['sex']         = $sex;
                                $data['college']     = $college;
                                $data['class']       = $class;
                                $data['position']    = $position;
                                $data['job_title']   = $job_title;
                                $data['mobile']      = $mobile;
                                $data['user_email']  = $user_email;
                                $response            = DB::name('user')->update($data);
                                if ($response !== false) {
                                    $role_where['user_id']=$id;
                                    $role_data['role_id']=$user_type;
                                    Db::name('RoleUser')->where($role_where)->update($role_data);
                                    $a++;
                                }

                            }else{
                                $data['user_type']     = $user_type;
                                $data['user_login']    = $user_login;
                                $data['user_nickname'] = $user_nickname;
                                $data['user_pass']     = cmf_password('123456');
                                $data['sex']         = $sex;
                                $data['college']     = $college;
                                $data['class']       = $class;
                                $data['position']    = $position;
                                $data['job_title']   = $job_title;
                                $data['mobile']      = $mobile;
                                $data['user_email']  = $user_email;
                                $data['create_time'] = time();
                                $response            = DB::name('user')->insertGetId($data);
                                if ($response !== false) {
                                    Db::name('RoleUser')->insert(["role_id" => $user_type, "user_id" => $response]);
                                    $a++;
                                }
                            }
                        }else{
                            $b++;
                        }

                    }
                    unlink($filePath);  //删除文件
                    //判断导入成功数量
                    if ($a==$highestRow-1) {
                        $info='导入成功！本次成功导入数量：'.$a.'条,无效数据'.$b.'条';
                        $status=1;
                    }else {
                        $info='导入成功！本次成功导入字段数量：'.$a.'条,无效数据'.$b.'条,与目标数不符！';
                        $status=1;
                    }
                }else{
                    $info="文件不存在,文件上传失败！";
                    $status=0;
                }
            }else{
                $info="请上传文件！";
                $status=0;
            }
        }
        $response=array('status'=>$status,'info'=>$info);
        return ($response);
    }

    public function download_userInfo()
    {
        //先清空缓存  避免表字段信息有残留
        cmf_clear_cache();
        //引入IOFactory.php 文件里面的PHPExcel_IOFactory这个类
        import('PHPExcel.Classes.PHPExcel');
        import('PHPExcel.Classes.PHPExcel.IOFactory.PHPExcel_IOFactory');
        //初始化字段数组
        $fieldsData=array();
        $fieldsData=array("ID","用户名（工号/学号）","真实昵称","性别","学院","专业(班别)","职位","职称","手机","邮箱");

        $where['id']=array('neq',1);
        $where['user_type']=array('eq',2);
        $user_info= DB::name('user')->where($where)->field('id,user_login,user_nickname,sex,college,class,position,job_title,mobile,user_email')->select();
        $user_info = $user_info->toArray();
        foreach ($user_info as &$val){
            if($val['sex']==1){
                $val['sex']="男";
            }elseif ($val['sex']==2){
                $val['sex']="女";
            }
            else{
                $val['sex']="保密";
            }
        }
        $filename='预约管理系统—管理员账户数据表文件';
        $date = date("Y_m_d_H_i_s",time());
        $filename .= "_{$date}.xlsx";
         $PHPExcel = new \PHPExcel();
         $PHPSheet = $PHPExcel->getActiveSheet();
         $PHPSheet->setTitle("账户信息");//给当前活动sheet设置名称
        $key = ord("A");
        foreach($fieldsData as $v){
            $colum = chr($key);
            $PHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
            //$PHPSheet->getColumnDimension($colum)->setAutoSize(true);
            $PHPSheet->getColumnDimension($colum)->setWidth(15);
            $key++;
        }

        $column = 2;
        foreach($user_info as $key => $rows){ //行写入
            $span = ord("A");
            foreach($rows as $keyName=>$value){// 列写入
                $j = chr($span);
                $PHPSheet->setCellValue($j.$column, $value);
                //$PHPSheet->getColumnDimension($j)->setAutoSize(true);
                $PHPSheet->getColumnDimension($colum)->setWidth(15);
                $span++;
            }
            $column++;
        }
         $PHPWriter = \PHPExcel_IOFactory::createWriter($PHPExcel,"Excel2007");//创建生成的格式
         header('Content-Disposition: attachment;filename='.$filename);//下载下来的表格名
         header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
         $PHPWriter->save("php://output");
    }

    //模板下载
    public function download()
    {
        $file_url  = dirname($_SERVER['DOCUMENT_ROOT']) . '/update/template/批量添加账户模板.xls';
        $file_url  = iconv("utf-8", "gb2312", $file_url);
        $file_name = '批量添加账户模板.xls';
        if (file_exists($file_url)) {
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

        } else {
            $this->error("下载失败！");
        }
    }



}