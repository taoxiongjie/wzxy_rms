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
 * Class EngineroomInfoController
 * @package app\admin\controller
 */
class EngineroomInfoController extends AdminBaseController
{

    /**
     * 机房信息
     * @author X烦恼 <384576861@qq.com>
     */
    public function index()
    {
        $where = [];
        /**搜索条件**/
        $name =  trim($this->request->param('name'));

        if ($name) {
            $where['b.name'] = ['like', "%$name%"];
            $this->assign('name', $name);
        }
        $p_mark =  trim($this->request->param('p_mark'));
        if ($p_mark) {
            $where['p_mark'] = $p_mark;
            $place=array('mark'=>$p_mark);
        }else{
            $place=array('mark'=>"");
        }
        $place_data = Db::name('engineroom_position')->where(["status"=>1])->select();
        $this->assign("place_data",$place_data);
        $this->assign("place",$place);
        $engineroom_info=Db::table("fn_engineroom_info")
            ->alias("b")
            ->where($where)
            ->field("b.*,p.name as place_name")
            ->join("fn_engineroom_position p","b.p_mark = p.mark",'left')
            ->order("create_time DESC,b.list_order AES")
            ->paginate(10);
        // 获取分页显示
        $page = $engineroom_info->render();
        $engineroom_info=$engineroom_info->all();
        foreach ($engineroom_info as &$value){
            $value['seat_num'] = Db::name('seat_info')->where(["room_mark"=>$value['mark']])->count();

        }
        $this->assign("engineroom_info",$engineroom_info);
        $this->assign("page", $page);
        return $this->fetch();
    }
    /**
     * 添加
     */
    public function add()
    {
        $place_data = Db::name('engineroom_position')->where(["status"=>1])->select();
        $this->assign("place_data",$place_data);
        $TagModel = new TagModel();
        $this->assign("arrStatus", $TagModel::$STATUS);
        return $this->fetch();
    }
    /**
     * 添加提交
     */
    public function addPost()
    {
        if ($this->request->isPost()) {

            $result = $this->validate($this->request->param(), 'EngineroomInfo.add');
            if ($result !== true) {
                $this->error($result);
            } else {
               /* if($_POST['open_num']>$_POST['seat_num']){
                    $this->error("开放座位数不能大于座位数");
                }*/
                if($_POST['end_time']<$_POST['start_time']){
                    $this->error("截止时间不能小于起始时间");
                }
                $_POST['create_time'] = date('Y-m-d H:i:s', time());
                $result = DB::name('engineroom_info')->insertGetId($_POST);
                cmf_log_record($result);  //添加日志 $id=对象ID
                if ($result !== false) {
                    $this->success("添加成功！", url("EngineroomInfo/index"));
                } else {
                    $this->error("添加失败！");
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
        $data = DB::name('engineroom_info')->where(["id" => $id])->find();

        $place_data = Db::name('engineroom_position')->where(["status"=>1])->select();
        $this->assign("place_data",$place_data);
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
            $result = $this->validate($this->request->param(), 'EngineroomInfo.edit');
            if ($result !== true) {
                // 验证失败 输出错误信息
                $this->error($result);
            } else {
                /*if($_POST['open_num']>$_POST['seat_num']){
                    $this->error("开放座位数不能大于座位数");
                }*/
                if($_POST['end_time']<$_POST['start_time']){
                    $this->error("截止时间不能小于起始时间");
                }
                $result = DB::name('engineroom_info')->update($_POST);
                cmf_log_record($result);  //添加日志 $id=对象ID
                if ($result !== false) {
                    $this->success("保存成功！", url("EngineroomInfo/index"));
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
            $engineroomInfo = Db::name('engineroom_info')->find($id);
            if (Db::name('engineroom_info')->delete($id) !== false) {
                cmf_log_record($id);  //添加日志 $id=对象ID
                $this->success("删除成功！");
                if ($engineroomInfo['icon_url']) {
                    $filePath = $_SERVER['DOCUMENT_ROOT'] . '/upload/' . $engineroomInfo['icon_url'];
                    if (file_exists($filePath)) { //检查图片文件是否存在
                        unlink($filePath);
                        Db::name('asset')->where(array('file_path' => $engineroomInfo['icon_url']))->delete();
                    }
                }
            } else {
                $this->error("删除失败！");
            }
        }
        if (isset($param['ids'])) {
            $ids = $this->request->param('ids/a');
            $engineroomInfo = Db::name('engineroom_info')->where(['id' => ['in', $ids]])->select();
            if (Db::name('engineroom_info')->where(['id' => ['in', $ids]])->delete() !== false) {
                cmf_log_record($ids);  //添加日志 $id=对象ID
                $this->success("删除成功！");
                foreach ($engineroomInfo as $value ){
                    if ($value['icon_url']) {
                        $filePath = $_SERVER['DOCUMENT_ROOT'] . '/upload/' . $value['icon_url'];
                        if (file_exists($filePath)) { //检查图片文件是否存在
                            unlink($filePath);
                            Db::name('asset')->where(array('file_path' => $value['icon_url']))->delete();
                        }
                    }
                }
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
        parent::listOrders(Db::name('engineroom_info'));
        $this->success("排序更新成功！", '');
    }
    /**
     * 启用/未启用
     */
    public function toggle(){
        $param = $this->request->param();
        if(isset($param['ids'])  && isset($param['display'])){
            $ids = $this->request->param('ids/a');
            if (Db::name('engineroom_info')->where(['id' => ['in', $ids]])->update(['status' =>1])!==false) {
                $this->success("启用成功！");
            } else {
                $this->error("启用失败！");
            }
        }
        if(isset($param['ids']) && isset($param['hide'])){
            $ids = $this->request->param('ids/a');
            if (Db::name('engineroom_info')->where(['id' => ['in', $ids]])->update(['status' =>0])!==false) {
                $this->success("禁用成功！");
            } else {
                $this->error("禁用失败！");
            }
        }
    }

    /**
     * 开放时间设置
     */
    public function time_setting()
    {
        $mark= $this->request->param('mark');
     /*   $open_time=Db::table("fn_engineroom_open_time")
            ->alias("e")
            ->where(array('e_mark'=>$mark,'status'=>1))
            ->join("fn_open_time_info o","e.time_type = o.id",'left')
            ->field("e.*,o.name,o.id")
            ->select();*/
        $timeData=Db::name('open_time_info')->where(['room_mark'=>$mark,'status'=>1])->select();
        $time = $timeData->toArray();
        foreach ($time as &$value){
            $open_time=Db::name('engineroom_open_time')->where(array('e_mark'=>$mark,'time_type'=>$value['id']))->find();
            $value['mon']=$open_time['mon'];
            $value['tues']=$open_time['tues'];
            $value['wed']=$open_time['wed'];
            $value['thur']=$open_time['thur'];
            $value['fri']=$open_time['fri'];
            $value['sat']=$open_time['sat'];
            $value['sun']=$open_time['sun'];
        }
        $this->assign("time", $time);
        $this->assign("mark", $mark);
        return $this->fetch();
    }
    /**
     * 开放时间提交
     */
    public function time_setting_post(){
        if ($this->request->isPost()) {
            $data=$_POST;
            $mark=$_POST['mark'];
            unset($data['mark']);
            $timeArr=[];
            $weekArr=['mon','tues','wed','thur','fri','sat','sun'];
           foreach ($data as $val1){
               $time=[];
               $where=[];
               $dataArr= array_keys($val1);
               foreach ($dataArr as $val2){
                   $keyArr=explode('_',$val2);
                   $time['e_mark']=$mark;
                   $time['time_type']=$keyArr[1];
                   $time[$keyArr[0]]=1;
                   if(!in_array($keyArr[1],$timeArr)){
                       array_push($timeArr,$keyArr[1]);
                   }
               }
                foreach ($weekArr as $k=>$val2){
                   if(!array_key_exists($weekArr[$k],$time)){
                        $time[$weekArr[$k]]=0;
                    }
                }
               $where['e_mark']=$mark;
               $where['time_type']=$keyArr[1];
               $result=Db::name('engineroom_open_time')->where($where)->find();
              if($result){
                  Db::name('engineroom_open_time')->where($where)->update($time);
              }else{
                  Db::name('engineroom_open_time')->insertGetId($time);
              }
            }
             $e_where['e_mark']=$mark;
             $open_time_info = Db::name('engineroom_open_time')->field('time_type')->where($e_where)->select();
               foreach($open_time_info as $k2=>$v2){
                   if(!in_array($v2['time_type'],$timeArr)){
                       $deletewhere['time_type'] = $v2['time_type'];
                       $deletewhere['e_mark'] = $mark;
                       Db::name('engineroom_open_time')->where($deletewhere)->delete();
                   }
               }
        }
        $this->success("设置成功");
    }



    /**
     * 座位信息
     */
    public function seat_info()
    {
        $param= $this->request->param();
        $where['room_mark']=$param['mark'];
        //$where['building_mark']=$param['b_mark'];
        $list= Db::name('seat_info')->where($where)->order('id ASE')->select();
        $this->assign("list", $list);
        $this->assign("mark", $param['mark']);
        $this->assign("seat_num", $param['seat_num']);
        return $this->fetch();
    }
    /**
     * 座位信息添加
     */
    public function seat_add()
    {
        $param= $this->request->param();
        $this->assign("mark", $param['mark']);
        $this->assign("seat_num", $param['seat_num']);
        $TagModel = new TagModel();
        $this->assign("arrStatus", $TagModel::$STATUS);
        return $this->fetch();
    }
    /**
     * 座位信息添加提交
     */
    public function seat_add_post()
    {
        if ($this->request->isPost()) {

            $result = $this->validate($this->request->param(), 'EngineroomInfo.seat_add');
            if ($result !== true) {
                $this->error($result);
            } else {

                   $count=DB::name('seat_info')->where(array('room_mark'=>$_POST['room_mark'],'seat_id'=>$_POST['seat_id']))->count();
                   if(!$count){
                       $seat_num=$_POST['seat_num'];
                       unset($_POST['seat_num']);
                       $result = DB::name('seat_info')->insertGetId($_POST);
                       cmf_log_record($result);  //添加日志 $id=对象ID
                       if ($result !== false) {
                           $this->success("添加成功！", url("EngineroomInfo/seat_info",array('mark'=>$_POST['room_mark'],'seat_num'=>$seat_num)));
                       } else {
                           $this->error("添加失败！");
                       }
                   }else {
                       $this->error("编号已存在！");
                   }

            }
        }
    }
    /**
     * 座位信息启用/未启用
     */
    public function seat_toggle(){
        $param = $this->request->param();
        if(isset($param['id'])  && isset($param['display'])){
            $id = $this->request->param('id');
            if (Db::name('seat_info')->where(['id' =>$id])->update(['status' =>1])!==false) {
                $this->success("启用成功！");
            } else {
                $this->error("启用失败！");
            }
        }
        if(isset($param['id']) && isset($param['hide'])){
            $id = $this->request->param('id');
            if (Db::name('seat_info')->where(['id' =>$id])->update(['status' =>0])!==false) {
                $this->success("禁用成功！");
            } else {
                $this->error("禁用失败！");
            }
        }
    }
    /**
     *  座位信息编辑
     */
    public function seat_edit()
    {
        $param = $this->request->param();

        $id=trim($param['id']);
        $seat_id=trim($param['seat_id']);
        $mark=trim($param['mark']);
        $count=DB::name('seat_info')->where(array('room_mark'=>$mark,'seat_id'=>$seat_id,'id'=>array('neq',$id)))->count();
        if(!$count) {
            //更新座位编号
            if (Db::name('seat_info')->where(['id' => $id])->update(['seat_id' => $seat_id]) !== false) {
                $status = 1;
            } else {
                $status = 3;
            }
        }else {
            $status = 2;
        }
        $response=['status'=>$status];
        return  Json($response);
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
                        $place_id=trim($objPHPExcel->getActiveSheet()->getCell('C'.$i)->getValue());
                        if($name){
                            $data['name']=$name;
                            $data['remark']=$remark;
                            $data['place_id']=$place_id;
                            $data['create_time']=Date('Y-m-d H:i:s',time());
                            $response= DB::name('engineroom_info')->insertGetId($data);;
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
                    $sql = "TRUNCATE TABLE ". config("database.prefix")."engineroom_info";
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
                            $place_id=trim($objPHPExcel->getActiveSheet()->getCell('C'.$i)->getValue());
                            if($name){
                                $data['name']=$name;
                                $data['remark']=$remark;
                                $data['place_id']=$place_id;
                                $data['create_time']=Date('Y-m-d H:i:s',time());
                                $response= DB::name('engineroom_info')->insertGetId($data);;
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
        $file_url=dirname($_SERVER['DOCUMENT_ROOT']).'/update/template/品牌分类模板.xls';
        $file_url = iconv("utf-8", "gb2312", $file_url);
        $file_name='品牌分类模板.xls';
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