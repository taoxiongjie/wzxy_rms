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
namespace app\user\controller;

use cmf\controller\HomeBaseController;
use think\Validate;
use app\user\model\UserModel;

class RegisterController extends HomeBaseController
{
    public function _initialize()
    {
      
    }
    /**
     * 前台用户注册
     */
    public function index()
    {
        /*$redirect = $this->request->post("redirect");
        if (empty($redirect)) {
            $redirect = $this->request->server('HTTP_REFERER');
        } else {
            $redirect = base64_decode($redirect);
        }
        session('login_http_referer', $redirect);

        if (cmf_is_user_login()) {
            return redirect($this->request->root() . '/');
        } else {*/
            return $this->fetch();
       /* }*/
    }

    /**
     * 前台用户注册提交
     */
    public function doRegister()
    {
        if ($this->request->isPost()) {

            //保存路径
            $app="avatar";
            $dir_file=date("Ymd");
            $imgDir=$_SERVER['DOCUMENT_ROOT'].'/upload/'. $app.'/'.$dir_file.'/';
            if(!is_dir($imgDir))
            {
                $oldumask=umask(0);
                mkdir($imgDir,0777,true);
                umask($oldumask);
            }

            $rules = [

                'student_id'  => 'require',
                'student_name'  => 'require',
                'password' => 'require|min:6|max:32',
                'password_old' => 'require|min:6|max:32',
	            'college'  => 'require',
	            'class'  => 'require',
	            'email'  => 'require',
	            'mobile'  => 'require',
	            'captcha'  => 'require',
                'captcha'  => 'require',
            ];

            $validate = new Validate($rules);
            $validate->message([
	            'student_id.require'     => '学号不能为空',
	            'student_name.require'     => '姓名不能为空',
                'password.require' => '密码不能为空',
            	'password_old.require'     => '确认密码不能为空',
                'password.max'     => '密码不能超过32个字符',
                'password.min'     => '密码不能小于6个字符',
            	'college.require'     => '学院不能为空',
	            'class.require'     => '班级不能为空',
	            'email.require'     => '邮箱不能为空',
	            'mobile.require'     => '手机号不能为空',
                'captcha.require'  => '验证码不能为空',
            ]);
            $data = $this->request->post();
            if (!$validate->check($data)) {
                $this->error($validate->getError());
            }
            if($data['password']!=$data['password_old']){
                $this->error('两次密码不一致');
            }
            if (cmf_check_mobile($data['mobile'])) {
                $this->error('手机格式错误');
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
            $user['user_email'] = $data['email'];
            $user['user_login']=$data['student_id'];
            $user['mobile']=$data['mobile'];
            $log   = $register->register($user, 1);

            $sessionLoginHttpReferer = session('login_http_referer');
            $redirect = empty($sessionLoginHttpReferer) ? cmf_get_root() . '/' : $sessionLoginHttpReferer;
            switch ($log) {
                case 0:
                    $this->success('注册成功', $redirect);
                    break;
                case 1:
                    $this->error("您的账户已注册过");
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
            ]);
            $validate->message([
                'verification_code.require' => '验证码不能为空',
                'password.require'          => '密码不能为空',
                'password.max'              => '密码不能超过32个字符',
                'password.min'              => '密码不能小于6个字符',
                'captcha.require'           => '验证码不能为空',
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