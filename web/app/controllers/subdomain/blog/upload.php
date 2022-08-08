<?php
	global $myUser;
	header('Content-Type:text/json;charset=utf-8');
	$ret = [];
	if($myUser==null){
		$ret["status"]=-1;
		$ret["msg"]="用户未登录";
		echo $ret;
		return ;
	}
	// TODO: 将文件存储路径进行可配置
	$absPath = "/opt/uoj/web/app/upload/";
	$fileName = $myUser['username']."-".$_FILES['file']["name"];
	$base64FileName = base64_encode($fileName);
	$tmpName  = $_FILES['file']["tmp_name"];
	$ret["filename"] = $_FILES['file']["name"];
	$ret["tmpName"] = $tmpName;
	$ret["status"] = 0;
	if(file_exists($tmpName)){
		if(move_uploaded_file($tmpName,$absPath.$base64FileName)){
			$ret["path"] = $base64FileName;
			$ret["msg"]="成功";
			$ret["status"] = 0;
		}else{
			$ret["msg"]="失败";
			$ret["status"] = $_FILES['file']['error'];
		}
	}else{
		$ret["msg"]="文件不存在";
		$ret["status"] = $_FILES['file']['error'];
	}
	echo json_encode($ret);
