<?php
// +----------------------------------------------------------------------
// | Author:X烦恼 <384576861@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use app\admin\model\RouteModel;
use cmf\controller\AdminBaseController;
use app\admin\model\TagModel;
use think\Db;
use app\portal\model\PortalPostModel;
use app\portal\service\PostService;
use app\portal\model\PortalCategoryModel;
/**
 * Class AssetStatisticsController
 * @package app\admin\controller
 */
class AssetStatisticsController extends AdminBaseController
{

    /**
     * 资产信息
     * @author X烦恼 <384576861@qq.com>
     */
    public function index()
    {
        $where = [];
        /**搜索条件**/
        $name =  trim($this->request->param('name'));
        if ($name) {
            $where['name'] = ['like', "%$name%"];
        }
        $data = Db::name('asset_type')
            ->where($where)
            ->order(" list_order AES  ,create_time AES ")
            ->paginate(10);
        // 获取分页显示
        $page = $data->render();
        $this->assign("data",$data);
        $this->assign("page", $page);
        return $this->fetch();
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
            $result = $this->validate($this->request->param(), 'asset_statistics');
            if ($result !== true) {
                $this->error($result);
            } else {
                $_POST['create_time'] = date('Y-m-d H:i:s', time());
                $result = DB::name('asset_type')->insertGetId($_POST);
                cmf_log_record($result);  //添加日志 $id=对象ID
                if ($result !== false) {
                    $this->success("添加成功！", url("asset_statistics/index"));
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
        $data = DB::name('asset_type')->where(["id" => $id])->find();
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
            $result = $this->validate($this->request->param(), 'asset_statistics.edit');
            if ($result !== true) {
                // 验证失败 输出错误信息
                $this->error($result);
            } else {
                $result = DB::name('asset_type')->update($_POST);
                cmf_log_record($result);  //添加日志 $id=对象ID
                if ($result !== false) {
                    $this->success("保存成功！", url("asset_statistics/index"));
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
            if (Db::name('asset_type')->delete($id) !== false) {
                cmf_log_record($id);  //添加日志 $id=对象ID
                $this->success("删除成功！");
            } else {
                $this->error("删除失败！");
            }
        }
        if (isset($param['ids'])) {
            $ids = $this->request->param('ids/a');
            if (Db::name('asset_type')->where(['id' => ['in', $ids]])->delete() !== false) {
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
        parent::listOrders(Db::name('asset_type'));
        $this->success("排序更新成功！", '');
    }
    /**
     * 启用/未启用
     */
    public function toggle(){
        $param = $this->request->param();
        if(isset($param['ids'])  && isset($param['display'])){
            $ids = $this->request->param('ids/a');
            if (Db::name('asset_type')->where(['id' => ['in', $ids]])->update(['status' =>1])!==false) {
                $this->success("启用成功！");
            } else {
                $this->error("启用失败！");
            }
        }
        if(isset($param['ids']) && isset($param['hide'])){
            $ids = $this->request->param('ids/a');
            if (Db::name('asset_type')->where(['id' => ['in', $ids]])->update(['status' =>0])!==false) {
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
                    $a=0;
                    $b=0;
                    //从第二行开始
                    for($i=2;$i<=$highestRow;$i++){
                        $id=trim($objPHPExcel->getActiveSheet()->getCell('A'.$i)->getValue());
                        $name=trim($objPHPExcel->getActiveSheet()->getCell('B'.$i)->getValue());
                        $remark=trim($objPHPExcel->getActiveSheet()->getCell('C'.$i)->getValue());
                        if($name) {
                            if (DB::name('asset_type')->where(array("id" => $id))->count()) {
                                $data['name']   = $name;
                                $data['remark'] = $remark;
                                $response       = DB::name('asset_type')->where(array("id"=>$id))->update($data);
                                if ($response !== false) {
                                    $a++;
                                }
                            } else {
                                $data['name']        = $name;
                                $data['remark']      = $remark;
                                $data['create_time'] = Date('Y-m-d H:i:s', time());
                                $response            = DB::name('asset_type')->insertGetId($data);;
                                if ($response !== false) {
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
    //模板下载
    public function download(){
        $file_url=dirname($_SERVER['DOCUMENT_ROOT']).'/update/template/资产信息模板.xls';
        $file_url = iconv("utf-8", "gb2312", $file_url);
        $file_name='资产信息模板.xls';
        //basename  文件名
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

    public function download_assetInfo()
    {
        //先清空缓存  避免表字段信息有残留
        cmf_clear_cache();
        //引入IOFactory.php 文件里面的PHPExcel_IOFactory这个类
        import('PHPExcel.Classes.PHPExcel');
        import('PHPExcel.Classes.PHPExcel.IOFactory.PHPExcel_IOFactory');
        //初始化字段数组
        $fieldsData=array();
        $fieldsData=array("ID","资产类型名称","描述");

        $asset_info= DB::name('asset_type')->field('id,name,remark')->select();
        $asset_info = $asset_info->toArray();
        $filename='资产类型数据表文件';
        $date = date("Y_m_d_H_i_s",time());
        $filename .= "_{$date}.xlsx";
        $PHPExcel = new \PHPExcel();
        $PHPSheet = $PHPExcel->getActiveSheet();
        $PHPSheet->setTitle("资产类型信息");//给当前活动sheet设置名称
        $key = ord("A");
        foreach($fieldsData as $v){
            $colum = chr($key);
            $PHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
            $PHPSheet->getColumnDimension($colum)->setAutoSize(true);
            $key++;
        }
        $column = 2;
        foreach($asset_info as $key => $rows){ //行写入
            $span = ord("A");
            foreach($rows as $keyName=>$value){// 列写入
                $j = chr($span);
                $PHPSheet->setCellValue($j.$column, $value);
                $PHPSheet->getColumnDimension($j)->setAutoSize(true);
                $span++;
            }
            $column++;
        }
        $PHPWriter = \PHPExcel_IOFactory::createWriter($PHPExcel,"Excel2007");//创建生成的格式
        header('Content-Disposition: attachment;filename='.$filename);//下载下来的表格名
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $PHPWriter->save("php://output");
    }
    /**
     * 分类统计
     * @author X烦恼 <384576861@qq.com>
     */
    public function class_statistics(){


        //获取楼宇层级
        $buildingList= Db::name('building_info')
            ->where(["status"=>1])
            ->order(" list_order AES  ,create_time AES ")
            ->select();
        $buildingArr=array();
        foreach ($buildingList as &$val1 ){
            //获取楼层
            $floorList= Db::name('floor_info')->where(["status"=>1,"b_mark"=>$val1['mark']]) ->order(" list_order AES  ,create_time AES ")->select();
            $floorArr=array();
            foreach ($floorList as &$val2 ){
                $roomList= Db::name('room_info')->where(["status"=>1,"f_mark"=>$val2['mark']]) ->order(" list_order AES  ,create_time AES ")->select();
                $roomArr=array();
               foreach ($roomList as $val3){
                $roomArr[]=array('id'=>$val3['id']+0,'name'=>$val3['name'],'mark'=>$val3['mark'],'b_mark'=>$val3['b_mark'],'f_mark'=>$val3['f_mark']);
               }
                $floorArr[]=array('id'=>$val2['id']+0,'name'=>$val2['name'],'mark'=>$val2['mark'],'b_mark'=>$val2['b_mark'],'child'=>$roomArr);
            }
            $buildingArr[]=array('id'=>$val1['id']+0,'name'=>$val1['name'],'mark'=>$val1['mark'],'child'=>$floorArr);
        }
        //获取设备类型
         $asset_type=Db::table("fn_asset_info")
             ->alias("a")
             ->field("t.id,t.name")
             ->join("fn_asset_type t","a.asset_type = t.id",'left')
             ->distinct(true)
             ->select();


        $html="";
        if($buildingArr){
            foreach ($buildingArr as &$vo){
                $html.="<li><span class=\"folder overview\"><label><input type=\"checkbox\" name=\"building_ids\" data-mark=\"".$vo['mark']."\" \">".$vo['name']."</label></span>";
                $html.="  <ul>";
                $floorData=$vo['child'];
                foreach ($floorData as &$floor){
                    $html.="<li><span class=\"folder\"><label><input type=\"checkbox\" name=\"floor_ids\" data-mark=\"".$floor['mark']."\"  \">".$floor['name']."</label></span>";
                    $html.="  <ul>";
                    $roomData=$floor['child'];
                    foreach ($roomData as &$room){
                        $html.="<li><span class=\"file\"><label><input type=\"checkbox\" name=\"room_ids\"  data-mark=\"".$room['mark']."\" \">&nbsp;".$room['name']."</label></span>";
                        $html.="</li>";
                    }
                    $html.="  </ul>";
                    $html.="</li>";
                }
                $html.="  </ul>";
                $html.="</li>";
            }
        }

        $this->assign('asset_type',$asset_type);
        $this->assign('html',$html);
        return $this->fetch();

    }
    // 分类查询提交
    public function class_statistics_post(){
        $status=0;
        $info="查询失败";
        $where=[];
        $building_ids =$this->request->param('building_ids');
        $floor_ids =$this->request->param('floor_ids');
        $room_ids =$this->request->param('room_ids');
        $device_type_ids =$this->request->param('device_type_ids');

        //通过 盘点范围  设备类型 条件  获取查询结果
        $searchResult=getClassDeviceInfoList($building_ids,$floor_ids,$room_ids,$device_type_ids);
        if($searchResult){
            $status=1;
            $info=$searchResult['resultInfo'];
        }
        $response=array('status'=>$status,'info'=>$info,'device_data_list'=>$searchResult['device_data_list']);
        echo json_encode($response);
    }

    // 综合统计
    public function complex_statistics(){

        //获取楼宇层级
        $buildingList= Db::name('building_info')
            ->where(["status"=>1])
            ->order(" list_order AES  ,create_time AES ")
            ->select();
        $buildingArr=array();
        foreach ($buildingList as &$val1 ){
            //获取楼层
            $floorList= Db::name('floor_info')->where(["status"=>1,"b_mark"=>$val1['mark']]) ->order(" list_order AES  ,create_time AES ")->select();
            $floorArr=array();
            foreach ($floorList as &$val2 ){
                $roomList= Db::name('room_info')->where(["status"=>1,"f_mark"=>$val2['mark']]) ->order(" list_order AES  ,create_time AES ")->select();
                $roomArr=array();
                foreach ($roomList as $val3){
                    $roomArr[]=array('id'=>$val3['id']+0,'name'=>$val3['name'],'mark'=>$val3['mark'],'b_mark'=>$val3['b_mark'],'f_mark'=>$val3['f_mark']);
                }
                $floorArr[]=array('id'=>$val2['id']+0,'name'=>$val2['name'],'mark'=>$val2['mark'],'b_mark'=>$val2['b_mark'],'child'=>$roomArr);
            }
            $buildingArr[]=array('id'=>$val1['id']+0,'name'=>$val1['name'],'mark'=>$val1['mark'],'child'=>$floorArr);
        }
        $html="";
        if($buildingArr){
            foreach ($buildingArr as &$vo){
                $html.="<li><span class=\"folder overview\"><label><input type=\"checkbox\" name=\"building_ids\" data-mark=\"".$vo['mark']."\" \">".$vo['name']."</label></span>";
                $html.="  <ul>";
                $floorData=$vo['child'];
                foreach ($floorData as &$floor){
                    $html.="<li><span class=\"folder\"><label><input type=\"checkbox\" name=\"floor_ids\" data-mark=\"".$floor['mark']."\"  \">".$floor['name']."</label></span>";
                    $html.="  <ul>";
                    $roomData=$floor['child'];
                    foreach ($roomData as &$room){
                        $html.="<li><span class=\"file\"><label><input type=\"checkbox\" name=\"room_ids\"  data-mark=\"".$room['mark']."\" \">&nbsp;".$room['name']."</label></span>";
                        $html.="</li>";
                    }
                    $html.="  </ul>";
                    $html.="</li>";
                }
                $html.="  </ul>";
                $html.="</li>";
            }
        }

        $asset_type=array(0=>array('id'=>0,"name"=>"报废"),
            1=>array('id'=>2,"name"=>"维修"),
            2=>array('id'=>3,"name"=>"借入"),
            3=>array('id'=>4,"name"=>"借出"),
        );
        $this->assign('asset_type',$asset_type);
        $this->assign('html',$html);
        return $this->fetch();

    }

    // 综合查询提交
    public function complex_statistics_post(){
        $status=0;
        $info="查询失败";
        $where=[];
        $building_ids =$this->request->param('building_ids');
        $floor_ids =$this->request->param('floor_ids');
        $room_ids =$this->request->param('room_ids');
        $statistics_type_ids =$this->request->param('statistics_type_ids');

        //通过 盘点范围  设备类型 条件  获取查询结果
        $searchResult=getComplexDeviceInfoList($building_ids,$floor_ids,$room_ids,$statistics_type_ids);
        if($searchResult){
            $status=1;
            $info=$searchResult['resultInfo'];
        }
        $response=array('status'=>$status,'info'=>$info,'device_data_list'=>$searchResult['device_data_list']);
        echo json_encode($response);
    }


}