<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author:kane < chengjin005@163.com>
// +----------------------------------------------------------------------
namespace app\admin\model;

use think\Model;

class AssetModel extends Model
{
    public static   $STATUS = array(
        1=>"正常",
        0=>"报废",
        2=>"维修",
        3=>"借出",
        4=>"借入",


    );
}