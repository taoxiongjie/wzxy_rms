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
 * Class ReservePositionController
 * @package app\admin\controller
 */
class ReserveController extends AdminBaseController
{

    /**
     * 机房信息
     * @author X烦恼 <384576861@qq.com>
     */
    public function index()
    {

        $where = [];
        /**搜索条件**/
        $param = $this->request->param();
        if ($param) {
            $this->assign("reserve_id",$param['id']);
        }

        $data = Db::name('engineroom_position')
            ->where($where)
            ->order(" list_order AES  ,create_time AES ")
            ->select();
        // 获取分页显示
        $this->assign("data",$data);
        return $this->fetch();

    }

    /**
     * 机房信息
     * @author X烦恼 <384576861@qq.com>
     */
    public function engineroom_list()
    {
        $param = $this->request->param();
        $mark= $param['mark'];

        $where['status']=1;
        $data = Db::name('engineroom_position')
            ->where($where)
            ->order(" list_order AES  ,create_time AES ")
            ->select();
        $this->assign("data",$data);
        $listwhere['status']=1;
        if ($mark) {
            $listwhere['p_mark']=$mark;
            $this->assign('building_name', $param['building_name']);
        }
        $reserve_id=$this->request->param('reserve_id');
        if ($reserve_id) {
            $this->assign("reserve_id",$reserve_id);
        }
        $list= DB::name('engineroom_info')->where($listwhere)->select();
        $this->assign("list",$list);
        $this->assign("building_mark",$mark);
       /* $dateArr=[];
        $weekarray=array("日","一","二","三","四","五","六");
        for ($i=0;$i<7;$i++){
            $date_timme=date("Y-m-d",strtotime("+".$i."day"));
            $date_week= $weekarray[date("w",strtotime($date_timme))];
            $date=date("m月d日",strtotime("+".$i."day"));
            $dateArr[]=$date."/周".$date_week;
        }*/

        $dateArr=[];
        $weekarray=array("日","一","二","三","四","五","六");
        for ($i=0;$i<7;$i++){
            $date_timme=date("Y-m-d",strtotime("+".$i."day"));
            $date_week= $weekarray[date("w",strtotime($date_timme))];
            $date=date("m月d日",strtotime("+".$i."day"));
            $dateArr[]=array('week'=>$date." 周".$date_week,'dateInfo'=>$date_timme,'time_week'=>date("w",strtotime($date_timme)));
        }




        $room_mark =  trim($this->request->param('room_mark'));
        $room_name =  trim($this->request->param('room_name'));
        if(empty($room_mark)){
            $markArr=$list->toArray();
            $room_mark=$markArr[0]['mark'];
            $room_name=$list[0]['name'];
        }
        $weekArr=['sun','mon','tues','wed','thur','fri','sat'];


        foreach ($dateArr as $key=>&$value){
            $where=[];
            $where['o.status']=1;
            $where['e.e_mark']=$room_mark;
            $where["e.".$weekArr[$value['time_week']]]=1;
            if($key==0){
                $time    =strtotime(date('Y-m-d').date("H:00"));
                $timeData=Db::table("fn_open_time_info")
                    ->alias("o")
                    ->where($where)
                    ->field("o.start_time,o.end_time,o.id as time_type,e.".$weekArr[$value['time_week']]."")
                    ->join("fn_engineroom_open_time e","o.id = e.time_type",'left')
                    ->order("o.list_order AES,o.id AES")
                    ->select()->toArray();
                foreach ($timeData as $k=>&$val_time){
                    $new_time   =strtotime(date('Y-m-d').$val_time['end_time']);
                    if($time>=$new_time){
                        unset($timeData[$k]);
                    }
                }
                $value['open_time']=$timeData;
            }else{
                $timeData=Db::table("fn_open_time_info")
                    ->alias("o")
                    ->where($where)
                    ->field("o.start_time,o.end_time,o.id as time_type ,e.".$weekArr[$value['time_week']]."")
                    ->join("fn_engineroom_open_time e","o.id = e.time_type",'left')
                    ->order("o.list_order AES,o.id AES")
                    ->select()->toArray();
                $value['open_time']=$timeData;
            }

        }

        $this->assign("dateArr",$dateArr);
        $this->assign("room_mark",$room_mark);
        $this->assign("room_name",$room_name);
        return $this->fetch();
    }
    /**
     * 添加
     */
    public function room_open_time(){

        if ($this->request->isPost()) {
            $mark=$_POST['room_mark'];
            $dateArr=[];
            $weekarray=array("日","一","二","三","四","五","六");
            for ($i=0;$i<7;$i++){
                $date_timme=date("Y-m-d",strtotime("+".$i."day"));
                $date_week= $weekarray[date("w",strtotime($date_timme))];
                $date=date("m月d日",strtotime("+".$i."day"));
                $dateArr[]=array('week'=>$date."周".$date_week,'time_type'=>date("w",strtotime($date_timme)));
            }

            $weekArr=['sun','mon','tues','wed','thur','fri','sat'];
            foreach ($dateArr as &$value){
                $where=[];
                $where['o.status']=1;
                $where['e.e_mark']=$mark;
                $where["e.".$weekArr[$value['time_type']]]=1;
                $timeData=Db::table("fn_open_time_info")
                    ->alias("o")
                    ->where($where)
                    ->field("o.name,e.".$weekArr[$value['time_type']]."")
                    ->join("fn_engineroom_open_time e","o.id = e.time_type",'left')
                    ->order("o.list_order AES,o.id AES")
                    ->select()->toArray();
                $value['open_time']=$timeData;
            }

        }
        echo json_encode($dateArr);
    }

