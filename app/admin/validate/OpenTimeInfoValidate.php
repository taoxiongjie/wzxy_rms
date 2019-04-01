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
namespace app\admin\validate;

use think\Validate;

class OpenTimeInfoValidate extends Validate
{
    protected $rule = [
        'start_time' => 'require|unique:open_time_info,start_time^room_mark',
        'end_time' => 'require|unique:open_time_info,end_time^room_mark',
    ];
    protected $message = [
        'start_time.require' => '开始时间点不能为空',
        'start_time.unique'  => '开始时间点已存在',
        'end_time.require' => '结束时间点不能为空',
        'end_time.unique'  => '结束时间点已存在',
    ];

    protected $scene = [
        'add'  => ['start_time,end_time'],
        'edit' => ['start_time,end_time'],
    ];
}