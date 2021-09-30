<?php
	// Actually, these things should be done by main_judger so that the code would be much simpler.
	// However, this lib exists due to some history issues.
	
	function dataNewProblem($id) {
		mkdir("/var/uoj_data/upload/$id");
		mkdir("/var/uoj_data/$id");
		
		exec("cd /var/uoj_data; rm $id.zip; zip $id.zip $id -r -q");
	}

	class UOJProblemConfException extends Exception {
		public function __construct($message) {
			parent::__construct("<strong>problem.conf</strong> : $message");
		}
	}
	class UOJFileNotFoundException extends Exception {
		public function __construct($file_name) {
			parent::__construct("file <strong>" . htmlspecialchars($file_name) . '</strong> not found');
		}
	}
	
	function dataClearProblemData($problem) {
		$id = $problem['id'];
		if (!validateUInt($id)) {
			error_log("dataClearProblemData: hacker detected");
			return "invalid problem id";
		}
		
		exec("rm /var/uoj_data/upload/$id -r");
		exec("rm /var/uoj_data/$id -r");
		dataNewProblem($id);
	}
	
	class SyncProblemDataHandler {
		private $problem, $user;
		private $upload_dir, $data_dir, $prepare_dir;
		private $requirement, $problem_extra_config;
		private $problem_conf, $final_problem_conf;
		private $allow_files;
		
		public function __construct($problem, $user) {
			$this->problem = $problem;
			$this->user = $user;
		}
		
		private function check_conf_on($name) {
			return isset($this->problem_conf[$name]) && $this->problem_conf[$name] == 'on';
		}
		
		private function copy_to_prepare($file_name) {
			global $uojMainJudgerWorkPath;
			if (!isset($this->allow_files[$file_name])) {
				throw new UOJFileNotFoundException($file_name);
			}
			$src = escapeshellarg("{$this->upload_dir}/$file_name");
			$dest = escapeshellarg("{$this->prepare_dir}/$file_name");
			if (isset($this->problem_extra_config['dont_use_formatter']) || !is_file("{$this->upload_dir}/$file_name")) {
				exec("cp $src $dest -r", $output, $ret);
			} else {
				exec("$uojMainJudgerWorkPath/run/formatter <$src >$dest", $output, $ret);
			}
			if ($ret) {
				throw new UOJFileNotFoundException($file_name);
			}
		}
		private function copy_file_to_prepare($file_name) {
			global $uojMainJudgerWorkPath;
			if (!isset($this->allow_files[$file_name]) || !is_file("{$this->upload_dir}/$file_name")) {
				throw new UOJFileNotFoundException($file_name);
			}
			$this->copy_to_prepare($file_name);
		}
		private function compile_at_prepare($name, $config = array()) {
			global $uojMainJudgerWorkPath;
			$include_path = "$uojMainJudgerWorkPath/include";
			
			if (!isset($config['src'])) {
				$config['src'] = "$name.cpp";
			}
			
			if (isset($config['path'])) {
				exec("mv {$this->prepare_dir}/$name.cpp {$this->prepare_dir}/{$config['path']}/$name.cpp");
				$work_path = "{$this->prepare_dir}/{$config['path']}";
			} else {
				$work_path = $this->prepare_dir;
			}

			$cmd_prefix = "$uojMainJudgerWorkPath/run/run_program >{$this->prepare_dir}/run_compiler_result.txt --in=/dev/null --out=stderr --err={$this->prepare_dir}/compiler_result.txt --tl=10 --ml=512 --ol=64 --type=compiler --work-path={$work_path}";
			if (isset($config['need_include_header']) && $config['need_include_header']) {
				exec("$cmd_prefix --add-readable-raw=$include_path/ /usr/bin/g++ -o $name {$config['src']} -I$include_path -lm -O2 -DONLINE_JUDGE");
			} else {
				exec("$cmd_prefix /usr/bin/g++ -o $name {$config['src']} -lm -O2 -DONLINE_JUDGE");
			}
			
			$fp = fopen("{$this->prepare_dir}/run_compiler_result.txt", "r");
			if (fscanf($fp, '%d %d %d %d', $rs, $used_time, $used_memory, $exit_code) != 4) {
				$rs = 7;
			}
			fclose($fp);
			
			unlink("{$this->prepare_dir}/run_compiler_result.txt");
			
			if ($rs != 0 || $exit_code != 0) {
				if ($rs == 0) {
					throw new Exception("<strong>$name</strong> : compile error<pre>\n" . uojFilePreview("{$this->prepare_dir}/compiler_result.txt", 100) . "\n</pre>");
				} elseif ($rs == 7) {
					throw new Exception("<strong>$name</strong> : compile error. No comment");
				} else {
					throw new Exception("<strong>$name</strong> : compile error. Compiler " . judgerCodeStr($rs));
				}
			}
			
			unlink("{$this->prepare_dir}/compiler_result.txt");
			
			if (isset($config['path'])) {
				exec("mv {$this->prepare_dir}/{$config['path']}/$name.cpp {$this->prepare_dir}/$name.cpp");
				exec("mv {$this->prepare_dir}/{$config['path']}/$name {$this->prepare_dir}/$name");
			}
		}
		private function makefile_at_prepare() {
			global $uojMainJudgerWorkPath;
			
			$include_path = "$uojMainJudgerWorkPath/include";
			$cmd_prefix = "$uojMainJudgerWorkPath/run/run_program >{$this->prepare_dir}/run_makefile_result.txt --in=/dev/null --out=stderr --err={$this->prepare_dir}/makefile_result.txt --tl=10 --ml=512 --ol=64 --type=compiler --work-path={$this->prepare_dir}";
			exec("$cmd_prefix --add-readable-raw=$include_path/ /usr/bin/make INCLUDE_PATH=$include_path");
			
			$fp = fopen("{$this->prepare_dir}/run_makefile_result.txt", "r");
			if (fscanf($fp, '%d %d %d %d', $rs, $used_time, $used_memory, $exit_code) != 4) {
				$rs = 7;
			}
			fclose($fp);
			
			unlink("{$this->prepare_dir}/run_makefile_result.txt");
			
			if ($rs != 0 || $exit_code != 0) {
				if ($rs == 0) {
					throw new Exception("<strong>Makefile</strong> : compile error<pre>\n" . uojFilePreview("{$this->prepare_dir}/makefile_result.txt", 100) . "\n</pre>");
				} elseif ($rs == 7) {
					throw new Exception("<strong>Makefile</strong> : compile error. No comment");
				} else {
					throw new Exception("<strong>Makefile</strong> : compile error. Compiler " . judgerCodeStr($rs));
				}
			}
			
			unlink("{$this->prepare_dir}/makefile_result.txt");
		}
		
		public function handle() {
			$id = $this->problem['id'];
			if (!validateUInt($id)) {
				error_log("dataSyncProblemData: hacker detected");
				return "invalid problem id";
			}

			$this->upload_dir = "/var/uoj_data/upload/$id";
			$this->data_dir = "/var/uoj_data/$id";
			$this->prepare_dir = "/var/uoj_data/prepare_$id";

			if (file_exists($this->prepare_dir)) {
				return "please wait until the last sync finish";
			}

			try {
				$this->requirement = array();
				$this->problem_extra_config = json_decode($this->problem['extra_config'], true);

				$chk = 'ncmp';
				$submit = 'off';
				$samples = 1;
				$sub_inconf = 0;
				if(is_file("{$this->upload_dir}/problem.conf"))
				{
					$conf = getUOJConf("{$this->upload_dir}/problem.conf");
					$chk = getUOJConfVal($conf, 'use_builtin_checker','ncmp');
					$submit = getUOJConfVal($conf,'submit_answer','off');
					$samples = getUOJConfVal($conf,'n_sample_tests',1);
					$sub_inconf = getUOJConfVal($conf,'n_subtasks',0);
				}

				mkdir($this->prepare_dir, 0755);
				$files = my_dir($this->upload_dir);
				sort($files,SORT_NATURAL);
				$n = count($files);
				$in_files = array();
				$out_files = array();
				$sub_end = array();
				$sub_dep = array();
				$sub_dep_num = array();
				$sub_scores = array();
				$n_tests = 0;
				$customized_checker = 0;
				$customized_judger = 0;
				$ans = 0;
				$name_uoj_style = 0;
				$ex_num = 0;
				$traditional = 1;
				$sub_task = 0;
				for($num = 1; $num <= $n; $num++)
				{
					$cur = array_shift($files);
					$name = basename($cur);
					if(!strcmp($name,"Makefile"))
					{
						$traditional = 0;
					}
					$str = explode('.',$name);
					$pre = $str[0];
					$suf = $str[1];
					if(!strcmp($suf,"in"))
					{
						array_push($in_files,$pre);
					}
					else if(!strcmp($suf,"out"))
					{
						array_push($out_files,$pre);	
					}
					else if(!strcmp($suf,"ans"))
					{
						$ans = 1;
						array_push($out_files,$pre);
					}
					else if(!strcmp($suf,"txt"))
					{
						$name_uoj_style = 1;
						$sub = substr($pre,0,4);
						if(!strcmp($sub,"ex_i"))
						{
							$ex_num = $ex_num + 1;
						}
						else if(!strcmp($sub,"inpu"))
						{
							$n_tests = $n_tests + 1;
						}
					}
					else if(!strcmp($suf,"cpp"))
					{
						if(!strcmp($pre,"chk"))
						{
							$customized_checker = 1;
						}
						else if(!strcmp($pre,"judger"))
						{
							$customized_judger = 1;
						}
					}

					if(is_dir("{$this->upload_dir}/".$cur))
					{
						if(!strcmp($name,"require"))
						{
							$traditional = 0;
						}
						$sub = substr($name,0,7);
						
						if(!strcmp($sub,"subtask"))
						{
							$sub_task ++;
							$sonfiles = my_dir("{$this->upload_dir}/".$name);
							sort($sonfiles,SORT_NATURAL);
							$sn = count($sonfiles);

							$sin_files = array();
							$sout_files = array();
							$sn_tests = 0;
							$score = 0;
							$dependencies = 'none';
							if(is_file("{$this->upload_dir}/problem.conf"))
							{
								$score = getUOJConfVal($conf,"subtask_score_".$sub_task,0);
								$dependencies = getUOJConfVal($conf,"subtask_dependence_".$sub_task,"none");
								$dep_arr=array();
								$dep_num = 0;
								if(!strcmp($dependencies,"many"))
								{
									for ($i = 1; $i <= $sub_task;$i ++)
									{
										$dependencies = getUOJConfVal($conf,"subtask_dependence_".$sub_task."_".$i,0);
										if($dependencies > 0)
										{
											$dep_num ++;
											array_push($dep_arr,$dependencies);
										}
									}
									array_push($sub_dep,$dep_arr);
								}
								array_push($sub_dep_num,$dep_num);
							}
							array_push($sub_scores,$score);
							for($i = 1;$i <= $sn;$i++)
							{
								$cur = array_shift($sonfiles);
								$name1 = basename($cur);
								$str = explode('.',$name1);
								$pre = $str[0];
								$suf = $str[1];
								if(!strcmp($suf,"in"))
								{
									array_push($sin_files,$pre);
								}
								else if(!strcmp($suf,"out"))
								{
									array_push($sout_files,$pre);	
								}
								else if(!strcmp($suf,"ans"))
								{
									$ans = 1;
									array_push($sout_files,$pre);
								}
							}
							
							$sn = count($sin_files);
							for($i = 0; $i < $sn; $i++)
							{
								$in = array_shift($sin_files);
								if(!in_array($in,$sout_files))
								{
									if(!$ans)
									{
										throw new UOJFileNotFoundException($in.".out");
									}
									else
									{
										throw new UOJFileNotFoundException($in.".ans");
									}
								}
								$key = array_search($in,$sout_files);
								unset($sout_files[$key]);
								$n_tests =$n_tests + 1;
								copy("{$this->upload_dir}/".$name."/".$in.".in","{$this->upload_dir}/auto".$n_tests.".in");
								if(!$ans)
								{
									copy("{$this->upload_dir}/".$name."/".$in.".out","{$this->upload_dir}/auto".$n_tests.".out");
								}
								else
								{
									copy("{$this->upload_dir}/".$name."/".$in.".ans","{$this->upload_dir}/auto".$n_tests.".ans");
								}
									

							}
							array_push($sub_end,$n_tests);
							if(!empty($sout_files))
							{
								$out = array_shift($sout_files);
								throw new UOJFileNotFoundException($out.".in");
							}

					}
					}
				}
				if(!$name_uoj_style && $traditional && ($sub_inconf == 0))
				{
					$n = count($in_files);
					for($num = 0; $num < $n; $num++)
					{
						$in = array_shift($in_files);
						if(!in_array($in,$out_files))
						{
							if(!$ans)
							{
								throw new UOJFileNotFoundException($in.".out");
							}
							else
							{
								throw new UOJFileNotFoundException($in.".ans");
							}
						}
						$key = array_search($in,$out_files);
						unset($out_files[$key]);
						$sub = substr($in,0,3);
						if(!strcmp($sub,"ex_"))
						{
							$ex_num = $ex_num + 1;
							rename("{$this->upload_dir}/".$in.".in","{$this->upload_dir}/ex_auto".$ex_num.".in");
							if(!$ans)
							{
								rename("{$this->upload_dir}/".$in.".out","{$this->upload_dir}/ex_auto".$ex_num.".out");
							}
							else
							{
								rename("{$this->upload_dir}/".$in.".ans","{$this->upload_dir}/ex_auto".$ex_num.".ans");
							}
						}
						else
						{
							$n_tests =$n_tests + 1;
							rename("{$this->upload_dir}/".$in.".in","{$this->upload_dir}/auto".$n_tests.".in");
							if(!$ans)
							{
								rename("{$this->upload_dir}/".$in.".out","{$this->upload_dir}/auto".$n_tests.".out");
							}
							else
							{
								rename("{$this->upload_dir}/".$in.".ans","{$this->upload_dir}/auto".$n_tests.".ans");
							}
							
						}
					}
					if(!empty($out_files))
					{
						$out = array_shift($out_files);
						throw new UOJFileNotFoundException($out.".in");
					}
				}

				if($traditional && ($sub_inconf == 0)){

					$conf = fopen("{$this->upload_dir}/problem.conf", "w");
					fwrite($conf,"n_tests ".$n_tests);
					if($ex_num > 0)
					{
						fwrite($conf,"\nn_ex_tests ".$ex_num);
					}
					if(!strcmp($submit,"on"))
					{
						fwrite($conf,"\nsubmit_answer on\n");
					}
					else
					{
						fwrite($conf,"\nn_sample_tests ".$samples."\n");
					}
					if($sub_task > 0)
					{
						fwrite($conf,"n_subtasks ".$sub_task);
						for($i = 1;$i <= $sub_task; $i ++)
						{
							$end = array_shift($sub_end);
							fwrite($conf,"\nsubtask_end_".$i." ".$end);
							$score = array_shift($sub_scores);
							if($score > 0)
							{
								fwrite($conf,"\nsubtask_score_".$i." ".$score);
							}
							$dep_n=array_shift($sub_dep_num);
							if($dep_n > 0)
							{
								fwrite($conf,"\nsubtask_dependence_".$i." many");
								$dep_arr =array_shift($sub_dep);
								for($j = 1;$j <= $dep_n;$j ++)
								{
									
									$dep = array_shift($dep_arr);
									fwrite($conf,"\nsubtask_dependence_".$i."_".$j." ".$dep);
								}
							}
						}
						fwrite($conf,"\n");
					}
					if(!$name_uoj_style)
					{
						fwrite($conf,"input_pre auto\n");
						fwrite($conf,"input_suf in\n");
						fwrite($conf,"output_pre auto\n");
					}
					else if($traditional)
					{
						fwrite($conf,"input_pre input\n");
						fwrite($conf,"input_suf txt\n");
						fwrite($conf,"output_pre output\n");
					}
					if(!$ans)
					{
						if(!$name_uoj_style)
							fwrite($conf,"output_suf out\n");
						else
							fwrite($conf,"output_suf txt\n");
					}
					else
					{
						fwrite($conf,"output_suf ans\n");
					}
					
					if(!$customized_judger)
					{
						fwrite($conf,"\nuse_builtin_judger on");
					}
					if(!$customized_checker)
					{
						fwrite($conf,"\nuse_builtin_checker ".$chk);
					}
				}
				else if(!is_file("{$this->upload_dir}/problem.conf"))
				{
					$conf = fopen("{$this->upload_dir}/problem.conf", "w");
					fwrite($conf,"\ntime_limit 1\nmemory_limit 256\noutput_limit 64");
					fclose($conf);
				}
				
				#throw new UOJFileNotFoundException("problem.conf");
				$this->problem_conf = getUOJConf("{$this->upload_dir}/problem.conf");
				$this->final_problem_conf = $this->problem_conf;
				if ($this->problem_conf === -1) {
					throw new UOJFileNotFoundException("problem.conf");
				} elseif ($this->problem_conf === -2) {
					throw new UOJProblemConfException("syntax error");
				}

				$this->allow_files = array_flip(array_filter(scandir($this->upload_dir), function($x){return $x !== '.' && $x !== '..';}));

				$zip_file = new ZipArchive();
				if ($zip_file->open("{$this->prepare_dir}/download.zip", ZipArchive::CREATE) !== true) {
					throw new Exception("<strong>download.zip</strong> : failed to create the zip file");
				}
				
				if (isset($this->allow_files['require']) && is_dir("{$this->upload_dir}/require")) {
					$this->copy_to_prepare('require');
				}

				if ($this->check_conf_on('use_builtin_judger')) {
					$n_tests = getUOJConfVal($this->problem_conf, 'n_tests', 10);
					if (!validateUInt($n_tests) || $n_tests <= 0) {
						throw new UOJProblemConfException("n_tests must be a positive integer");
					}
					for ($num = 1; $num <= $n_tests; $num++) {
						$input_file_name = getUOJProblemInputFileName($this->problem_conf, $num);
						$output_file_name = getUOJProblemOutputFileName($this->problem_conf, $num);

						$this->copy_file_to_prepare($input_file_name);
						$this->copy_file_to_prepare($output_file_name);
					}

					if (!$this->check_conf_on('interaction_mode')) {
						if (isset($this->problem_conf['use_builtin_checker'])) {
							if (!preg_match('/^[a-zA-Z0-9_]{1,20}$/', $this->problem_conf['use_builtin_checker'])) {
								throw new Exception("<strong>" . htmlspecialchars($this->problem_conf['use_builtin_checker']) . "</strong> is not a valid checker");
							}
						} else {
							$this->copy_file_to_prepare('chk.cpp');
							$this->compile_at_prepare('chk', array('need_include_header' => true));
						}
					}
					
					if ($this->check_conf_on('submit_answer')) {
						if ($this->problem['hackable']) {
							throw new UOJProblemConfException("the problem can't be hackable if submit_answer is on");
						}

						for ($num = 1; $num <= $n_tests; $num++) {
							$input_file_name = getUOJProblemInputFileName($this->problem_conf, $num);
							$output_file_name = getUOJProblemOutputFileName($this->problem_conf, $num);
							
							if (!isset($this->problem_extra_config['dont_download_input'])) {
								$zip_file->addFile("{$this->prepare_dir}/$input_file_name", "$input_file_name");
							}

							$this->requirement[] = array('name' => "output$num", 'type' => 'text', 'file_name' => $output_file_name);
						}
					} else {
						$n_ex_tests = getUOJConfVal($this->problem_conf, 'n_ex_tests', 0);
						if (!validateUInt($n_ex_tests) || $n_ex_tests < 0) {
							throw new UOJProblemConfException("n_ex_tests must be a non-nagative integer");
						}

						for ($num = 1; $num <= $n_ex_tests; $num++) {
							$input_file_name = getUOJProblemExtraInputFileName($this->problem_conf, $num);
							$output_file_name = getUOJProblemExtraOutputFileName($this->problem_conf, $num);

							$this->copy_file_to_prepare($input_file_name);
							$this->copy_file_to_prepare($output_file_name);
						}

						if ($this->problem['hackable']) {
							$this->copy_file_to_prepare('std.cpp');
							if (isset($this->problem_conf['with_implementer']) && $this->problem_conf['with_implementer'] == 'on') {
								$this->compile_at_prepare('std',
									array(
										'src' => 'implementer.cpp std.cpp',
										'path' => 'require'
									)
								);
							} else {
								$this->compile_at_prepare('std');
							}
							$this->copy_file_to_prepare('val.cpp');
							$this->compile_at_prepare('val', array('need_include_header' => true));
						}
						
						if ($this->check_conf_on('interaction_mode')) {
							$this->copy_file_to_prepare('interactor.cpp');
							$this->compile_at_prepare('interactor', array('need_include_header' => true));
						}

						$n_sample_tests = getUOJConfVal($this->problem_conf, 'n_sample_tests', $n_tests);
						if (!validateUInt($n_sample_tests) || $n_sample_tests < 0) {
							throw new UOJProblemConfException("n_sample_tests must be a non-nagative integer");
						}
						if ($n_sample_tests > $n_ex_tests) {
							throw new UOJProblemConfException("n_sample_tests can't be greater than n_ex_tests");
						}

						if (!isset($this->problem_extra_config['dont_download_sample'])) {
							for ($num = 1; $num <= $n_sample_tests; $num++) {
								$input_file_name = getUOJProblemExtraInputFileName($this->problem_conf, $num);
								$output_file_name = getUOJProblemExtraOutputFileName($this->problem_conf, $num);
								$zip_file->addFile("{$this->prepare_dir}/{$input_file_name}", "$input_file_name");
								if (!isset($this->problem_extra_config['dont_download_sample_output'])) {
									$zip_file->addFile("{$this->prepare_dir}/{$output_file_name}", "$output_file_name");
								}
							}
						}

						$this->requirement[] = array('name' => 'answer', 'type' => 'source code', 'file_name' => 'answer.code');
					}
				} else {
					if (!isSuperUser($this->user)) {
						throw new UOJProblemConfException("use_builtin_judger must be on.");
					} else {
						foreach ($this->allow_files as $file_name => $file_num) {
							$this->copy_to_prepare($file_name);
						}
						$this->makefile_at_prepare();
						
						$this->requirement[] = array('name' => 'answer', 'type' => 'source code', 'file_name' => 'answer.code');
					}
				}
				putUOJConf("{$this->prepare_dir}/problem.conf", $this->final_problem_conf);

				if (isset($this->allow_files['download']) && is_dir("{$this->upload_dir}/download")) {
					foreach (scandir("{$this->upload_dir}/download") as $file_name) {
						if (is_file("{$this->upload_dir}/download/{$file_name}")) {
							$zip_file->addFile("{$this->upload_dir}/download/{$file_name}", $file_name);
						}
					}
				}
				
				$zip_file->close();

				$orig_requirement = json_decode($this->problem['submission_requirement'], true);
				if (!$orig_requirement) {
					$esc_requirement = DB::escape(json_encode($this->requirement));
					DB::update("update problems set submission_requirement = '$esc_requirement' where id = $id");
				}
			} catch (Exception $e) {
				exec("rm {$this->prepare_dir} -r");
				return $e->getMessage();
			}

			exec("rm {$this->data_dir} -r");
			rename($this->prepare_dir, $this->data_dir);
		
			exec("cd /var/uoj_data; rm $id.zip; zip $id.zip $id -r -q");

			return '';
		}
	}
	
	function dataSyncProblemData($problem, $user = null) {
		return (new SyncProblemDataHandler($problem, $user))->handle();
	}
	function dataAddExtraTest($problem, $input_file_name, $output_file_name) {
		$id = $problem['id'];

		$cur_dir = "/var/uoj_data/upload/$id";
		
		$problem_conf = getUOJConf("{$cur_dir}/problem.conf");
		if ($problem_conf == -1 || $problem_conf == -2) {
			return $problem_conf;
		}
		$problem_conf['n_ex_tests'] = getUOJConfVal($problem_conf, 'n_ex_tests', 0) + 1;
		
		$new_input_name = getUOJProblemExtraInputFileName($problem_conf, $problem_conf['n_ex_tests']);
		$new_output_name = getUOJProblemExtraOutputFileName($problem_conf, $problem_conf['n_ex_tests']);
		
		putUOJConf("$cur_dir/problem.conf", $problem_conf);
		move_uploaded_file($input_file_name, "$cur_dir/$new_input_name");
		move_uploaded_file($output_file_name, "$cur_dir/$new_output_name");
		
		if (dataSyncProblemData($problem) === '') {
			rejudgeProblemAC($problem);
		} else {
			error_log('hack successfully but sync failed.');
		}
	}
	function my_dir($dir) 
	{
		$num = 0;
		$files = array();
		if(@$handle = opendir($dir)) 
		{
			while(($file = readdir($handle)) !== false) 
			{
				if($file != ".." && $file != ".") 
				{
					array_push($files, $file);
					$num ++;
				}
			}
			closedir($handle);
			return $files;
		}
	}
?>
