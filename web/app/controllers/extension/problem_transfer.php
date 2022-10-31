<?php
     function DEBUG($data) {
          $output = $data;
          if (is_array($output))
          $output = implode(',', $output);
     
          echo "<script>console.log('Debug Objects: " . $output . "' );</script>";
     }
 
     requirePHPLib('form');
     requirePHPLib('judger');
     $root = "/root";
     if (!validateUInt($_GET['id']) || !($problem = queryProblemBrief($_GET['id']))) {
		become404Page();
	}
	
	$problem_content = queryProblemContent($problem['id']);
     $problem_content['name'] = "tmp";
     $problem_content['title'] = $problem['title'];
     $problem_content = json_encode($problem_content);
     $problem_extra_config = getProblemExtraConfig($problem);
     $problem_id = $problem['id'];
     $dir = "/home/transfer/" . $problem_id;
     echo $dir;
     if (!@mkdir($dir, 0777, true)) {
          $error = error_get_last();
          echo $error['message'];
     }
     $filename = $dir . "/config";
     $fd = fopen($filename, "w", 0);
     $ret = fwrite($fd, $problem_content);
     echo $ret;
?>
