<?php

if (!isset($_POST["comment"])) {
	become404Page();
}
$comment_editor =new UOJCommentEditor($_POST["comment"]);
$comment_editor->printHTML();