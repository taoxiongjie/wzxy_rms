<?php
// +----------------------------------------------------------------------
// | Author:X烦恼 <384576861@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;
use cmf\controller\AdminBaseController;
use app\admin\model\ScrapInfoModel;
use think\Db;

/**
 * Class ScrapInfoController
 * @package app\admin\controller
 */
class ScrapInfoController extends AdminBaseController
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
       /* $request = input('request.');
        $keywordComplex = [];
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];
            $keywordComplex['building_name|floor_name|room_name|supplier|purchaser']    = ['like', "%$keyword%"];
        }*/
        if ($name) {
            $where['a.name'] = ['like', "%$name%"];
            $this->assign('name', $name);
        }

        $scrap_info_data=Db::table("fn_scrap_info")
            ->alias("r")
           // ->whereOr($keywordComplex)
            ->where($where)
            ->field("r.*,a.name ,u.user_nickname")
            ->join("fn_asset_info a","r.asset_id = a.id",'left')
            ->join("fn_user u","r.user_id = u.id",'left')
            ->order("r.list_order AES,r.update_time DESC")
            ->paginate(10);

        // 获取分页显示
        $page = $scrap_info_data->render();

        $this->assign("scrap_info_data",$scrap_info_data);
        $this->assign("page", $page);
        return $this->fetch();
    }
    /**
     * 添加
     */
    public function add()
    {
        /*$asset_type_data = Db::name('asset_type')->where(["status"=>1])->select();
        $this->assign("asset_type_data",$asset_type_data);
        $AssetModel = new AssetModel();
        $this->assign("arrStatus", $AssetModel::$STATUS);
        return $this->fetch();*/
        $where = [];
        /**搜索条件**/
        $name =  trim($this->request->param('name'));
        $request = input('request.');
        $keywordComplex = [];
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];
            $keywordComplex['building_name|floor_name|room_name|supplier|purchaser']    = ['like', "%$keyword%"];
        }
        if ($name) {
            $where['a.name'] = ['like', "%$name%"];
            $this->assign('name', $name);
        }
        $asset_type =  trim($this->request->param('asset_type'));
        if ($asset_type) {
            $where['asset_type'] = $asset_type;
            $type=array('id'=>$asset_type);
        }else{
            $type=array('id'=>"");
        }
        $where['a.status'] =1;
        $asset_type_data = Db::name('asset_type')->where(["status"=>1])->select();
        $this->assign("asset_type_data",$asset_type_data);
        $this->assign("type",$type);
        $scrap_info_data=Db::table("fn_asset_info")
            ->alias("a")
            ->whereOr($keywordComplex)
            ->where($where)
            ->field("a.*,t.name as asset_type_name,u.user_nickname")
            ->join("fn_asset_type t","a.asset_type = t.id",'left')
            ->join("fn_user u","a.user_id = u.id",'left')
            ->order("a.list_order AES,a.create_time DESC")
            ->paginate(10);
        // 获取分页显示
        $page = $scrap_info_data->render();

        $this->assign("scrap_info_data",$scrap_info_data);
        $this->assign("page", $page);
        return $this->fetch();
    }
    /**
     * 添加提交
     */
    public function addPost()
    {
        if ($this->request->isPost()) {
            $result = $this->validate($this->request->param(), 'ScrapInfo.add');
            if ($result !== true) {
                $this->error($result);
            } else {
                $_POST['create_time'] = date('Y-m-d H:i:s', time());
                $_POST['user_id'] =cmf_get_current_admin_id();
                $result = DB::name('scrap_info')->insertGetId($_POST);
                cmf_log_record($result);  //添加日志 $id=对象ID
                if ($result !== false) {
                    $this->success("添加成功！", url("ScrapInfo/index"));
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

        $data=Db::table("fn_scrap_info")
            ->alias("r")
            // ->whereOr($keywordComplex)
            ->where(["r.id" => $id])
            ->field("r.*,a.name,a.id as asset_id ,u.user_nickname")
            ->join("fn_asset_info a","r.asset_id = a.id",'left')
            ->join("fn_user u","r.user_id = u.id",'left')
            ->find();
        $ScrapInfoModel = new ScrapInfoModel();
        $this->assign("arrStatus", $ScrapInfoModel::$STATUS);
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
            $result = $this->validate($this->request->param(), 'ScrapInfo');
            if ($result !== true) {
                // 验证失败 输出错误信息
                $this->error($result);
            } else {
                unset($_POST['name']);
                $_POST['update_time']=date('Y-m-d H:i',time());
                $result = DB::name('scrap_info')->update($_POST);
                cmf_log_record($result);  //添加日志 $id=对象ID
                if ($result !== false) {
                    $result_asset_info = DB::name('asset_info')->where(['id' => $_POST['asset_id']])->update(['status' => $_POST['status']]);

                    $this->success("保存成功！", url("ScrapInfo/index"));
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
            $repair_info=Db::name('scrap_info')->where(['id' => $id])->find();
            if (Db::name('scrap_info')->delete($id) !== false) {
                cmf_log_record($id);  //添加日志 $id=对象ID
                DB::name('asset_info')->where(['id' => $repair_info['asset_id']])->update(['status' =>1]);
                $this->success("删除成功！", url("ScrapInfo/index"));
            } else {
                $this->error("删除失败！");
            }
        }
        if (isset($param['ids'])) {
            $ids = $this->request->param('ids/a');
            $repair_info=Db::name('scrap_info')->where(['id' => ['in', $ids]])->select();
            if (Db::name('scrap_info')->where(['id' => ['in', $ids]])->delete() !== false) {
                cmf_log_record($ids);  //添加日志 $id=对象ID
                foreach ($repair_info as $value ){
                    DB::name('asset_info')->where(['id' => $value['asset_id']])->update(['status' =>1]);
                }
                $this->success("删除成功！", url("ScrapInfo/index"));
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
        parent::listOrders(Db::name('scrap_info'));
        $this->success("排序更新成功！", '');
    }
    /**
     * 报废处理
     */
    public function handle(){
        $param = $this->request->param();
        $ids = $this->request->param('ids/a');
        if ($ids!="") {
            $a="";
            foreach ($ids as $val){
                if($a==""){
                    $a=$val;

                }else{
                    $a.=','.$val;
                }

            }
            $this->assign("ids", $a);
            return $this->fetch();
        }else{
            $id = $this->request->param('id');
            $this->assign("id", $id);
            return $this->fetch();
        }
    }

    /**
     * 报废处理
     */
    public function handle_post(){
        $param           = $this->request->param();
        if (isset($param['ids'])) {
            if ($this->request->isPost()) {
                $resultva = $this->validate($this->request->param(), 'ScrapInfo');
                if ($resultva !== true) {
                    // 验证失败 输出错误信息
                    $this->error($resultva);
                } else {
                    $ids = $this->request->param('ids');
                    $idArr = explode(',', $ids);
                    $a     = 0;
                    foreach ($idArr as $val) {
                        $data['asset_id']      = $val;
                        $data['repairer']      = $param['repairer'];
                        $data['start_time']    = $param['start_time'];
                        $data['repair_info']   = $param['repair_info'];
                        $data['status']        = 0;
                        $data['user_id']       = cmf_get_current_admin_id();
                        $data['update_time']   = date('Y-m-d H:i:s', time());
                        $result                = Db::name('scrap_info')->insertGetId($data);
                        if ($result) {
                            $result_asset_info = DB::name('asset_info')->where(['id' => $val])->update(['status' => 0]);
                            if ($result_asset_info) {
                                $a++;
                            }
                        }
                    }
                    if ($a == count($idArr)) {
                        $this->success("操作成功！", url("ScrapInfo/add"));
                    } else {
                        $this->error("操作失败！");
                    }
                }
            }
        }
    }



    /**
     * 启用/未启用
     */
    public function toggle(){
        $param = $this->request->param();
        if(isset($param['ids'])  && isset($param['display'])){
            $ids = $this->request->param('ids/a');
            $update_time=date('Y-m-d H:i',time());
            $user_id=cmf_get_current_user_id();
            if (Db::name('scrap_info')->where(['id' => ['in', $ids]])->update(['status' =>1,'update_time'=>$update_time,'user_id'=>$user_id])!==false) {
                $this->success("已修复成功！");
            } else {
                $this->error("已修复失败！");
            }
        }
        if(isset($param['ids']) && isset($param['hide'])){
            $ids = $this->request->param('ids/a');
            $update_time=date('Y-m-d H:i',time());
            $user_id=cmf_get_current_user_id();
            if (Db::name('scrap_info')->where(['id' => ['in', $ids]])->update(['status' =>0,'update_time'=>$update_time,'user_id'=>$user_id])!==false) {
                $this->success("报废成功！");
            } else {
                $this->error("报废失败！");
            }
        }
        if(isset($param['ids']) && isset($param['repair'])){
            $ids = $this->request->param('ids/a');
            $update_time=date('Y-m-d H:i',time());
            $user_id=cmf_get_current_user_id();
            if (Db::name('scrap_info')->where(['id' => ['in', $ids]])->update(['status' =>2,'update_time'=>$update_time,'user_id'=>$user_id])!==false) {
                $this->success("报废成功！");
            } else {
                $this->error("报废失败！");
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
                        $asset_type=trim($objPHPExcel->getActiveSheet()->getCell('C'.$i)->getValue());
                        $building_name=trim($objPHPExcel->getActiveSheet()->getCell('D'.$i)->getValue());
                        $floor_name=trim($objPHPExcel->getActiveSheet()->getCell('E'.$i)->getValue());
                        $room_name=trim($objPHPExcel->getActiveSheet()->getCell('F'.$i)->getValue());
                        $manufact=trim($objPHPExcel->getActiveSheet()->getCell('G'.$i)->getValue());
                        $asset_num=trim($objPHPExcel->getActiveSheet()->getCell('H'.$i)->getValue());
                        $purchaser=trim($objPHPExcel->getActiveSheet()->getCell('I'.$i)->getValue());
                        $purchase_amount=trim($objPHPExcel->getActiveSheet()->getCell('J'.$i)->getValue());
                        $purchase_time=trim($objPHPExcel->getActiveSheet()->getCell('K'.$i)->getValue());
                        $supplier=trim($objPHPExcel->getActiveSheet()->getCell('L'.$i)->getValue());
                        $repair_time=trim($objPHPExcel->getActiveSheet()->getCell('M'.$i)->getValue());
                        $power=trim($objPHPExcel->getActiveSheet()->getCell('N'.$i)->getValue());
                        $weight=trim($objPHPExcel->getActiveSheet()->getCell('O'.$i)->getValue());
                        $remark=trim($objPHPExcel->getActiveSheet()->getCell('P'.$i)->getValue());
                        $list_order=trim($objPHPExcel->getActiveSheet()->getCell('Q'.$i)->getValue());
                        $status=trim($objPHPExcel->getActiveSheet()->getCell('R'.$i)->getValue());
                        $data['name']=$name;
                        $data['asset_type']=$asset_type;
                        $data['building_name']=$building_name;
                        $data['floor_name']=$floor_name;
                        $data['room_name']=$room_name;
                        $data['manufact']=$manufact;
                        $data['asset_num']=$asset_num;
                        $data['purchaser']=$purchaser;
                        $data['purchase_amount']=$purchase_amount;
                        $data['purchase_time']=$purchase_time;
                        $data['supplier']=$supplier;
                        $data['repair_time']=$repair_time;
                        $data['power']=$power;
                        $data['weight']=$weight;
                        $data['remark']=$remark;
                        $data['list_order']=$list_order;
                        $data['status']=$status;
                        if(DB::name('scrap_info')->where(array('id'=>$id))->count()){
                            $response= DB::name('scrap_info')->where(array('id'=>$id))->update($data);;
                            if($response!==false){
                                $a++;
                            }else{
                                $b++;
                            }
                        }else{
                            $data['create_time']=Date('Y-m-d H:i:s',time());
                            $response= DB::name('scrap_info')->add($data);
                            if($response!==false){
                                $a++;
                            }else{
                                $b++;
                            }

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

    public function download_ScrapInfo()
    {
        //先清空缓存  避免表字段信息有残留
        cmf_clear_cache();
        //引入IOFactory.php 文件里面的PHPExcel_IOFactory这个类
        import('PHPExcel.Classes.PHPExcel');
        import('PHPExcel.Classes.PHPExcel.IOFactory.PHPExcel_IOFactory');
        //初始化字段数组
        $fieldsData=array();
        $fieldsData=array("ID","资产名称","资产类型","楼宇名称","楼层名称","房间名称","厂家名称","资产型号","采购人","采购金额","购买时间","供应商","保修年限(年)","功耗(Kw)","载重(Kg)","描述","排序","状态");

        $scrap_info= DB::name('scrap_info')->field('id,name,asset_type,building_name,floor_name,room_name,manufact,asset_num,purchaser,purchase_amount,purchase_time,supplier,repair_time,power,weight,remark,list_order,status')->select();
        $scrap_info = $scrap_info->toArray();
        $filename='资产信息数据表文件';
        $date = date("Y_m_d_H_i_s",time());
        $filename .= "_{$date}.xlsx";
        $PHPExcel = new \PHPExcel();
        $PHPSheet = $PHPExcel->getActiveSheet();
        $PHPSheet->setTitle("资产信息");//给当前活动sheet设置名称
        $key = ord("A");
        foreach($fieldsData as $v){
            $colum = chr($key);
            $PHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
            $PHPSheet->getColumnDimension($colum)->setAutoSize(true);
            $key++;
        }
        $column = 2;
        foreach($scrap_info as $key => $rows){ //行写入
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
}