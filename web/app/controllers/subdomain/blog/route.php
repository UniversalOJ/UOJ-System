<?php

call_user_func(function() { // to prevent variable scope leak

	Route::pattern('id', '[1-9][0-9]{0,9}');
	Route::pattern('blog_username', '[a-zA-Z0-9_\-]{1,20}');

	switch (UOJConfig::$data['switch']['blog-domain-mode']) {
		case 1:
			$domain = '{blog_username}.'.UOJConfig::$data['web']['blog']['host'];
			$prefix = '';
			break;
		case 2:
			$domain = UOJConfig::$data['web']['blog']['host'];
			$prefix = '/{blog_username}';
			break;
		case 3:
			$domain = UOJConfig::$data['web']['main']['host'];
			$prefix = '/blog/{blog_username}';
			break;
	}

	Route::group([
			'domain' => UOJConfig::$data['web']['blog']['host']
		], function() {
			Route::any("/", '/blogs.php');
			Route::any("/blogs/{id}", '/blog_show.php');
			Route::any("/post/{id}", '/blog_show.php');
		}
	);
	Route::group([
			'domain' => $domain,
			'onload' => function() {
				UOJContext::setupBlog();
			}
		], function() use ($prefix) {
			Route::any("$prefix/", '/subdomain/blog/index.php');
			Route::any("$prefix/archive", '/subdomain/blog/archive.php');
			Route::any("$prefix/aboutme", '/subdomain/blog/aboutme.php');
			Route::any("$prefix/click-zan", '/click_zan.php');
			Route::any("$prefix/post/{id}", '/subdomain/blog/blog.php');
			Route::any("$prefix/slide/{id}", '/subdomain/blog/slide.php');
			Route::any("$prefix/post/(?:{id}|new)/write", '/subdomain/blog/blog_write.php');
			Route::any("$prefix/slide/(?:{id}|new)/write", '/subdomain/blog/slide_write.php');
			Route::any("$prefix/post/{id}/delete", '/subdomain/blog/blog_delete.php');
		}
	);
});
