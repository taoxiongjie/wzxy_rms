<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Validate;
use app\user\model\UserModel;

class RegisterController extends AdminBaseController
{
    public function _initialize()
    {
      
    }
    /**
     * 用户密码重置
     */
    public function index()
    {
        $config    = cmf_get_option('admin_login');
        $this->assign($config);
        return $this->fetch();
    }

    /**
     * 用户注册
     */
    public function registered()
    {
        $config    = cmf_get_option('admin_login');
        $this->assign($config);
        return $this->fetch();
    }
    /**
     * 前台用户注册提交
     */
    public function doRegister()
    {
        if ($this->request->isPost()) {

            //保存路径
            $app="admin/Avatar";
            $dir_file=date("Ymd");
            $imgDir=$_SERVER['DOCUMENT_ROOT'].'/upload/'. $app.'/'.$dir_file.'/';
            if(!is_dir($imgDir))
            {
                $oldumask=umask(0);
                mkdir($imgDir,0777,true);
                umask($oldumask);
            }

            $rules = [

                'student_id'  => 'require|number|unique:review,user_login',
                'student_name'  => 'require',
                'password' => 'require|min:6|max:16',
                'password_old' => 'require|min:6|max:16',
	            'college'  => 'require',
	            'class'  => 'require',
	            'email'  => 'require|email|unique:review,user_email',
                'mobile'  => 'require|mobile|unique:review,mobile',
	            'captcha'  => 'require',

            ];

            $validate = new Validate($rules);
            $validate->message([
	            'student_id.require'     => '学号不能为空',
                'student_id.number'     => '学号格式不对',
                'student_id.unique'     => '该学号已注册',
	            'student_name.require'     => '姓名不能为空',
                'password.require' => '密码不能为空',
            	'password_old.require'     => '确认密码不能为空',
                'password.max'     => '密码不能超过16位数',
                'password.min'     => '密码不能小于6位数',
            	'college.require'     => '学院不能为空',
	            'class.require'     => '班级不能为空',
	            'email.require'     => '邮箱不能为空',
                'email.email'     => '邮箱格式不对',
                'email.unique'  => '邮箱已经存在',
	            'mobile.require'     => '手机号不能为空',
                'mobile.mobile'     => '手机号格式错误',
                'mobile.unique'  => '手机号已经存在',
                'captcha.require'  => '验证码不能为空',

            ]);
            $data = $this->request->post();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            if($data['password']!=$data['password_old']){
                $this->error('两次密码不一致');
            }
            $captchaId = empty($data['_captcha_id']) ? '' : $data['_captcha_id'];
            if (!cmf_captcha_check($data['captcha'], $captchaId)) {
                $this->error('验证码错误');
            }
            $reallpath="";
            for ($i=0;$i<2;$i++){
                $myFile="avatar".($i+1)."";  //文件名
                if(!empty($_FILES[$myFile]["name"])){
                    $fileName=date("YmdHis").mt_rand(1000,9999);
                    $upfile=@$_FILES[$myFile]; //上传文件
                    $upfilename = $upfile['name']; //文件名
                    $fileInfo=pathinfo($upfilename);  //文件扩展名
                    $extension=$fileInfo['extension']; //文件类型
                    if(in_array($extension,array('gif','jpg','png','bmp','JPG','PNG','BNP','GIF')))
                    {
                        $relname = $fileName.'.'.$extension;
                        move_uploaded_file($upfile['tmp_name'],$imgDir.$relname);
                        $reallpath[]=$app.'/'.$dir_file.'/'.$relname;
                    }
                }else{
                    if($i=='0'){
                        $this->error('请上传学生证');
                    }else{
                        $this->error('请上传头像');
                    }
                }

            }
            $register          = new UserModel();
            $user['student_card'] = $reallpath[0];
            $user['avatar'] = $reallpath[1];
            $user['user_pass'] = $data['password'];
            $user['user_nickname'] = $data['student_name'];
            $user['college'] = $data['college'];
            $user['class'] = $data['class'];
            $user['sex'] = $data['sex'];
            $user['user_email'] = $data['email'];
            $user['user_login']=$data['student_id'];
            $user['mobile']=$data['mobile'];
            $log   = $register->register($user, 1);

            switch ($log) {
                case 0:
                    $this->success('正在审核');
                    break;
                case 1:
                    $this->success("您的注册申请已审核通过", url('admin/public/login'));
                    break;
                case 2:
                    $this->error("审核未通过,请重新提交材料！");
                    break;
                case 3:
                    $this->error("学号异常,请联系实验室管理员！");
                    break;
                case 4:
                    $this->success("注册成功,等待审核！");
                    break;
                case 5:
                    $this->success("提交成功,等待审核！");
                    break;
                default :
                    $this->success('正在审核');
            }

        } else {
            $this->error("请求错误");
        }

    }

    /**
     * 用户密码重置
     */
    public function passwordReset()
    {

        if ($this->request->isPost()) {
            $validate = new Validate([
                'captcha'           => 'require',
                'verification_code' => 'require',
                'password'          => 'require|min:6|max:32',
                'password_old'  => 'require|confirm:password',
            ]);
            $validate->message([
                'captcha.require'           => '验证码不能为空',
                'verification_code.require' => '邮箱验证码不能为空',
                'password.require'          => '密码不能为空',
                'password.max'              => '密码不能超过32个字符',
                'password.min'              => '密码不能小于6个字符',
                'password_old.require'      => '确认密码不能为空',
                'password_old.confirm'      => '密码不一致，请重新输入',

            ]);

            $data = $this->request->post();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }

            $captchaId = empty($data['_captcha_id']) ? '' : $data['_captcha_id'];
            if (!cmf_captcha_check($data['captcha'], $captchaId)) {
                $this->error('验证码错误');
            }

            $errMsg = cmf_check_verification_code($data['username'], $data['verification_code']);
            if (!empty($errMsg)) {
                $this->error($errMsg);
            }

            $userModel = new UserModel();
            if ($validate::is($data['username'], 'email')) {

                $log = $userModel->emailPasswordReset($data['username'], $data['password']);

            } else if (cmf_check_mobile($data['username'])) {
                $user['mobile'] = $data['username'];
                $log            = $userModel->mobilePasswordReset($data['username'], $data['password']);
            } else {
                $log = 2;
            }
            switch ($log) {
                case 0:
                    $this->success('密码重置成功', $this->request->root());
                    break;
                case 1:
                    $this->error("您的账户尚未注册");
                    break;
                case 2:
                    $this->error("您输入的账号格式错误");
                    break;
                default :
                    $this->error('未受理的请求');
            }

        } else {
            $this->error("请求错误");
        }
    }


}