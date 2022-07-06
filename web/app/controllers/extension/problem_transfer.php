<?php
     requirePHPLib('form');
     requirePHPLib('judger');
     $root = "/root";
     if (!validateUInt($_GET['id']) || !($problem = queryProblemBrief($_GET['id']))) {
		become404Page();
	}
	
	$problem_content = queryProblemContent($problem['id']);
     $problem_extra_config = getProblemExtraConfig($problem);
     echo $problem_content
?>
