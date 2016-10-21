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
    	echo "<body style='margin:0 auto;padding-top:50px;text-align:center;'>";
        echo "分别调用: FileImageWater Controller【遍历文件夹】 SqlImageWater Controller【从数据库读取】<br><br>";
        echo "示例 http://localhost/thinkphp/index.php/Home/FileImageWater/?code=postbird<br><br>";
        echo "code 为必需 验证码 默认为postbird，请进行更改<br>";
        echo "<hr>";
        echo "powered by  <a href='http://www.ptbird.cn'>postbird</a>";
        echo "</body>";
        $this->display();
    }
}