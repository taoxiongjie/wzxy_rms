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

class ReserveStatisticsController extends AdminBaseController
{
    /**
     * 预约列表
     */
    public function index()
    {
        $where=[];
        $param = $this->request->param();

        if (!empty($param['start_time']) && !empty($param['end_time'])) {
            $startTime=explode(' ',$param['start_time']);
            $endTime=explode(' ',$param['end_time']);
            $where['f.dateInfo'] = [['EGT', $startTime[0]], ['ELT', $endTime[0]]];
           /* $where['o.start_time'] = ['EGT', $startTime[1]];
            $where['o.start_time'] = ['ELT', $endTime[1]];*/
        } else {
            if (!empty($param['start_time'])) {
                $startTime=explode(' ',$param['start_time']);
                $where['f.dateInfo'] = ['EGT', $startTime[0]];
                /*$where['o.end_time'] = ['EGT', $startTime[1]];*/
            }
            if (!empty($param['end_time'])) {
                $endTime=explode(' ',$param['end_time']);
                $where['f.dateInfo'] = ['ELT', $endTime[0]];
               /* $where['o.start_time'] = ['ELT', $endTime[1]];*/
            }
        }
        if(!empty($param['status'])){
            $where['f.status'] =$param['status'];
        }
        if(!empty($param['building_mark'])){
            $where['f.building_mark'] =$param['building_mark'];
            $room_list=Db::name('engineroom_info')->where(["status"=>1,"p_mark"=>$param['building_mark']])->select();
        }
        if(!empty($param['room_mark'])){
            $where['f.room_mark'] =$param['room_mark'];
        }
        $list = Db::table('fn_reserve_info')
            ->alias('f')
            ->field('f.*,u.user_nickname,e.name as building_name ,i.name as room_name,o.start_time,o.end_time')
            ->join( "fn_user u "," f.user_id = u.id",'left')
            ->join( 'fn_engineroom_position e ','f.building_mark = e.mark','left')
            ->join(  "fn_engineroom_info i "," f.room_mark = i.mark and i.p_mark=f.building_mark" ,'left')
            ->join(  "fn_open_time_info o" ," f.time_type = o.id",'left')
            ->where($where)
            ->order("create_time desc,dateInfo desc")
            ->paginate(10);
            $page = $list->render();
            $list =$list ->toArray();
            $day=date('Y-m-d');

            foreach ($list['data'] as $k=>&$value){
                if($value['dateInfo']<$day && $value['status']==0){
                    $value['status']=3;
                    Db::name('reserve_info')->where(array('id'=>$value['id']))->update(['status'=>3]);
                }
                $week=explode(' ', $value['week']);
                $value['week']=$week[1];
                if (!empty($param['start_time'])) {
                    if($value['dateInfo']==$startTime[0]){
                        if($value['start_time']<$startTime[1]){
                            unset($list['data'][$k]);
                        }
                    }
                }
                if (!empty($param['end_time'])) {
                    if ($value['dateInfo']==$endTime[0]) {
                        if ($value['end_time']>$endTime[1]) {
                            unset($list['data'][$k]);
                        }
                    }
                }

            }

            $status_list=['0'=>['value'=>1,'name'=>"未使用"],
                            '1'=>['value'=>2,'name'=>"已使用"],
                            '2'=>['value'=>3,'name'=>"已取消"],
                            '3'=>['value'=>4,'name'=>"已过期"]];
        $this->assign('status_list', $status_list);
        $this->assign('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('end_time', isset($param['end_time']) ? $param['end_time'] : '');
        $this->assign('status', isset($param['status']) ? $param['status'] : '');
        $this->assign('page', $page);
        $this->assign('list',$list['data']);
        $this->assign('building_mark',isset($param['building_mark']) ? $param['building_mark'] : '');
        $this->assign('room_mark',isset($param['room_mark']) ? $param['room_mark'] : '');
        $this->assign('room_list',isset($room_list) ? $room_list : '');
        //机房地点
        $building_where['status']=1;
        $building_list = Db::name('engineroom_position') ->where($building_where) ->order(" list_order AES  ,create_time AES ") ->select();
        $this->assign('building_list',$building_list);

        return $this->fetch();
    }
    /**
     * 获取所属楼宇所有教室
     */
    public function room_data_ajax()
    {
        $mark =  trim($this->request->param('b_mark'));
        //查询房间信息
        $room_list=Db::name('engineroom_info')->where(["status"=>1,"p_mark"=>$mark])->select();
        echo json_encode($room_list);
    }


    /**
     * 下载表格
     */
    public function download_excel()
    {
        $where=[];
        $param = $this->request->param();

        if (!empty($param['start_time']) && !empty($param['end_time'])) {
            $startTime=explode(' ',$param['start_time']);
            $endTime=explode(' ',$param['end_time']);
            $where['f.dateInfo'] = [['EGT', $startTime[0]], ['ELT', $endTime[0]]];
        } else {
            if (!empty($param['start_time'])) {
                $startTime=explode(' ',$param['start_time']);
                $where['f.dateInfo'] = ['EGT', $startTime[0]];
            }
            if (!empty($param['end_time'])) {
                $endTime=explode(' ',$param['end_time']);
                $where['f.dateInfo'] = ['ELT', $endTime[0]];
            }
        }
        if(!empty($param['status'])){
            $where['f.status'] =$param['status'];
        }
        if(!empty($param['building_mark'])){
            $where['f.building_mark'] =$param['building_mark'];
        }
        if(!empty($param['room_mark'])){
            $where['f.room_mark'] =$param['room_mark'];
        }


        $list = Db::table('fn_reserve_info')
            ->alias('f')
            ->field('f.id,e.name as building_name ,i.name as room_name,f.dateInfo,f.week,o.start_time,o.end_time,f.seat_id,u.user_nickname,f.create_time,f.content,f.status')
            ->join( "fn_user u "," f.user_id = u.id",'left')
            ->join( 'fn_engineroom_position e ','f.building_mark = e.mark','left')
            ->join(  "fn_engineroom_info i "," f.room_mark = i.mark and i.p_mark=f.building_mark" ,'left')
            ->join(  "fn_open_time_info o" ," f.time_type = o.id",'left')
            ->where($where)
            ->order("create_time desc,dateInfo desc")
            ->select();
        $list =$list->toArray();
        foreach ($list as $k=>&$val){
            if($val['status']==1){
                $val['status']="未使用";
            }else if($val['status']==2){
                $val['status']="已使用";
            }else if($val['status']==3){
                $val['status']="已取消";
            }else{
                $val['status']="已过期";
            }
            $week=explode(' ', $val['week']);
            $val['week']=$week[1];
            $val['seat_id']=$val['seat_id'].'号';
            if (!empty($param['start_time'])) {
                if($val['dateInfo']==$startTime[0]){
                    if($val['start_time']<$startTime[1]){
                        unset($list[$k]);
                    }
                }
            }
            if (!empty($param['end_time'])) {
                if ($val['dateInfo']==$endTime[0]) {
                    if ($val['end_time']>$endTime[1]) {
                        unset($list[$k]);
                    }
                }
            }
        }
        //先清空缓存  避免表字段信息有残留
        cmf_clear_cache();
        //引入IOFactory.php 文件里面的PHPExcel_IOFactory这个类
        import('PHPExcel.Classes.PHPExcel');
        import('PHPExcel.Classes.PHPExcel.IOFactory.PHPExcel_IOFactory');
        //初始化字段数组
        $fieldsData=array("预约号","地点","机房","日期","星期","开始时间","结束时间","座位号","预约者","预约时间","预约单说明","状态");

        $filename='机房预约使用信息数据表文件';
        $date = date("Y_m_d_H_i_s",time());
        $filename .= "_{$date}.xlsx";
        $PHPExcel = new \PHPExcel();
        $PHPSheet = $PHPExcel->getActiveSheet();
        $PHPSheet->setTitle("预约信息");//给当前活动sheet设置名称
        $key = ord("A");
        foreach($fieldsData as $v){
            $colum = chr($key);
            $PHPExcel->setActiveSheetIndex(0) ->setCellValue($colum.'1', $v);
            //$PHPSheet->getColumnDimension($colum)->setAutoSize(true);
            $PHPSheet->getColumnDimension($colum)->setWidth(15);

            $key++;
        }
        $column = 2;

        foreach($list as $key => $rows){ //行写入
            $span = ord("A");
            foreach($rows as $keyName=>$value){// 列写入
                $j = chr($span);
                $PHPSheet->setCellValue($j.$column, $value);
                $span++;
            }
            $column++;
        }
        $fileName = dirname($_SERVER['DOCUMENT_ROOT'])."/public/upload/admin/Excel/" .$filename;
        $fileName = iconv("utf-8", "gb2312", $fileName);
        ob_end_clean();
        ob_start();
        $objWriter = \PHPExcel_IOFactory::createWriter($PHPExcel, 'Excel2007');
        $objWriter->save($fileName,TRUE);

        if(is_file($fileName)){

            $length = filesize($fileName);
            //$type = mime_content_type($file);
            $showname =  ltrim(strrchr($fileName,'/'),'/');
            header("Content-Description: File Transfer");
            header('Content-Type: application/octet-stream');
            header('Content-Length:' . $length);
            if (preg_match('/MSIE/', $_SERVER['HTTP_USER_AGENT'])) { //for IE
                header('Content-Disposition: attachment; filename="' . rawurlencode($showname) . '"');
            } else {
                header('Content-Disposition: attachment; filename="' . $showname . '"');
            }
            readfile($fileName);
            //exit;
        } else {
            exit('文件已被删除！');
        }
        unlink($fileName);
    }

}
