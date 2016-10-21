<?php
/*
*
*	使用thinkphp 循环为已经上传的图片添加水印
*
*	by postbird
*
*   http://www.ptbird.cn
*
*	2016-10-21
*
*	license MIT
*
*/
namespace Home\Controller;
use Think\Controller;

//处理速度让人崩溃200张要30s
//因此需要设置最大执行时间为无限
ini_set('max_execution_time','0');
class IndexController extends Controller {
    public function index(){
        echo "分别调用: FileImageWaterController.class.php【遍历文件夹】 SqlImageWaterController.class.php 【从数据库读取】";

        $this->display();
    }
}