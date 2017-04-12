<?php

function rand_str($len, $charset = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
	$n_chars = strlen($charset);
	$str = '';
	for ($i = 0; $i < $len; $i++) {
		$str .= $charset[mt_rand(0, $n_chars - 1)];
	}
	return $str;
}

function translate($filename, $target, $tab) {
	$content = file_get_contents($filename);
	foreach ($tab as $k => $v) {
		$content = str_replace("__{$k}__", $v, $content);
	}
	file_put_contents($target, $content);
}

$svn_pwd = rand_str(32);
$svn_cert = '--username root --password '.$svn_pwd;

$config = [
	'profile' => [
		'oj-name'  => 'Universal Online Judge',
		'oj-name-short' => 'UOJ',
		'administrator' => 'root',
		'admin-email' => 'admin@local_uoj.ac',
		'QQ-group' => '',
		'ICP-license' => ''
	],
	'database' => [
		'database'  => 'app_uoj233',
		'username' => 'root',
		'password' => 'root',
		'host' => '127.0.0.1'
	],
	'web' => [
		'domain' => null,
		'main' => [
			'protocol' => 'http',
			'host' => '23333',
			'port' => 80
		],
		'blog' => [
			'protocol' => 'http',
			'host' => '23333',
			'port' => 80
		]
	],
	'security' => [
		'user' => [
			'client_salt' => rand_str(32)
		],
		'cookie' => [
			'checksum_salt' => [rand_str(16), rand_str(16), rand_str(16)]
		],
	],
	'mail' => [
		'noreply' => [
			'username' => 'noreply@none',
			'password' => 'noreply'
		]
	],
	'judger' => [
		'socket' => [
			'port' => 2333,
			'password' => rand_str(32)
		]
	],
	'svn' => [
		'our-root' => [
			'username' => 'our-root',
			'password' => rand_str(32)
		]
	],
	'switch' => [
		'ICP-license' => false,
		'web-analytics' => false,
		'blog-use-subdomain' => false
	]
];

$judge_client_config = [
	'uoj_protocol' => 'http',
	'uoj_host' => '127.0.0.1',
	'judger_name' => 'main_judger',
	'judger_password' => rand_str(32),
	'socket_port' => $config['judger']['socket']['port'],
	'socket_password' => $config['judger']['socket']['password'],
	'svn_username' => 'root',
	'svn_password' => $svn_pwd
];

$translate_table = [
	'svn_cert' => $svn_cert,
	'svn_pwd' => $svn_pwd,
	'our_root_password' => $config['svn']['our-root']['password'],
	'main_judger_password' => $judge_client_config['judger_password']
];

translate('new_problem.sh', '/var/svn/problem/new_problem.sh', $translate_table);
translate('post-commit.sh', '/var/svn/problem/post-commit.sh', $translate_table);

translate('uoj-passwd', '/var/svn/uoj/conf/passwd', $translate_table);
translate('uoj-post-commit', '/var/svn/uoj/hooks/post-commit', $translate_table);
file_put_contents('uoj_config.php', "<?php\nreturn ".str_replace('\'23333\'','isset($_SERVER[\'HTTP_X_FORWARDED_HOST\']) ? $_SERVER[\'HTTP_X_FORWARDED_HOST\'] : (isset($_SERVER[\'HTTP_HOST\']) ? $_SERVER[\'HTTP_HOST\'] : \'\')',var_export($config, true)).";\n");
file_put_contents('judge_client_config.json', json_encode($judge_client_config, JSON_PRETTY_PRINT));
translate('install', 'install', $translate_table);

