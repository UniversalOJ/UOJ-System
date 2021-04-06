<?php
requirePHPLib('form');
requirePHPLib('judger');

if ($myUser == null) {
	becomeMsgPage("请先登录!");
}


function handleUpload($zip_file_name, $content, $tot_size) {
	global $myUser;
	$esc_content = DB::escape(json_encode($content));
	$index = uojRandString(20);
	$esc_index = DB::escape($index);
	while (DB::selectFirst("select count(*) as count from pastes where `index` = '$esc_index'")['count'] != "0") {
		$index = uojRandString(20);
		$esc_index = DB::escape($index);
	}
	DB::query("insert into pastes (`index`, `creator`, `content`, `created_at`) values ('$esc_index', '${myUser['username']}', '$esc_content', '".date('Y-m-d H:i:s')."')");
	redirectTo("/pastes/".$index);
}

$paste_form = newSubmissionForm('paste',
	[
		[
			'type' => "source code",
			"name" => "paste",
			"file_name" => "paste.code"
		]
	],
	'uojRandAvaiablePasteFileName',
	'handleUpload');
$paste_form->succ_href = '/paste';
$paste_form->runAtServer();
echoUOJPageHeader("Paste!");
$paste_form->printHTML();
echoUOJPageFooter();