    /**
     * 座位预约
     */
    public function seat_reserve()
    {
        $param = $this->request->param();
        $where['room_mark']=$param['room_mark'];
        $list= Db::name('seat_info')->where($where)->order('id ASE')->select()->toArray();
        foreach ($list as &$value){
            $where['room_mark']=$value['room_mark'];
            $where['dateInfo']=$param['dateInfo'];
            $where['time_type']=$param['time_type'];
            $where['seat_id']=$value['seat_id'];
            $data=DB::name('reserve_info')->where($where)->find();
            if($data){
                $value['status']=0;
            }
        }
        $Datetime= Db::name('open_time_info')->where(array('id'=>$param['time_type']))->find();

        $reserve_id=$this->request->param('reserve_id');
        if ($reserve_id) {
            $this->assign("reserve_id",$reserve_id);
        }

        $this->assign("list", $list);
        $time=$Datetime['start_time'].'-'.$Datetime['end_time'];
        $this->assign("building_mark",$param['building_mark']);
        $this->assign("building_name",$param['building_name']);
        $this->assign("room_name",$param['room_name']);
        $this->assign("room_mark",$param['room_mark']);
        $this->assign("week",$param['week']);
        $this->assign("dateInfo",$param['dateInfo']);

        $this->assign("time",$time);
        $this->assign("time_type",$param['time_type']);
        return $this->fetch();
    }
    /**
     * 座位预约
     */
    public function seat_reserve_post()
    {
        $param = $this->request->param();
        $param['user_id']=cmf_get_current_admin_id();
        $reserve_where['building_mark']=$param['building_mark'];
        $reserve_where['room_mark']=$param['room_mark'];
        $reserve_where['time_type']=$param['time_type'];
        $reserve_where['dateInfo']=$param['dateInfo'];
        $reserve_where['seat_id']=$param['seat_id'];
        $reserve_where['status']=array('neq',3); //不等于3  没有取消
        $reserve_info = DB::name('reserve_info')->where($reserve_where)->find();
        if (empty($reserve_info)) {
            if($param['reserve_id']) {
                    $update_where['id']=$param['reserve_id'];
                    unset($param['reserve_id']);
                    $result = DB::name('reserve_info')->where($update_where)->update($param);
                    cmf_log_record($update_where['id']);  //添加日志 $id=对象ID
                    if ($result !== false) {
                        $response = ['status' => 1, 'reserve_unm' => "您的 " . $update_where['id']." 号预约单已修改成功，"];
                    } else {
                        $response = ['status' => 0];
                    }
            }else{
                $where['dateInfo']=$param['dateInfo'];
                $where['time_type']=$param['time_type'];
                $where['user_id']=cmf_get_current_admin_id();
                $data = DB::name('reserve_info')->where($where)->find();
                if (empty($data)) {
                    unset($param['reserve_id']);
                    $param['create_time'] = date('Y-m-d H:i:s', time());
                    $result               = DB::name('reserve_info')->insertGetId($param);
                    cmf_log_record($result);  //添加日志 $id=对象ID
                    if ($result !== false) {
                        $response = ['status' => 1, 'reserve_unm' => "预约成功，您的预约单号为:" . $result];
                    } else {
                        $response = ['status' => 0];
                    }
                } else {
                    $response = ['status' => 2];
                }
            }
        } else {
            $response = ['status' => 3];
        }
        return  Json($response);
    }

