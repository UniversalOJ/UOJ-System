<?php
return [
	'profile' => [
		'oj-name'  => 'Universal Online Judge',
		'oj-name-short' => 'UOJ',
		'administrator' => 'admin',
		'admin-email' => 'admin@uoj',
		'qq-group' => '',
		'ICP-license' => ''
	],
	'database' => [
		'database'  => 'app_uoj233',
		'username' => 'root',
		'password' => '',
		'host' => '127.0.0.1'
	],
	'web' => [
		'domain' => null,
		'main' => [
			'protocol' => 'http',
			'host' => UOJContext::httpHost(),
			'port' => 80
		],
		'blog' => [
			'protocol' => 'http',
			'host' => UOJContext::httpHost(),
			'port' => 80
		]
	],
	'security' => [
		'user' => [
			'client_salt' => 'salt0'
		],
		'cookie' => [
			'checksum_salt' => ['salt1', 'salt2', 'salt3']
		],
	],
	'mail' => [
		'noreply' => [
			'username' => 'noreply@none',
			'password' => 'noreply',
			'host' => 'smtp.sina.com',
			'secure' => '',
			'port' => 25
		]
	],
	'judger' => [
		'socket' => [
			'port' => '233',
			'password' => 'password233'
		]
	],
	'svn' => [
		'our-root' => [
			'username' => 'our-root',
			'password' => 'our-root'
		]
	],
	'switch' => [
		'web-analytics' => false,
		'blog-use-subdomain' => false
	]
];
