<?php
	requirePHPLib('form');
	
	if (!UOJContext::hasBlogPermission()) {
		become403Page();
	}
	if (isset($_GET['id'])) {
		if (!validateUInt($_GET['id']) || !($blog = queryBlog($_GET['id'])) || !UOJContext::isHisBlog($blog)) {
			become404Page();
		}
	} else {
		$blog = DB::selectFirst("select * from blogs where poster = '".UOJContext::user()['username']."' and type = 'B' and is_draft = true");
	}
	$blog_editor = new UOJBlogEditor();
	$blog_editor->name = 'blog';
	if ($blog) {
		$blog["files"] = DB::selectAll("select filename,path from blogs_file where blog_id='".DB::escape($blog['id'])."'");
		$blog_editor->cur_data = array(
			'title' => $blog['title'],
			'content_md' => $blog['content_md'],
			'content' => $blog['content'],
			'tags' => queryBlogTags($blog['id']),
			'is_hidden' => $blog['is_hidden'],
			'files' => $blog["files"]
		);
	} else {
		$blog_editor->cur_data = array(
			'title' => '新博客',
			'content_md' => '',
			'content' => '',
			'tags' => array(),
			'is_hidden' => true
		);
	}
	if ($blog && !$blog['is_draft']) {
		$blog_editor->blog_url = HTML::blog_url(UOJContext::user()['username'], "/post/{$blog['id']}");
	} else {
		$blog_editor->blog_url = null;
	}
	
	function updateBlog($id, $data) {
		DB::update("update blogs set title = '".DB::escape($data['title'])."', content = '".DB::escape($data['content'])."', content_md = '".DB::escape($data['content_md'])."', is_hidden = {$data['is_hidden']} where id = {$id}");
		updateBlogOfFile($id,$data["fileList"]);
	}
	function insertBlog($data) {
		DB::insert("insert into blogs (title, content, content_md, poster, is_hidden, is_draft, post_time) values ('".DB::escape($data['title'])."', '".DB::escape($data['content'])."', '".DB::escape($data['content_md'])."', '".Auth::id()."', {$data['is_hidden']}, {$data['is_draft']}, now())");

	}
	function updateBlogOfFile($id,$data){
		$existFile = DB::selectAll("select filename from blogs_file where blog_id= '".DB::escape($id)."' ");
		foreach ($data as $file){
			$flag = false;
			$fileName = $file["fileName"];
			$filePath = $file["filePath"];
			foreach ($existFile as $efile){
				if($efile["filename"]==$fileName){
					$flag = true;
					break;
				}
			}
			if($flag){
				continue;
			}else{
				DB::insert("insert into blogs_file (blog_id,filename,path) values ('".DB::escape($id)."','".DB::escape($fileName)."','".DB::escape($filePath)."')");
			}
		}
	}
	
	$blog_editor->save = function($data) {
		global $blog;
		$ret = array();
		if ($blog) {
			if ($blog['is_draft']) {
				if ($data['is_hidden']) {
					updateBlog($blog['id'], $data);
				} else {
					deleteBlog($blog['id']);
					insertBlog(array_merge($data, array('is_draft' => 0)));
					$blog = array('id' => DB::insert_id(), 'tags' => array());
					updateBlogOfFile($blog['id'],$data["fileList"]);
					$ret['blog_write_url'] = HTML::blog_url(UOJContext::user()['username'], "/post/{$blog['id']}/write");
					$ret['blog_url'] = HTML::blog_url(UOJContext::user()['username'], "/post/{$blog['id']}");
				}
			} else {
				updateBlog($blog['id'], $data);
				//updateBlogOfFile($blog['id'],$data["fileList"]);
			}
		} else {
			insertBlog(array_merge($data, array('is_draft' => $data['is_hidden'] ? 1 : 0)));
			$blog = array('id' => DB::insert_id(), 'tags' => array());
			updateBlogOfFile($blog['id'],$data["fileList"]);
			if (!$data['is_hidden']) {
				$ret['blog_write_url'] = HTML::blog_url(UOJContext::user()['username'], "/post/{$blog['id']}/write");
				$ret['blog_url'] = HTML::blog_url(UOJContext::user()['username'], "/post/{$blog['id']}");
			}
		}
		if ($data['tags'] !== $blog['tags']) {
			DB::delete("delete from blogs_tags where blog_id = {$blog['id']}");
			foreach ($data['tags'] as $tag) {
				DB::insert("insert into blogs_tags (blog_id, tag) values ({$blog['id']}, '".DB::escape($tag)."')");
			}
		}
		return $ret;
	};
	$blog_editor->runAtServer();
?>
<?php echoUOJPageHeader('写博客') ?>
<div class="text-right">
<a href="http://uoj.ac/blog/7">这玩意儿怎么用？</a>
</div>
<?php $blog_editor->printHTML() ?>
<form id="form_example"	action="upload" enctype="multipart/form-data" method="post">
	<input type="file"  id="files" name="pic" multiple/>
	<input type="submit" value="上传">
</form>
<ul class="list-group" id="file-list-display">
	<li	class="list-group-item"></li>
</ul>
<?php echoUOJPageFooter() ?>
