<?php

class ImageBed{
	public  $path;// 图片保存路径
	public  $apiPath;// api请求路径
	function __construct(){
		$this->path = "/var/uoj_data/upload/";
		$this->apiPath = "/picture/v?p=";
	}
	public function upload($file){
		$retPath = ""; // 得到图片返回路径
		$fileName = $file['name'];
		$base64FileName = base64_encode($fileName);
		$tmpName  = $_FILES['file']["tmp_name"];
		if(file_exists($tmpName)){
			if(move_uploaded_file($tmpName,$this->path.$base64FileName)){
				$retPath = $this->apiPath.$base64FileName;
			}else{
				die(json_encode(array('error' => '未知错误')));
			}
		}
		 die(json_encode(array('path' => $retPath)));
	}
	public function download($filename){
		// 返回图片
		$filePath = $this->path.$filename;
		$filePath = iconv('UTF-8','gbk',$filePath);
		if(!is_file($filePath) || !is_readable($filePath)){
			exit("Can not open file: $filename");
		}
		$fileHandle=fopen($filePath,"rb");
		if($fileHandle===false){
			exit("Can not open file: $filename");
		}
		header('Content-type:application/octet-stream; charset=utf-8');
		header("Content-Transfer-Encoding: binary");
		header("Accept-Ranges: bytes");
		//文件大小
		header("Content-Length: ".filesize($filePath));
		//触发浏览器文件下载功能
		header('Content-Disposition:attachment;filename="'.urlencode($filename).'"');
		//循环读取文件内容，并输出
		while(!feof($fileHandle)) {
			//从文件指针 handle 读取最多 length 个字节（每次输出10k）
			echo fread($fileHandle, 10240);
		}
		//关闭文件流
		fclose($fileHandle);

	}
}