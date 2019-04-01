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

class FloorInfoValidate extends Validate
{
    protected $rule = [
        'name' => 'require',
        'mark' => 'require|unique:floor_info,mark',
        'b_mark' => 'require',


    ];
    protected $message = [
        'name.require' => '楼层名称不能为空',
        'b_mark.require' => '请选择所属楼宇',
        'mark.require' => '楼层标识不能为空',
        'mark.unique'  => '楼层标识已存在',

    ];

    protected $scene = [
        'add'  => ['name','p_mark','mark' ],
        'edit' => ['name','p_mark','mark' ],
    ];
}