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

class AssetInfoValidate extends Validate
{
    protected $rule = [
        'name' => 'require|unique:asset_info,name',
    ];
    protected $message = [
        'name.require' => '资产名称不能为空',
    ];

    protected $scene = [
        'add'  => ['name'],
        'edit' => ['name'],
    ];
}