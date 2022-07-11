<?php
	requirePHPLib('form');
	requirePHPLib('judger');
	requirePHPLib('data');
	if (!validateUInt($_GET['id']) || !($problem = queryProblemBrief($_GET['id']))) {
		become404Page();
	}
	if (!hasProblemPermission($myUser, $problem)) {
		become403Page();
	}
	
	$oj_name = UOJConfig::$data['profile']['oj-name'];
	$problem_extra_config = getProblemExtraConfig($problem);

	$data_dir = "/var/uoj_data/${problem['id']}";

	function echoFileNotFound($file_name) {
		echo '<h4>', htmlspecialchars($file_name), '<sub class="text-danger"> ', '文件未找到', '</sub></h4>';
	}
	function echoFilePre($file_name) {
		global $data_dir;
		$file_full_name = $data_dir . '/' . $file_name;

		$finfo = finfo_open(FILEINFO_MIME);
		$mimetype = finfo_file($finfo, $file_full_name);
		if ($mimetype === false) {
			echoFileNotFound($file_name);
			return;
		}
		finfo_close($finfo);

		echo '<h4>', htmlspecialchars($file_name), '<sub> ', $mimetype, '</sub></h4>';
		echo "<pre>\n";

		$output_limit = 1000;
		if (strStartWith($mimetype, 'text/')) {
			echo htmlspecialchars(uojFilePreview($file_full_name, $output_limit));
		} else {
			echo htmlspecialchars(uojFilePreview($file_full_name, $output_limit, 'binary'));
		}
		echo "\n</pre>";
	}


	//上传数据
	if ($_POST['problem_data_file_submit']=='submit') {
		if ($_FILES["problem_data_file"]["error"] > 0) {
			$errmsg = "Error: ".$_FILES["problem_data_file"]["error"];
			becomeMsgPage('<div>' . $errmsg . '</div><a href="/problem/'.$problem['id'].'/manage/data">返回</a>');
		} else {
			$zip_mime_types = array('application/zip', 'application/x-zip', 'application/x-zip-compressed');
			if (in_array($_FILES["problem_data_file"]["type"], $zip_mime_types)) {
				$up_filename="/tmp/".rand(0,100000000)."data.zip";
				move_uploaded_file($_FILES["problem_data_file"]["tmp_name"], $up_filename);
				$zip = new ZipArchive;
				if ($zip->open($up_filename) === TRUE) {
					$ret = mkdir("/var/uoj_data/transfer/{$problem['id']}", 0777, true);
					// if (!$ret) {
					// 	$error = error_get_last();
					// 	echo "<script>alert('${ret} ${error['message']}')</script>";
					// }
					$ret = $zip->extractTo("/var/uoj_data/transfer/{$problem['id']}");
					if (!$ret) {
						echo "<script>alert('解压失败')</script>";
					}
					$zip->close();
					exec("cd /var/uoj_data/transfer/{$problem['id']}; if [ `find . -maxdepth 1 -type f`File = File ]; then for sub_dir in `find -maxdepth 1 -type d ! -name .`; do mv -f \$sub_dir/* . && rm -rf \$sub_dir; done; fi");
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

	//添加配置文件


	$info_form = new UOJForm('info');
	$http_host = HTML::escape(UOJContext::httpHost());
	$download_url = HTML::url("/download.php?type=export&id={$problem['id']}");
	$info_form->appendHTML(<<<EOD
<div class="form-group row">
	<label class="col-sm-3 control-label">export_{$problem['id']}.zip</label>
	<div class="col-sm-9">
		<div class="form-control-static">
			<a href="$download_url">$download_url</a>
		</div>
	</div>
</div>
EOD
	);
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
?>
<?php
	$REQUIRE_LIB['dialog'] = '';
?>
<?php echoUOJPageHeader(HTML::stripTags($problem['title']) . ' - 迁移 - 题目管理') ?>
<h1 class="page-header" align="center">#<?=$problem['id']?> : <?=$problem['title']?> 管理</h1>
<ul class="nav nav-tabs" role="tablist">
<li class="nav-item"><a class="nav-link" href="/problem/<?= $problem['id'] ?>/manage/statement" role="tab">编辑</a></li>
	<li class="nav-item"><a class="nav-link" href="/problem/<?= $problem['id'] ?>/manage/managers" role="tab">管理者</a></li>
	<li class="nav-item"><a class="nav-link" href="/problem/<?= $problem['id'] ?>/manage/data" role="tab">数据</a></li>
	<li class="nav-item"><a class="nav-link active" href="/problem/<?= $problem['id'] ?>/manage/transfer" role="tab">迁移</a></li>
	<a class="nav-link" href="/problem/<?=$problem['id']?>" role="tab">返回</a></li>
</ul>

<div class="row">
	<!-- <div class="col-md-10 top-buffer-sm">
		<div class="row">
			<script type="text/javascript">
				curFileName = '';
				$('#div-file_list a').click(function(e) {
					$('#div-file_content').html('<h3>Loading...</h3>');
					$(this).tab('show');

					var fileName = $(this).text();
					curFileName = fileName;
					$.get('/problem/<?= $problem['id'] ?>/manage/data', {
							display_file: '',
							file_name: fileName
						},
						function(data) {
							if (curFileName != fileName) {
								return;
							}
							$('#div-file_content').html(data);
						},
						'html'
					);
					return false;
				});
			</script>
		</div>
	</div> -->
	<div class="col-md-8 top-buffer-sm">
	<div class="top-buffer-md">
			<?php $info_form->printHTML(); ?>
		</div>
	</div>
	<div class="col-md-2 top-buffer-sm">
	

		<div class="top-buffer-md">
			<?php $data_form->printHTML(); ?>
		</div>


		<div class="top-buffer-md">
			<button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#UploadDataModal">导入题目</button>
		</div>
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
									<p class="help-block">请将导出的UOJ题目文件(.ZIP)格式，上传，上传后点击加载会将之前的配置加载。</p>
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
	
</div>
<?php echoUOJPageFooter() ?>