    /**
     * 预约取消
     */
    public function seat_reserve_cancel(){
        $param = $this->request->param();
        if(isset($param['id'])){
            $id = $this->request->param('id');
            if (Db::name('reserve_info')->where(['id' => $id])->update(['status' =>3])!==false) {
                $this->success("取消成功！");
            } else {
                $this->error("取消失败！");
            }
        }
    }

    /**
     * 添加
     */
    public function add()
    {
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
            $result = $this->validate($this->request->param(), 'ReservePosition');
            if ($result !== true) {
                $this->error($result);
            } else {
                $_POST['create_time'] = date('Y-m-d H:i:s', time());
                $result = DB::name('reserve')->insertGetId($_POST);
                cmf_log_record($result);  //添加日志 $id=对象ID
                if ($result !== false) {
                    $this->success("添加成功！", url("ReservePosition/index"));
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
        $data = DB::name('reserve')->where(["id" => $id])->find();
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
            $result = $this->validate($this->request->param(), 'ReservePosition.edit');
            if ($result !== true) {
                // 验证失败 输出错误信息
                $this->error($result);
            } else {
                $result = DB::name('reserve')->update($_POST);
                cmf_log_record($result);  //添加日志 $id=对象ID
                if ($result !== false) {
                    $this->success("保存成功！", url("ReservePosition/index"));
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
            if (Db::name('reserve')->delete($id) !== false) {
                cmf_log_record($id);  //添加日志 $id=对象ID
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
        if (isset($param['ids'])) {
            $ids = $this->request->param('ids/a');
            if (Db::name('reserve')->where(['id' => ['in', $ids]])->delete() !== false) {
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
        parent::listOrders(Db::name('reserve'));
        $this->success("排序更新成功！", '');
    }
    /**
     * 启用/未启用
     */
    public function toggle(){
        $param = $this->request->param();
        if(isset($param['ids'])  && isset($param['display'])){
            $ids = $this->request->param('ids/a');
            if (Db::name('reserve')->where(['id' => ['in', $ids]])->update(['status' =>1])!==false) {
                $this->success("启用成功！");
            } else {
                $this->error("启用失败！");
            }
        }
        if(isset($param['ids']) && isset($param['hide'])){
            $ids = $this->request->param('ids/a');
            if (Db::name('reserve')->where(['id' => ['in', $ids]])->update(['status' =>0])!==false) {
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
                            $response= DB::name('reserve')->insertGetId($data);;
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
                    $sql = "TRUNCATE TABLE ". config("database.prefix")."reserve";
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
                                $response= DB::name('reserve')->insertGetId($data);;
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