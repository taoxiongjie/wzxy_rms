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

class ScrapInfoValidate extends Validate
{
    protected $rule = [
        'repairer'=>'require',
        'start_time'=>'require',

    ];
    protected $message = [
        'repairer.require' => '报废人不能为空',
        'start_time.require' => '报废起始时间不能为空',

    ];

    protected $scene = [
        'add'  => ['repairer','repair_time','repair_amount'],
        'edit' => ['repairer','repair_time','repair_amount'],
    ];
}