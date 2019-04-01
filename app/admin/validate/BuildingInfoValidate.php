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

class BuildingInfoValidate extends Validate
{
    protected $rule = [
        'name' => 'require|unique:building_info,name',
        'mark' => 'require|unique:building_info,mark',
    ];
    protected $message = [
        'name.require' => '名称不能为空',
        'name.unique'  => '名称已存在',
        'mark.require' => '标识不能为空',
        'mark.unique'  => '标识已存在',
    ];

    protected $scene = [
        'add'  => ['name','mark'],
        'edit' => ['name','mark'],
    ];
}