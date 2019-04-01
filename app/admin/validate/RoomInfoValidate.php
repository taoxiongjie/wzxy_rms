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

class RoomInfoValidate extends Validate
{
    protected $rule = [
        'name' => 'require',
        'mark' => 'require|unique:room_info,mark',
        'b_mark' => 'require',
        'f_mark' => 'require',
    ];
    protected $message = [
        'name.require' => '教室名称不能为空',
        'mark.require' => '教室标识不能为空',
        'mark.unique'  => '教室标识已存在',
        'b_mark.require' => '请选择所属楼宇',
        'f_mark.require' => '请选择所属楼层',

    ];

    protected $scene = [
        'add'  => ['name','b_mark','f_mark','mark' ],
        'edit' => ['name','b_mark','f_mark','mark' ],
    ];
}