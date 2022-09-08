<?php
global $myUser;
if($myUser == null){
	$ret["status"]=-1;
	$ret["msg"]="用户未登录";
	echo $ret;
	return ;
}
$imageBed = new ImageBed();
$imageBed->upload($_FILES['file']);