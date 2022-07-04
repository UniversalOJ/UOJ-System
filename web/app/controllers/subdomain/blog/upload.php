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
	$ret["file"]=$_FILES;
	$fileName = $myUser['username']."-".$_FILES['file']["name"];
	$tmpName  = $_FILES['file']["tmp_name"];
	$uploads_dir = "upload/";
	$ret["filename"] = $fileName.time();
	$ret["tmpName"] = $tmpName;
	$ret["status"] = 0;
	if(file_exists($tmpName)){
		if(move_uploaded_file($tmpName, "/opt/uoj/web/app/upload/".$fileName)){
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
