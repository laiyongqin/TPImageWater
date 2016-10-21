<?php
/*
*
*	使用thinkphp 循环为已经上传的图片添加水印
*   遍历需要处理的所有文件夹里面的图片并进行处理
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

class FileImageWaterController extends Controller {
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
        //两个参数 第一个是要处理的文件夹 第二个是水印图片的本地地址
        //根据自己的经验，水印不能使用png位图来生成镂空效果
        //
    	$this->imageWater('./Uploads/image/','./Uploads/water.jpg');
    	//调用最终的imageWater水印处理
        $this->display();
    }

    //第一次遍历路径文件夹的内容  得到所有文件
    //参数为实体路径 - 注意不能使用http的路径
    //thinkphp的controller相对于跟路径就是 ./
    //这里用的  ./Public/images/
	public function getImagesPath($rootPath){
		//路径分割
		$rootPath .= substr($rootPath, -1) == '/' ? '' : '/';
		//存取第一次遍历结果
	    $dirPathInfo = array();
	    foreach (glob($rootPath.'*') as $item) {
	    	//必须进行中文转码，中文会乱码
	    	$item = iconv('GB2312','UTF-8',$item);
	        $dirPathInfo [] = $item; 
	        if(is_dir($item)){
	        	//递归遍历子目录
	            $dirPathInfo = array_merge($dirPathInfo, $this->getImagesPath($item));
	        }
	    }
	    return $dirPathInfo;
	    //第一次遍历得到的东西很多，包括子文件夹名称、所有格式的文件
	    //这里为了以后遍历其他文件能用，没有进行格式获取
	}

    //对第一次遍历的文件进行处理和筛选
    //这个函数可以根据需要进行不同文件内容的筛选 这里筛选 jpg\png\jpeg\gif
    //参数是路径 path
    public function allImages($path){
    	//存储最后有图片allImages内容
    	$trueImages=array();
    	$count=0;//计数标记
    	//调用第一次遍历,参数path，返回第一次遍历的而所有内容
    	$allImages=$this->getImagesPath($path);
    	for($i=0;$i<count($allImages);$i++){
    		//首先将子目录名称去掉 
    		//通过分割 / 得到每个路径分割后的内容 
    		$tempStr=explode("/",$allImages[$i]);
    		//由于子目录名称没有后续内容 因此再次通过.分割上面分割的内容 子目录被剔除
    		//同时得到文件的后缀  tempImage[0]是分割的文件名称  tempImage[1]是后缀 
    		$tempImage=explode(".",$tempStr[count($tempStr)-1]);
    		//进行后缀的比较 得到需要的图片
            //一开始使用 == 比较字符串,后面发现后缀可能是大写,因此使用cmp函数
            if(strcasecmp($tempImage[1],"jpg")==0||strcasecmp($tempImage[1],"gif")==0||strcasecmp($tempImage[1],"png")==0||strcasecmp($tempImage[1],"jpeg")==0){
    			//这里需要注意的是 最后我们需要的是完整的路径  因此最后保存的还是 $allImages[$i] 
    			//也就是第一次遍历出来的内容  只不过这个过程中我们剔除了不需要的文件
    			$trueImages[$count]=$allImages[$i];
    			$count++;
    		}else{
    			continue;
    		}
    	}
    	return $trueImages;
    	//返回最后真正需要的图片
    }
    //进行水印处理
    // 参数是路径 path water
    // 最后 path是用在第一次遍历身上的
    // water是水印的位置
    public function imageWater($path,$water){
    	//得到所有需要处理的图片
    	$images=$this->allImages($path);
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
            $bsubw=40;
            $bsubh=70;
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
            $Image->open($water)->thumb($ww, $wh,\Think\Image::IMAGE_THUMB_SCALE)->save($tmpStr);
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
        echo "<hr>图片处理完成...."
    }
    
}