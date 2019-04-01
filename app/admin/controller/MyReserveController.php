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

class MyReserveController extends AdminBaseController
{
    /**
     * 我的预约列表
     */
    public function index()
    {
        $where=[];
        $param = $this->request->param();
        if (!empty($param['start_time']) && !empty($param['end_time'])) {
            $startTime=explode(' ',$param['start_time']);
            $endTime=explode(' ',$param['end_time']);
            $where['f.dateInfo'] = [['EGT', $startTime[0]], ['ELT', $endTime[0]]];
            $where['o.start_time'] = ['EGT', $startTime[1]];
            $where['o.start_time'] = ['ELT', $endTime[1]];
        } else {
            if (!empty($param['start_time'])) {
                $startTime=explode(' ',$param['start_time']);
                $where['f.dateInfo'] = ['EGT', $startTime[0]];
                $where['o.end_time'] = ['EGT', $startTime[1]];
            }
            if (!empty($param['end_time'])) {
                $endTime=explode(' ',$param['end_time']);
                $where['f.dateInfo'] = ['ELT', $endTime[0]];
                $where['o.start_time'] = ['ELT', $endTime[1]];
            }
        }
        if(!empty($param['status'])){
            $where['f.status'] =$param['status'];
        }
        $where['f.user_id']=cmf_get_current_admin_id();
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

            foreach ($list['data'] as &$value){
                if($value['dateInfo']<$day && $value['status']==0){
                    $value['status']=3;
                    Db::name('reserve_info')->where(array('id'=>$value['id']))->update(['status'=>3]);
                }
                $week=explode(' ', $value['week']);
                $value['week']=$week[1];
            }
            $status_list=['0'=>['value'=>1,'name'=>"未使用"],
                '1'=>['value'=>2,'name'=>"已使用"],
                '2'=>['value'=>3,'name'=>"已取消"],
                '3'=>['value'=>4,'name'=>"已过期"]];
            $this->assign('status_list', $status_list);
           /* ->paginate(10,false,['query'=>request()->param()])->each(function($item, $key){
                $nameArr="";
                $name = Db::name('station_info_relation') ->alias('s')->where(array('s.s_id'=>$item['id']))->join('__USER__ u', 's.user_id = u.id')->field('u.user_nickname')->select();
                foreach ($name as $value){
                    if($nameArr){
                        $nameArr.=','.$value['user_nickname'];
                    }else{
                        $nameArr.=$value['user_nickname'];
                    }
                }
                $item['student_name'] = $nameArr;
                 return $item;
            });*/
/*print_r($list);
exit();*/
        $this->assign('status', isset($param['status']) ? $param['status'] : '');
        $this->assign('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        $this->assign('end_time', isset($param['end_time']) ? $param['end_time'] : '');
        $this->assign('page', $page);
        $this->assign('list',$list['data']);

        return $this->fetch();
    }


}
