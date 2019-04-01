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

class EngineroomInfoValidate extends Validate
{
    protected $rule = [
        'name' => 'require|unique:floor_info,name',
        'mark' => 'require|unique:floor_info,mark',
        'p_mark' => 'require',
        'start_time' => 'require',
        'end_time' => 'require',
        'seat_num' => 'integer',
        'seat_id' => 'require',

    ];
    protected $message = [
        'name.require' => '机房名称不能为空',
        'name.unique'  => '机房名称已存在',
        'p_mark.require' => '请选择机房位置',
        'mark.require' => '机房标识不能为空',
        'mark.unique'  => '机房标识已存在',
        'seat_num.integer'  => '座位数必须为整数',
        'start_time.require' => '开放起始时间不能为空',
        'end_time.require' => '开放截止时间不能为空',
        'seat_id.require' => '座位编号不能为空',
    ];
    protected $scene = [
        'add'  => ['name','p_mark','mark','seat_num','start_time','end_time'],
        'edit' =>['name','p_mark','mark','seat_num','start_time','end_time'],
        'seat_add' => ['seat_id'],
    ];
}