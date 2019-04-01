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

class UserPassValidate extends Validate
{
    protected $rule = [

        'user_pass'  => 'require',
        'new_user_pass'  => 'require|min:6|max:32',
        'new_user_pass_old'  => 'require|confirm:new_user_pass',


    ];
    protected $message = [
        'user_pass.require'  => '原始密码不能为空',
        'new_user_pass.require'  => '新密码不能为空',
        'new_user_pass.max'     => '新密码不能超过32个字符',
        'new_user_pass.min'     => '新密码不能小于6个字符',
        'new_user_pass_old.require'  => '重复新密码不能为空',
        'new_user_pass_old.confirm'     => '密码不一致，请重新输入',

    ];

}