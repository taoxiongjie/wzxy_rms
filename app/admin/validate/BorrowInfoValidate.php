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

class BorrowInfoValidate extends Validate
{
    protected $rule = [

        'name'=>'require',
        'asset_type'=>'require',
        'b_mark'=>'require',
        'f_mark'=>'require',
        'r_mark'=>'require',
        'asset_num'=>'require',
        'borrower_name'=>'require',
        'start_time'=>'require',

    ];
    protected $message = [
        'name.require' => '资产名称不能为空',
        'asset_type.require' => '资产类型不能为空',
        'b_mark.require' => '所属楼宇必选',
        'f_mark.require' => '所属楼层必选',
        'r_mark.require' => '所属教室必选',
        'asset_num.require' => '资产型号不能为空',
        'borrower_name.require' => '借入人名字不能为空',
        'start_time.require' => '借出起始时间不能为空',

    ];

    protected $scene = [
        'add'  => ['name','asset_type','b_mark','f_mark','r_mark','asset_num','borrower_name','start_time'],
        'edit' => ['borrower_name','start_time'],
    ];
}