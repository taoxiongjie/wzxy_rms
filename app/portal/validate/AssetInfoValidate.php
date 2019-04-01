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
namespace app\portal\validate;

use think\Validate;

class AssetInfoValidate extends Validate
{
    protected $rule = [
        'name' => 'require|unique:asset_info,name',
        'asset_type' => 'require',

    ];
    protected $message = [
        'name.require' => '资产名称不能为空',
        'name.unique'  => '资产名称已经存在',
        'asset_type.require' => '请选择资产类型',
    ];

    protected $scene = [
        'add'  => ['name,asset_type'],
        'edit' => ['name^id'],
    ];
}