<?php

class UOJContext {
	public static $data = array();
	
	public static function pageConfig() {
		if (!isset(self::$data['type'])) {
			return array(
				'PageNav' => 'main-nav'
			);
		} else if (self::$data['type'] == 'blog') {
			return array(
				'PageNav' => 'blog-nav',
				'PageMainTitle' => UOJContext::$data['user']['username'] . '的博客',
				'PageMainTitleOnSmall' => '博客',
			);
		}
	}
	
	public static function isAjax() {
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
	}
	
	public static function contentLength() {
		if (!isset($_SERVER['CONTENT_LENGTH'])) {
			return null;
		}
		return (int)$_SERVER['CONTENT_LENGTH'];
	}
	
	public static function documentRoot() {
		return $_SERVER['DOCUMENT_ROOT'];
	}
	public static function storagePath() {
		return $_SERVER['DOCUMENT_ROOT'].'/app/storage';
	}
	public static function remoteAddr() {
		return $_SERVER['REMOTE_ADDR'];
	}
	public static function requestURI() {
		return $_SERVER['REQUEST_URI'];
	}
	public static function requestPath() {
		$uri = $_SERVER['REQUEST_URI'];
		$p = strpos($uri, '?');
		if ($p === false) {
			return $uri;
		} else {
			return substr($uri, 0, $p);
		}
	}
	public static function requestMethod() {
		return $_SERVER['REQUEST_METHOD'];
	}
	public static function httpHost() {
		if (isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
			return $_SERVER['HTTP_X_FORWARDED_HOST'];
		} else if (isset($_SERVER['HTTP_HOST'])) {
			return $_SERVER['HTTP_HOST'];
		} else {
			return $_SERVER['SERVER_NAME'].($_SERVER['SERVER_PORT'] == '80' ? '' : ':'.$_SERVER['SERVER_PORT']);
		}
	}
	public static function cookieDomain() {
		$domain = UOJConfig::$data['web']['domain'];
		if ($domain === null) {
			$domain = UOJConfig::$data['web']['main']['host'];
		}
		$domain = array_shift(explode(':', $domain));
		if (validateIP($domain) || $domain === 'localhost') {
			$domain = '';
		} else {
			$domain = '.'.$domain;
		}
		return $domain;
	}
	
	public static function setupBlog() {
		$username = blog_name_decode($_GET['blog_username']);
		if (!validateUsername($username) || !(self::$data['user'] = queryUser($username))) {
			become404Page();
		}
		if ($_GET['blog_username'] !== blog_name_encode(self::$data['user']['username'])) {
			permanentlyRedirectTo(HTML::blog_url(self::$data['user']['username'], '/'));
		}
		self::$data['type'] = 'blog';
	}
	
	public static function __callStatic($name, array $args) {
		switch (self::$data['type']) {
			case 'blog':
				switch ($name) {
					case 'user':
						return self::$data['user'];
					case 'userid':
						return self::$data['user']['username'];
					case 'hasBlogPermission':
						return Auth::check() && (isSuperUser(Auth::user()) || Auth::id() == self::$data['user']['username']);
					case 'isHis':
						if (!isset($args[0])) {
							return false;
						}
						$blog = $args[0];
						return $blog['poster'] == self::$data['user']['username'];
					case 'isHisBlog':
						if (!isset($args[0])) {
							return false;
						}
						$blog = $args[0];
						return $blog['poster'] == self::$data['user']['username'] && $blog['type'] == 'B' && $blog['is_draft'] == false;
					case 'isHisSlide':
						if (!isset($args[0])) {
							return false;
						}
						$blog = $args[0];
						return $blog['poster'] == self::$data['user']['username'] && $blog['type'] == 'S' && $blog['is_draft'] == false;
				}
				break;
		}
	}
}
