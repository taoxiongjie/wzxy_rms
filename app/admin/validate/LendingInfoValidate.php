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

class LendingInfoValidate extends Validate
{
    protected $rule = [
        'lender_name'=>'require',
        'deliverer_name'=>'require',
        'start_time'=>'require',

    ];
    protected $message = [
        'lender_name.require' => '借设备人名字不能为空',
        'deliverer_name.require' => '交付人名字不能为空',
        'start_time.require' => '借出起始时间不能为空',

    ];

    protected $scene = [
        'add'  => ['lender_name','eliverer_name','start_time'],
        'edit' => ['lender_name','deliverer_name','start_time'],
    ];
}