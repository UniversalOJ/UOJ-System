<?php

class UOJCommentEditor{
	public $comment_md; // 渲染后
	public $comment; // 渲染前
	public $ret;

	function __construct($_comment)
	{
		$this->comment =  $_comment;
		$this->mdToHTML();
	}
	function mdToHTML(){
		try {
			$v8 = new V8Js('POST');
			$v8->content_md = $this->comment;
			$v8->executeString(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/js/marked.js'), 'marked.js');
			$this->comment_md = $v8->executeString('marked(POST.content_md)');
		} catch (V8JsException $e) {
			die(json_encode(array('content_md' => '未知错误')));
		}
	}
	function printHTML(){
		ob_start();
		echoUOJPageHeader('评论预览', array('ShowPageHeader' => false, 'REQUIRE_LIB' => array('mathjax' => '', 'shjs' => '')));
		echo '<article>';
		echo $this->comment_md;
		echo '</article>';
		echoUOJPageFooter(array('ShowPageFooter' => false));
		$ret["html"] = ob_get_contents();
		ob_end_clean();
		die(json_encode($ret));
	}
}