<?php
echo "<pre>";
print_r(listDir('./')); //遍历当前目录
echo "</pre>";


function listDir($dir){
    $dir .= substr($dir, -1) == '/' ? '' : '/';
    $dirInfo = array();
    foreach (glob($dir.'*') as $v) {
        $dirInfo[] = $v; 
        if(is_dir($v)){
            $dirInfo = array_merge($dirInfo, listDir($v));
        }
    }
    return $dirInfo;
    print_r($dirInfo); //遍历当前目录

}


function imageWater(){
	//获取当前文件夹的遍历
	$allImages=listDir('./');
	//进行水印处理
	$image = new \Think\Image();

	for($i=0;i<count($allImages);$i++){
		$imageItem=$allImages[$i];
		$image->open($imageItem);//打开图片
		// 给裁剪后的图片添加图片水印（水印文件位于./logo.png），位置为右下角，保存为water.gif
		$image->water('./water.png')->save("water.gif");
		// 给原图添加水印并保存为water_o.gif（需要重新打开原图）
		$image->open($imageItem)->water('./water.png')->save("water_o.gif"); 
	}	
}

	// imageWater();

