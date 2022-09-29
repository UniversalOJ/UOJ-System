<?php
	requirePHPLib('form');
	requirePHPLib('judger');
	requirePHPLib('data');

#	echo '<pre>'; print_r($data_disp->data_files); echo '</pre>';

#	$data_disp->displayFile('problem.conf')


	$data_form2 = new UOJForm('data');
	$data_form2->handle = function() {
		global $myUser;
		set_time_limit(60 * 5);
		//$ret = dataSyncProblemData($problem, $myUser);

		$ret = problemImportBatch();
		echo $ret;
		if ($ret) {
			becomeMsgPage('<div>' . $ret . '</div><a href="/problem/'.$problem['id'].'/manage/transfer">返回</a>');
		} else {
			if ($ret) {
				becomeMsgPage('<div>加载成功，请查看题目和数据界面</div><a href="/problem/'.$problem['id'].'/manage/data">返回</a>');
			}
		}
	};
	$data_form2->submit_button_config['class_str'] = 'btn btn-danger btn-block';
	$data_form2->submit_button_config['text'] = '加载新题';
	$data_form2->submit_button_config['smart_confirm'] = '加载会连续创建新题';
	$data_form2->runAtServer();

if ($_POST['problem_data_file_submit']=='submit') {
	if ($_FILES["problem_data_file"]["error"] > 0) {
		$errmsg = "Error: ".$_FILES["problem_data_file"]["error"];
		becomeMsgPage('<div>' . $errmsg . '</div><a href="/problem/'.$problem['id'].'/manage/data">返回</a>');
	} else {
		$zip_mime_types = array('application/zip', 'application/x-zip', 'application/x-zip-compressed');
		if (in_array($_FILES["problem_data_file"]["type"], $zip_mime_types)) {
			$up_filename="/tmp/".rand(0,100000000)."import_batch_data.zip";
			move_uploaded_file($_FILES["problem_data_file"]["tmp_name"], $up_filename);
			$zip = new ZipArchive;
			if ($zip->open($up_filename) === TRUE) {
				system("rm -r /var/uoj_data/import_batch");
				$ret = mkdir("/var/uoj_data/import_batch", 0777, true);
				// if (!$ret) {
				// 	$error = error_get_last();
				// 	echo "<script>alert('${ret} ${error['message']}')</script>";
				// }
				$ret = $zip->extractTo("/var/uoj_data/import_batch/");
				if (!$ret) {
					echo "<script>alert('解压失败')</script>";
				}
				$zip->close();
				$allow_files = array_flip(array_filter(scandir("/var/uoj_data/import_batch"), function($x) {
					return $x !== '.' && $x !== '..' && $x != '__MACOSX';
				}));
				$len = count($allow_files);
				$cur = 0;
				foreach($allow_files as $k => $v) {
					system("mkdir -p /var/uoj_data/import_batch/".$cur);
					$zip = new ZipArchive();
					if ($zip->open("/var/uoj_data/import_batch/".$k) != true) {
						echo "<script>alert('解压失败')</script>";
					}
					$zip->extractTo("/var/uoj_data/import_batch/".$cur);
					$zip->close();
					$cur = $cur + 1;
				}
				foreach($allow_files as $k => $v) {
					system("rm /var/uoj_data/import_batch/".$k);
				}
				system("rm -rf /var/uoj_data/import_batch/__MACOSX");
				echo "<script>alert('上传成功！')</script>";
			} else {
				$errmsg = "解压失败！";
				becomeMsgPage('<div>' . $errmsg . '</div><a href="/problem/'.$problem['id'].'/manage/data">返回</a>');
			}
			unlink($up_filename);
		} else {
			$errmsg = "请上传zip格式！";
			becomeMsgPage('<div>' . $errmsg . '</div><a href="/problem/'.$problem['id'].'/manage/data">返回</a>');
		}
	}
}

?>
<?php echoUOJPageHeader("none") ?>

<h1 class="page-header text-center">题目批量迁移</h1>
<ul class="nav nav-tabs" role="tablist">
	<li class="nav-item"><a class="nav-link" href="/problems/batch_export" role="tab">导出</a></li>
	<li class="nav-item"><a class="nav-link active" href="/problems/batch_import" role="tab">导入</a></li>
</ul>
<div class="top-buffer-md">
	<?php $data_form2->printHTML(); ?>
</div>
<div class="modal fade" id="UploadDataModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  		<div class="modal-dialog">
    			<div class="modal-content">
      				<div class="modal-header">
						<h4 class="modal-title" id="myModalLabel">导入题目</h4>
        				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
      				</div>
      				<div class="modal-body">
        				<form action="" method="post" enctype="multipart/form-data" role="form">
							<div class="form-group">
									<label for="exampleInputFile">上传zip文件</label>
									<input type="file" name="problem_data_file" id="problem_data_file">
									<p class="help-block">请将之前批量导出的UOJ题目文件(batch.zip)上传，上传后点击加载会将之前的配置加载。</p>
							</div>
							<input type="hidden" name="problem_data_file_submit" value="submit">
      				</div>
      				<div class="modal-footer">
						<button type="submit" class="btn btn-success">上传</button>
						</form>
        				<button type="button" class="btn btn-secondary" data-dismiss="modal">关闭</button>
      				</div>
    			</div>
  		</div>
	</div>
<div class="top-buffer-md">
			<button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#UploadDataModal">导入题目</button>
		</div>
<?php echoUOJPageFooter() ?>
