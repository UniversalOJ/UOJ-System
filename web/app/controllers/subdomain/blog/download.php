<?php
//接收需要下载的文件名称
if(!isset($_GET['p'])) exit('Filename is empty');
if(empty($_GET['p'])||empty($_GET['name'])) exit('Filename not valid');
ob_clean();//清除一下缓冲区
//获得文件名称
$filename = basename(urldecode($_GET['p']));
$fileAlias = urldecode($_GET['name']);
//文件完整路径（这里将真实的文件存放在temp目录下）
$filePath = "/opt/uoj/web/app/upload/".$filename;
//将utf8编码转换成gbk编码，否则，文件中文名称的文件无法打开
$filePath = iconv('UTF-8','gbk',$filePath);
//检查文件是否可读
if(!is_file($filePath) || !is_readable($filePath)) exit('Can not access file '.$filename);
/**
 * 这里应该加上安全验证之类的代码，例如：检测请求来源、验证UA标识等等
 */
//以只读方式打开文件，并强制使用二进制模式
$fileHandle=fopen($filePath,"rb");
if($fileHandle===false){
	exit("Can not open file: $filename");
}
//文件类型是二进制流。设置为utf8编码（支持中文文件名称）
header('Content-type:application/octet-stream; charset=utf-8');
header("Content-Transfer-Encoding: binary");
header("Accept-Ranges: bytes");
//文件大小
header("Content-Length: ".filesize($filePath));
//触发浏览器文件下载功能
header('Content-Disposition:attachment;filename="'.urlencode($fileAlias).'"');
//循环读取文件内容，并输出
while(!feof($fileHandle)) {
	//从文件指针 handle 读取最多 length 个字节（每次输出10k）
	echo fread($fileHandle, 10240);
}
//关闭文件流
fclose($fileHandle);