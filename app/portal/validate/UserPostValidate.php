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

class UserPostValidate extends Validate
{
    protected $rule = [

        'user_login'=>'require|unique:user',
        'user_nickname'=>'require',
        'user_pass'  => 'require|min:6|max:32',
        'college'  => 'require',
        'mobile'  => 'require|mobile|unique:user,mobile',
        'user_email' => 'require|email|unique:user,user_email',

    ];
    protected $message = [
        'user_login.require'     => '用户名不能为空',
        'user_login.unique'  => '用户名已经存在',
        'user_nickname.require'  => '真实昵称不能为空',
        'user_pass.require'  => '密码不能为空',
        'user_pass.max'     => '密码不能超过32个字符',
        'user_pass.min'     => '密码不能小于6个字符',
        'college.require'     => '学院不能为空',
        'mobile.require'     => '手机号不能为空',
        'mobile.mobile'   => '手机号不正确',
        'mobile.unique'  => '手机号已经存在',
        'user_email.require' => '邮箱不能为空',
        'user_email.email'   => '邮箱不正确',
        'user_email.unique'  => '邮箱已经存在',
    ];

    protected $scene = [
        'add'  => ['user_login', 'user_nickname','user_pass','college','mobile', 'user_email'],
        'edit' => ['user_nickname','college','mobile', 'user_email'],

    ];
}