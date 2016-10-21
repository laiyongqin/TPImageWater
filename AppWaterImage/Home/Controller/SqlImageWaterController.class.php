<?php
/*
*
*	使用thinkphp 循环为已经上传的图片添加水印
*
*   从数据库读出部分图片的地址并转换成本地地址进行操作
*   从数据库中读图片，省去了遍历和筛选过滤的环节
*   相比遍历出来在进行比对，相对快速和方便
*   最终的函数没有本质的区别，只有参数变化了。
*   详细请看参数注释
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

class SqlImageWaterController extends Controller {
    //禁止随便访问
    //访问的时候后面必须加上该字段的参数值
    public function check($code){
        $key="postbird";
        if(md5($key)!=md5($code)){
            return false;
        }else{
            return true;
        }
    }
    public function index(){
        $code=$_GET['code'];
        if(!$this->check($code)){
            echo "no such file";
            return;
            exit();
            die;
        }
       
        //两个参数 第一个是要处理的文件地址的合辑
        //从数据库中读取文件地址,可能不是操作一个表，因此需要将表和要读取的字段名称作为数组参数传递
        //第二个是水印图片的本地地址
        //我要处理三张表 每张表取出图片字段
        //数据库已经配置了表前缀
        //默认的存储形式是  /Uploads/image/20150920/55fe9a083ca65.jpg 
            //因为我前面没有 . ,所以在后面进行处理的时候每张图片的前面加了.
            // 需要根据数据库字段进行设置 参照113-116行代码，在这里面设置就可以
        $imgSqlArr=array();
        $imgSqlArr[0]['table']='article_page';
        $imgSqlArr[0]['field']='cover';
        $imgSqlArr[1]['table']='item';
        $imgSqlArr[1]['field']='img';
        $imgSqlArr[2]['table']='item_img';
        $imgSqlArr[2]['field']='img';
    	$this->imageWater($imgSqlArr,'./Uploads/water.jpg');
        $this->display();
    }
    //替换得到所有图片
    //返回数组,需要对数组进行组合    
    public function getImgBySql($imgSqlArr){
        //因为不确定处理几张表，因此每次都组合一次数组
        //要返回的结果
            $res=array();
            for($i=0;$i<count($imgSqlArr);$i++){
                $tmpRes[$i]=array();
                //返回需要的列的数据
                $tmpRes[$i]=M($imgSqlArr[$i]['table'])->getField($imgSqlArr[$i]['field'],true);
                $res=array_merge($res,$tmpRes[$i]);
            }
            //返回数据 但是里面数据可能存在空值 空值需要跳过
            // p($res);exit();
            return $res;
    }
    //进行水印处理
    // 参数是  文件sql数组-imgSqlArr  water
    // water是水印的位置
    public function imageWater($imgSqlArr,$water){
         if(!is_array($imgSqlArr)){
            echo"参数只能为数组";
            die;
        }
    	//得到所有需要处理的图片
    	$images=$this->getImgBySql($imgSqlArr);
        p($images);
    	//使用Image类
		$Image = new \Think\Image(); 
        //水印位置是存放在根目录下 water.png 可以根据自己需要改
        //设置临时的水印文件与water.png在同一目录，为了图省事可以放在根目录
        //下面使用分割拼接主要是为了通用性，可以根据实际的文件夹要求直接写死
        //现在的做法是将water.png分出来，再把water截取后面water.png的长度，就是目录
        //截取的前几位字符串接上tmpWater.png就是字符串
        $tmpWaterArr=explode("/",$water);
        $tmpWater="tmpWater.jpg";
        $tmpWater=substr($water, 0,strlen($tmpWaterArr[count($tmpWaterArr)-1])+1).$tmpWater;
        // 上面表达式的应用从  "./Uploads/water.png" 又生成 "./Uploads/tmpWater.png"
        // 从而实现了响应式的临时水印的位置,当然如果嫌麻烦直接根目录也没啥
        //因为是默认覆盖源文件的，因此即使每次生成tmpwater.png,最后也只会多处一张图片

        //循环遍历图片,并进行操作
		for($i=0;$i<count($images);$i++){
            //判断数据库的空值进行跳过
            if(strlen($images[$i])==0){
                continue;
            }else{
                //有了就不需要了
                //我的数据库里面字段存的时候没有. 
                //所以需要在这里操作一下
                $images[$i]=".".$images[$i];
            }
			//同样保存到文件中的过程中需要进行 转码成GB2312
			$images[$i] = iconv('UTF-8','GB2312',$images[$i]);
			//打开原图片默认添加水印到左上角 并保存为原来位置和名称 自动覆盖
            //因为每个图片的大小不确定，因此需要根据图片大小进行位置的确定

			//关于水印位置和选项可以参照 thinkphp3.2 手册 
			//   http://document.thinkphp.cn/manual_3_2.html#image
            $imageInfo=$Image->open($images[$i]);

            //标准图片宽度和高度
            $bw=430;
            $bh=430;
            //标准的水印大小
            $bww=130;
            $bwh=50;
            //标准的水印距离标准的图片的右方和下方间距
            //(总是以水印左上角为起点)
            $bsubw=30;
            $bsubh=80;
            //因此可以得出标准的水印的位置应该是
            $bpx=$bw-$bww-$bsubw;//270
            $bpy=$bh-$bwh-$bsubh;//300
            //获取实际图片（打开的图片）的大小
            $imgw=$imageInfo->width();
            $imgh=$imageInfo->height();
            //实际水印的大小根据距离的位置($bsub*)与图片($b*)的比例计算出来
            $ww=round($bww*($imgw/$bw));
            $wh=round($bwh*($imgh/$bh));
            //先前没有考虑实际上距离的位置也应随着图片大小的变化而变化
            $subw=round($bsubw*($imgw/$bw));
            $subh=round($bsubh*($imgh/$bh));
            //【正确】实际水印的位置按照之前的预算，距离底部和右边的距离理论上是不变的，
            //  这样子才能维持原来水印的位置，只不过现在水印的大小变了
            $px=$imgw-$ww-$subw;
            $py=$imgh-$wh-$subh;
            //水印location的位置
            $location=array($px,$py);
            //计算水印的大小  生成缩略图
            //大小是根据上面的比例算出来的
            //这里要用到我们上面截取生成的临时水印的文件
            //这个处理是为了解决thinkphp再上传保存后$tmpWater就不是string类型
            //因此找了一个临时变量来处理
            $tmpStr=$tmpWater;
            $Image->open($water)->thumb($ww, $wh,\Think\Image::IMAGE_THUMB_FILLED)->save($tmpStr);
            //这里本来写了再次打开tmpWater
            //thinkphp的机理就是save函数调用后,tmpWater自动成为Iamge的对象
            //因此不需要打开
            
            //测试最后的各个图片的大小和位置
            // echo("标准    图片 ".$bw." ".$bh." "." 水印 ".$bww." ".$bwh." 位置 ".$bpx." ".$bpy."<br>");
            // echo("现在    图片 ".$imgw." ".$imgh." "." 水印 ".$ww." ".$wh." 位置 ".$px." ".$py."<br>");
            // exit();

			//目前使用的水印就是上面生成的比例大小的水印文件
            //water的第一个参数是字符串类型的
            //这里我本来使用了imageInfo，因为之前已经打开过一张了
            //但是发现每次保存都保存成为现在的水印图片
            //不知道什么原因，无奈只能重新打开和保存
			if($Image->open($images[$i])->water($tmpWater,$location,80)->save($images[$i])){
              echo "<p>成功处理第".$i."张图片...</p>";
            }
        }
        echo "<hr><h1>图片处理完成....<h1>";
        echo "powered by  <a href='http://www.ptbird.cn'>postbird</a>";
    }
}