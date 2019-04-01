<?php
// +----------------------------------------------------------------------
// | Author:X烦恼 <384576861@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\RouteModel;
use cmf\controller\AdminBaseController;
use app\admin\model\TagModel;
use think\Db;

/**
 * Class OpenTimeInfoController
 * @package app\admin\controller
 */
class OpenTimeInfoController extends AdminBaseController
{

    /**
     * 产地信息
     * @author X烦恼 <384576861@qq.com>
     */
    public function index()
    {
        $mark= $this->request->param('mark');
        $where = [];
        /**搜索条件**/
        $name =  trim($this->request->param('name'));
        if ($name) {
            $where['name'] = ['like', "%$name%"];
        }
        $where['room_mark'] =$mark;
        $data = Db::name('open_time_info')
            ->where($where)
            ->order(" list_order AES  ,create_time AES ")
            ->paginate(10);
        // 获取分页显示
        $page = $data->render();

        $this->assign("mark", $mark);
        $this->assign("data",$data);
        $this->assign("page", $page);
        return $this->fetch();
    }
    /**
     * 添加
     */
    public function add()
    {
        $mark= $this->request->param('mark');
        $TagModel = new TagModel();
        $this->assign("arrStatus", $TagModel::$STATUS);
        $this->assign("mark", $mark);
        return $this->fetch();
    }
    /**
     * 添加提交
     */
    public function addPost()
    {
        if ($this->request->isPost()) {
            $mark= $this->request->param('room_mark');
            $result = $this->validate($this->request->param(), 'OpenTimeInfo');
            if ($result !== true) {
                $this->error($result);
            } else {
                if($_POST['end_time'] >$_POST['start_time'] ){
                    //is_time_cross
                   $timeInfo=DB::name('open_time_info')->where(['room_mark'=>$mark])->select()->toArray();

                   foreach ($timeInfo as $value){
                      /*$time=is_time_cross($_POST['start_time'],$_POST['end_time'],$value['start_time'],$value['end_time']);
                        if($time==false){
                            $this->error("时间重合！");
                        }else{*/
                            $time1=is_time_cross(strtotime($value['start_time']),strtotime($value['end_time']),strtotime($_POST['start_time']),strtotime($_POST['end_time']));
                            if($time1==false){
                                $this->error("时间重合！");
                            }
                        /*}*/
                   }
                    $_POST['create_time'] = date('Y-m-d H:i:s', time());
                    $result = DB::name('open_time_info')->insertGetId($_POST);
                    cmf_log_record($result);  //添加日志 $id=对象ID
                    if ($result !== false) {
                        $this->success("添加成功！");
                    } else {
                        $this->error("添加失败！");
                    }
                }else{
                    $this->error("结束时间不能小于起始时间");
                }

            }
        }
    }
    /**
     * 编辑
     */
    public function edit()
    {
        $id = $this->request->param('id', 0, 'intval');
        cmf_log_record($id);  //添加日志 $id=对象ID
        $data = DB::name('open_time_info')->where(["id" => $id])->find();
        $TagModel = new TagModel();
        $this->assign("arrStatus", $TagModel::$STATUS);
        $this->assign($data);
        $this->assign("data",$data);
        return $this->fetch();
    }
    /**
     * 编辑提交
     */
    public function editPost()
    {
        if ($this->request->isPost()) {
            $result = $this->validate($this->request->param(), 'OpenTimeInfo.edit');
            if ($result !== true) {
                // 验证失败 输出错误信息
                $this->error($result);
            } else {
                $result = DB::name('open_time_info')->update($_POST);
                cmf_log_record($result);  //添加日志 $id=对象ID
                if ($result !== false) {
                    $this->success("保存成功！", url("OpenTimeInfo/index"));
                } else {
                    $this->error("保存失败！");
                }
            }


        }
    }
    /**
     * 删除
     */
    public function delete()
    {
        $param           = $this->request->param();
        if (isset($param['id'])) {
            $id = $this->request->param('id', 0, 'intval');
            if (Db::name('open_time_info')->delete($id) !== false) {
                cmf_log_record($id);  //添加日志 $id=对象ID
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
        if (isset($param['ids'])) {
            $ids = $this->request->param('ids/a');
            if (Db::name('open_time_info')->where(['id' => ['in', $ids]])->delete() !== false) {
                cmf_log_record($ids);  //添加日志 $id=对象ID
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
    }
    /**
     * 排序
     */
    public function listOrder()
    {
        parent::listOrders(Db::name('open_time_info'));
        $this->success("排序更新成功！", '');
    }
    /**
     * 启用/未启用
     */
    public function toggle(){
        $param = $this->request->param();
        if(isset($param['ids'])  && isset($param['display'])){
            $ids = $this->request->param('ids/a');
            if (Db::name('open_time_info')->where(['id' => ['in', $ids]])->update(['status' =>1])!==false) {
                $this->success("启用成功！");
            } else {
                $this->error("启用失败！");
            }
        }
        if(isset($param['ids']) && isset($param['hide'])){
            $ids = $this->request->param('ids/a');
            if (Db::name('open_time_info')->where(['id' => ['in', $ids]])->update(['status' =>0])!==false) {
                $this->success("禁用成功！");
            } else {
                $this->error("禁用失败！");
            }
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
                        $name=trim($objPHPExcel->getActiveSheet()->getCell('A'.$i)->getValue());
                        $remark=trim($objPHPExcel->getActiveSheet()->getCell('B'.$i)->getValue());
                        if($name){
                            $data['name']=$name;
                            $data['remark']=$remark;
                            $data['create_time']=Date('Y-m-d H:i:s',time());
                            $response= DB::name('open_time_info')->insertGetId($data);;
                            if($response!==false){
                                $a++;
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
        }else{
            //替换
            if(!empty($file_url)){
                $filePath=$_SERVER['DOCUMENT_ROOT'].'/upload/'.$file_url;
                //如果文件存在
                if(file_exists($filePath))	 { //检查文件是否存在
                    $sql = "TRUNCATE TABLE ". config("database.prefix")."open_time_info";
                    $result =Db::execute($sql);
                    if($result !==false){
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
                        for($i=2;$i<=$highestRow;$i++){
                            $name=trim($objPHPExcel->getActiveSheet()->getCell('A'.$i)->getValue());
                            $remark=trim($objPHPExcel->getActiveSheet()->getCell('B'.$i)->getValue());
                            if($name){
                                $data['name']=$name;
                                $data['remark']=$remark;
                                $data['create_time']=Date('Y-m-d H:i:s',time());
                                $response= DB::name('open_time_info')->insertGetId($data);;
                                if($response!==false){
                                    $a++;
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
        }
        $response=array('status'=>$status,'info'=>$info);
        return ($response);
    }
    //模板下载
    public function download(){
        $file_url=dirname($_SERVER['DOCUMENT_ROOT']).'/update/template/产地信息模板.xls';
        $file_url = iconv("utf-8", "gb2312", $file_url);
        $file_name='产地信息模板.xls';
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