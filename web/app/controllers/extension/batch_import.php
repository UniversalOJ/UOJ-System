<?php
	requirePHPLib('form');
	requirePHPLib('judger');
	function gen($array_list) {
		$ret = '';
		foreach($array_list as $i) {
			if ($ret == '') {
				$ret = $i;
			} else {
				$ret = $ret.','.$i;
			}
		}
		return $ret;
	}
	function gen_desp($probs) {
		$problem = array();
		if (empty($probs)) {
			return;
		}
		foreach($probs as $p) {
			if ($p == '') {
				continue;
			}
			$r = queryProblemBrief($p);
			if ($r == null or $r == false) {
				$problem[$p] = "invalid id, will be ignored";
				#array_push($problem, "id ".$p." is invalid and will be ignored");
			} else {
				$problem[$p] = $r['title'];
			}
	#		array_push($problem, $r['title']);
		}
		echo '<div class="top-buffer-md"></div>';

				echo '<table class="table table-bordered table-hover table-striped table-text-center">';
				echo '<thead>';
				echo '<tr>';
				echo '<th>id</th>';
				echo '<th>title</th>';
				echo '</tr>';
				echo '</thead>';
				echo '<tbody>';
				foreach ($problem as $k => $v) {
						echo '<tr>';
						echo '<td>', htmlspecialchars($k), '</td>';
						echo '<td>', htmlspecialchars($v), '</td>';
						echo '</tr>';
				}
				echo '</tbody>';
				echo '</table>';
	}
#	echo '<pre>'; print_r($data_disp->data_files); echo '</pre>';

#	$data_disp->displayFile('problem.conf');
	class DataDisplayer {
		public $problem_conf = array();
		public $data_files = array();
		public $displayers = array();

		public function __construct($problem_conf = null, $data_files = null) {


		}

		public function setProblemConfRowStatus($key, $status) {
			$this->problem_conf[$key]['status'] = $status;
			return $this;
		}

		public function setDisplayer($file_name, $fun) {
			$this->displayers[$file_name] = $fun;
			return $this;
		}
		public function addDisplayer($file_name, $fun) {
			$this->data_files[] = $file_name;
			$this->displayers[$file_name] = $fun;
			return $this;
		}
		public function echoDataFilesList($active_file) {
			foreach ($this->data_files as $file_name) {
				echo '<li class="nav-item">';
				if ($file_name != $active_file) {
					echo '<a class="nav-link" href="#">';
				} else {
					echo '<a class="nav-link active" href="#">';
				}
				echo htmlspecialchars($file_name), '</a>', '</li>';
			}
		}
		public function displayFile($file_name) {
			global $data_dir;

			if (isset($this->displayers[$file_name])) {
				$fun = $this->displayers[$file_name];
				$fun($this);
			} elseif (in_array($file_name, $this->data_files)) {
				echoFilePre($file_name);
			} else {
				echoFileNotFound($file_name);
			}
		}
	}

	function getDataDisplayer() {
		global $data_dir;
		global $problem;

		$allow_files = array_flip(array_filter(scandir($data_dir), function($x) {
			return $x !== '.' && $x !== '..';
		}));

		$getDisplaySrcFunc = function($name) use ($allow_files) {
			return function() use ($name, $allow_files) {
				$src_name = $name . '.cpp';
				if (isset($allow_files[$src_name])) {
					echoFilePre($src_name);
				} else {
					echoFileNotFound($src_name);
				}
				if (isset($allow_files[$name])) {
					echoFilePre($name);
				} else {
					echoFileNotFound($name);
				}
			};
		};
		return new DataDisplayer();
	}

	$data_disp = getDataDisplayer();

	if (isset($_GET['display_file'])) {
		if (!isset($_GET['file_name'])) {
			echoFileNotFound('');
		} else {
			$data_disp->displayFile($_GET['file_name']);
		}
		die();
	}

	$data_form = new UOJForm('data');
	$data_form->handle = function() {
		global $problem, $myUser;
		set_time_limit(60 * 5);
		//$ret = dataSyncProblemData($problem, $myUser);
		$ret = problemLoadProblem($problem, $myUser);
		if ($ret) {
			becomeMsgPage('<div>' . $ret . '</div><a href="/problem/'.$problem['id'].'/manage/transfer">返回</a>');
		} else {
			if ($ret) {
				becomeMsgPage('<div>加载成功，请查看题目和数据界面</div><a href="/problem/'.$problem['id'].'/manage/data">返回</a>');
			}
		}
	};
	$data_form->submit_button_config['class_str'] = 'btn btn-danger btn-block';
	$data_form->submit_button_config['text'] = '加载';
	$data_form->submit_button_config['smart_confirm'] = '';
	
	$data_form->runAtServer();

if ($_POST['problem_data_file_submit']=='submit') {
	if ($_FILES["problem_data_file"]["error"] > 0) {
		$errmsg = "Error: ".$_FILES["problem_data_file"]["error"];
		becomeMsgPage('<div>' . $errmsg . '</div><a href="/problem/'.$problem['id'].'/manage/data">返回</a>');
	} else {
		$zip_mime_types = array('application/zip', 'application/x-zip', 'application/x-zip-compressed');
		if (in_array($_FILES["problem_data_file"]["type"], $zip_mime_types)) {
			$up_filename="/tmp/".rand(0,100000000)."batch_data.zip";
			move_uploaded_file($_FILES["problem_data_file"]["tmp_name"], $up_filename);
			$zip = new ZipArchive;
			if ($zip->open($up_filename) === TRUE) {
				system("rm -r /var/uoj_data/batch_transfer");
				$ret = mkdir("/var/uoj_data/batch_transfer", 0777, true);
				// if (!$ret) {
				// 	$error = error_get_last();
				// 	echo "<script>alert('${ret} ${error['message']}')</script>";
				// }
				$ret = $zip->extractTo("/var/uoj_data/batch_transfer");
				if (!$ret) {
					echo "<script>alert('解压失败')</script>";
				}
				$zip->close();
				$allow_files = array_flip(array_filter(scandir("/var/uoj_data/batch_transfer"), function($x) {
					return $x !== '.' && $x !== '..';
				}));
				$len = count($allow_files);
				$cur = 0;
				foreach($allow_files as $k => $v) {
					system("mkdir -p /var/uoj_data/batch_transfer/".$cur);
					$zip = new ZipArchive();
					if ($zip->open("/var/uoj_data/batch_transfer/".$k) != true) {
						echo "<script>alert('解压失败')</script>";
					}
					$zip->extractTo("/var/uoj_data/batch_transfer/".$cur);
					$zip->close();
					$cur = $cur + 1;

				}
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
			<?php $data_form->printHTML(); ?>
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
