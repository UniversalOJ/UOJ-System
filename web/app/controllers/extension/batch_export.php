<?php
	requirePHPLib('form');
	requirePHPLib('judger');
	if ($_GET['export-batch'] != '') {
		$probs = $_GET['export-batch'];
		$probs = explode(',', $probs);
	}
	if($_GET['down']) {
		$probs = $_GET['down'];
		$probs = explode(',', $probs);
		download($probs);
		becomeMsgPage("下载中");
    }
    function download($probs)
    {
		function zipall($folder, $ziparchive) {
			$allow_files = array_flip(array_filter(scandir($folder), function($x) {
				return $x !== '.' && $x !== '..' && $x != "export.zip";
			}));
			foreach($allow_files as $k => $v) {
				if (file_exists($folder."/".$k)) {
					$ziparchive->addFile($folder."/".$k, $k);
				}
			}
			if ($ziparchive->close() == FALSE) {
				echo "failed ";
			} 
		}
		$data_dir = "/var/uoj_data";
		$zip_file = new ZipArchive;
		if (file_exists($data_dir."/export.lock")) {
			system("rm /var/uoj_data/export.lock");
			becomeMsgPage("正在进行其他导出，请等待");
		}
		system("rm ".$data_dir."/export_batch.zip");
		if ($zip_file->open($data_dir."/export_batch.zip", ZipArchive::CREATE) !== true) {
			becomeMsgPage('下载失败，请重试！');
		}
		system("touch ".$data_dir."/export.lock");
		foreach($probs as $p) {
			$r = queryProblemBrief($p);
			if ($r == null or $r == false) {
				continue;
			} 
			$file_dir = "/var/uoj_data/".$p;
			$zip_cur = new ZipArchive;
			system("rm ".$file_dir.".zip");
			if ($zip_cur->open($file_dir.".zip", ZipArchive::CREATE) !== True) {
				system("rm ".$data_dir."/export.lock");
				becomeMsgPage('下载失败，请重试！');
			}
			zipall($file_dir, $zip_cur);
			$zip_file->addFile($file_dir.".zip", $p.".zip");
		}
		$zip_file->close();
		header("Location: /download.php?type=exportbatch");
		exit();
    }	
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
?>
<?php echoUOJPageHeader("none") ?>

<h1 class="page-header text-center">题目批量迁移</h1>
<ul class="nav nav-tabs" role="tablist">
	<li class="nav-item"><a class="nav-link active" href="/problems/batch_export" role="tab">导出</a></li>
	<li class="nav-item"><a class="nav-link" href="/problems/batch_import" role="tab">导入</a></li>
</ul>
<div class="tab-content">
	<form id="checkForm" method="get"></form>
	<form id="submitForm" method="get"></form>
	<div class="input-group">
		<input type="text" class="form-control" name="export-batch" form="checkForm" placeholder="请输入要导出的题目编号，用英文逗号(,)隔开。如'1,2,3,5'" value="<?php echo gen($probs);?>" />  
		<div class="input-group-append">
			<button type="submit" class="btn btn-search btn-outline-primary" id="submit" form="checkForm"><span class="glyphicon"></span>确认题目</button>
			<button type="submit" class="btn btn-outline-primary" id="startdown" form="submitForm" name="down" value="<?php echo htmlspecialchars($_GET['export-batch']); ?>"><span class="glyphicon glyphicon-download"></span>确认题目后导出</button>
		</div>
	</div>

	<div class="col-md-9 top-buffer-sm" id="div-file_content">
				<?php gen_desp($probs) ?>
	</div>
</div>
<?php echoUOJPageFooter() ?>
