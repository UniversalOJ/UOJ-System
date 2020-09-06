<?php
	$new_user_msg_num = DB::selectCount("select count(*) from user_msg where receiver = '".Auth::id()."' and read_time is null");
	$new_system_msg_num = DB::selectCount("select count(*) from user_system_msg where receiver = '".Auth::id()."' and read_time is null");
	$new_msg_tot = $new_user_msg_num + $new_system_msg_num;
		
	if ($new_user_msg_num == 0) {
		$new_user_msg_num_html = '';
	} else {
		$new_user_msg_num_html = '<span class="badge badge-pill badge-secondary">'.$new_user_msg_num.'</span>';
	}
	if ($new_system_msg_num == 0) {
		$new_system_msg_num_html = '';
	} else {
		$new_system_msg_num_html = '<span class="badge badge-pill badge-secondary">'.$new_system_msg_num.'</span>';
	}
	if ($new_msg_tot == 0) {
		$new_msg_tot_html = '';
	} else {
		$new_msg_tot_html = '<sup><span class="badge badge-pill badge-secondary">'.$new_msg_tot.'</span></sup>';
	}
	
	if (!isset($PageMainTitle)) {
		$PageMainTitle = UOJConfig::$data['profile']['oj-name'];
	}
	if (!isset($PageMainTitleOnSmall)) {
		$PageMainTitleOnSmall = UOJConfig::$data['profile']['oj-name-short'];
	}
	if (!isset($ShowPageHeader)) {
		$ShowPageHeader = true;
	}
?>
<!DOCTYPE html>
<html lang="<?= UOJLocale::locale() ?>">
	<head>
		<meta charset="utf-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<?php if (isset($_GET['locale'])): ?>
		<meta name="robots" content="noindex, nofollow" />
		<?php endif ?>
		<title><?= isset($PageTitle) ? $PageTitle : UOJConfig::$data['profile']['oj-name-short'] ?> - <?= $PageMainTitle ?></title>
		
		<script type="text/javascript">uojHome = '<?= HTML::url('/') ?>'</script>

		<!-- Bootstrap core CSS -->
		<?= HTML::css_link('/css/bootstrap.min.css?v=2019.5.31') ?>
		<!-- Bootstrap Glyphicons CSS-->
		<?= HTML::css_link('/css/bootstrap-glyphicons.min.css?v=2019.5.31') ?>

		<!-- Custom styles for this template -->
		<?= HTML::css_link('/css/uoj-theme.css?v=2.3333') ?>
		
		<!-- jQuery (necessary for Bootstrap\'s JavaScript plugins) -->
		<?= HTML::js_src('/js/jquery.min.js') ?>
		
		<!-- jQuery autosize -->
		<?= HTML::js_src('/js/jquery.autosize.min.js') ?>
		<script type="text/javascript">
			$(document).ready(function() {
				$('textarea').autosize();
			});
		</script>
		
		<!-- jQuery cookie -->
		<?= HTML::js_src('/js/jquery.cookie.min.js') ?>
		
		<!-- jQuery modal -->
		<?= HTML::js_src('/js/jquery.modal.js') ?>
		
		<?php if (isset($REQUIRE_LIB['tagcanvas'])): ?>
		<!-- jQuery tag canvas -->
		<?= HTML::js_src('/js/jquery.tagcanvas.min.js') ?>
		<?php endif ?>
		
		<!-- Include all compiled plugins (below), or include individual files as needed -->
		<?= HTML::js_src('/js/popper.min.js?v=2019.5.31') ?>
		<?= HTML::js_src('/js/bootstrap.min.js?v=2019.5.31') ?>
		
		<!-- Color converter -->
		<?= HTML::js_src('/js/color-converter.min.js') ?>
		
		<!-- uoj -->
		<?= HTML::js_src('/js/uoj.js?v=2017.01.01') ?>
		
		<!-- readmore -->
		<?= HTML::js_src('/js/readmore/readmore.min.js') ?>
		
		<!-- LAB -->
		<?= HTML::js_src('/js/LAB.min.js') ?>

		<!-- favicon -->
		<link rel="shortcut icon" href="<?= HTML::url('/images/favicon.ico') ?>" />
		
		<?php if (isset($REQUIRE_LIB['blog-editor'])): ?>
		<!-- UOJ blog editor -->
		<?php $REQUIRE_LIB['jquery.hotkeys'] = '' ?>
		<?php $REQUIRE_LIB['switch'] = '' ?>
		<?= HTML::css_link('/js/codemirror/lib/codemirror.css') ?>
		<?= HTML::css_link('/css/blog-editor.css') ?>
		<?= HTML::js_src('/js/marked.js?v=2016.10.19') ?>
		<?= HTML::js_src('/js/blog-editor/blog-editor.js?v=2015.7.9') ?>
		<?= HTML::js_src('/js/codemirror/lib/codemirror.js') ?>
		<?= HTML::js_src('/js/codemirror/addon/mode/overlay.js') ?>
		<?= HTML::js_src('/js/codemirror/addon/selection/active-line.js') ?>
		<?= HTML::js_src('/js/codemirror/mode/xml/xml.js') ?>
		<?= HTML::js_src('/js/codemirror/mode/gfm/gfm.js') ?>
		<?= HTML::js_src('/js/codemirror/mode/markdown/markdown.js') ?>
		<?= HTML::js_src('/js/codemirror/mode/javascript/javascript.js') ?>
		<?= HTML::js_src('/js/codemirror/mode/css/css.js') ?>
		<?= HTML::js_src('/js/codemirror/mode/htmlmixed/htmlmixed.js') ?>
		<?= HTML::js_src('/js/codemirror/mode/clike/clike.js') ?>
		<?= HTML::js_src('/js/codemirror/mode/pascal/pascal.js') ?>
		<?php endif ?>
		
		<?php if (isset($REQUIRE_LIB['slide-editor'])): ?>
		<!-- UOJ slide editor -->
		<?= HTML::css_link('/js/codemirror/lib/codemirror.css') ?>
		<?= HTML::css_link('/css/slide-editor.css') ?>
		<?= HTML::js_src('/js/slide-editor/slide-editor.js') ?>
		<?= HTML::js_src('/js/codemirror/lib/codemirror.js') ?>
		<?= HTML::js_src('/js/codemirror/addon/mode/overlay.js') ?>
		<?= HTML::js_src('/js/codemirror/addon/selection/active-line.js') ?>
		<?php endif ?>
		
		<?php if (isset($REQUIRE_LIB['md5'])): ?>
		<!-- MD5 -->
		<?= HTML::js_src('/js/md5.min.js') ?>
		<?php endif ?>
		
		<?php if (isset($REQUIRE_LIB['dialog'])): ?>
		<!-- Bootstrap dialog -->
		<?= HTML::css_link('/css/bootstrap-dialog.min.css') ?>
		<?= HTML::js_src('/js/bootstrap-dialog.min.js') ?>
		<?php endif ?>
		
		<?php if (isset($REQUIRE_LIB['switch'])): ?>
		<!-- Bootstrap switch -->
		<?= HTML::css_link('/css/bootstrap-switch.min.css') ?>
		<?= HTML::js_src('/js/bootstrap-switch.min.js') ?>
		<?php endif ?>
		
		<?php if (isset($REQUIRE_LIB['mathjax'])): ?>
		<!-- MathJax -->
		<script type="text/x-mathjax-config">
			MathJax.Hub.Config({
				showProcessingMessages: false,
				tex2jax: {
					inlineMath: [["$", "$"], ["\\\\(", "\\\\)"]],
					processEscapes:true
				},
				menuSettings: {
					zoom: "Hover"
    			}
			});
		</script>
		<script src="//cdn.bootcss.com/mathjax/2.6.0/MathJax.js?config=TeX-AMS_HTML"></script>
		<?php endif ?>
		
		<?php if (isset($REQUIRE_LIB['jquery.form'])): ?>
		<!-- jquery form -->
		<?= HTML::js_src('/js/jquery.form.min.js') ?>
		<?php endif ?>
		
		<?php if (isset($REQUIRE_LIB['jquery.hotkeys'])): ?>
		<!-- jquery hotkeys -->
		<?= HTML::js_src('/js/jquery.hotkeys.js') ?>
		<?php endif ?>

		<?php if (isset($REQUIRE_LIB['flot'])): ?>
		<!-- flot -->
		<?= HTML::js_src('/js/jquery.flot.min.js') ?>
		<?= HTML::js_src('/js/jquery.flot.time.min.js') ?>
		<?= HTML::js_src('/js/jquery.flot.resize.min.js') ?>
		<?php
			$REQUIRE_LIB['colorhelpers'] = "";
		?>
		<?php endif ?>
		
		<?php if (isset($REQUIRE_LIB['colorhelpers'])): ?>
		<!-- colorhelpers -->
		<?= HTML::js_src('/js/jquery.colorhelpers.min.js') ?>
		<?php endif ?>
		
		<?php if (isset($REQUIRE_LIB['morris'])): ?>
		<!-- morris -->
		<?= HTML::js_src('/js/morris.min.js') ?>
		<?= HTML::css_link('/css/morris.css') ?>
		<?php $REQUIRE_LIB['raphael'] = "" ?>
		<?php endif ?>
		
		<?php if (isset($REQUIRE_LIB['raphael'])): ?>
		<!-- raphael -->
		<?= HTML::js_src('/js/raphael.min.js') ?>
		<?php endif ?>
		
		<?php if (isset($REQUIRE_LIB['shjs'])): ?>
		<!-- shjs -->
		<?= HTML::css_link('/css/sh_typical.min.css') ?>
		<?= HTML::js_src('/js/sh_main.min.js') ?>
		<script type="text/javascript">$(document).ready(function(){sh_highlightDocument()})</script>
		<?php endif ?>
		
		<?php if (isset($REQUIRE_LIB['ckeditor'])): ?>
		<!-- ckeditor -->
		<?= HTML::js_src('/js/ckeditor/ckeditor.js') ?>
		<?php endif ?>

		<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
			<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
		
		<script type="text/javascript">
		before_window_unload_message = null;
		$(window).on('beforeunload', function() {
			if (before_window_unload_message !== null) {
			    return before_window_unload_message;
			}
		});
		</script>
		
		<?php if (UOJConfig::$data['switch']['web-analytics']): ?>
		<script>var _hmt = _hmt || [];(function() {var hm = document.createElement("script");hm.src = "//hm.baidu.com/hm.js?bbd5ae87bf89e087603a1988103688ff";var s = document.getElementsByTagName("script")[0];s.parentNode.insertBefore(hm, s);})();</script>
		<?php endif ?>
	</head>
	<body role="document">
		<div class="container theme-showcase" role="main">
			<?php if ($ShowPageHeader): ?>
			<div>
				<ul class="nav nav-pills float-right" role="tablist">
				<?php if (Auth::check()): ?>
					<li class="nav-item dropdown">
						<a class="nav-link dropdown-toggle" href="#" data-toggle="dropdown">
							<span class="uoj-username" data-rating="<?= Auth::user()['rating'] ?>" data-link="0"><?= Auth::id() ?></span> <?= $new_msg_tot_html ?>
						</a>
						<ul class="dropdown-menu" role="menu">
							<li role="presentation"><a class="dropdown-item" href="<?= HTML::url('/user/profile/' . Auth::id()) ?>"><?= UOJLocale::get('my profile') ?></a></li>
							<li role="presentation"><a class="dropdown-item" href="<?= HTML::url('/user/msg') ?>"><?= UOJLocale::get('private message') ?>&nbsp;&nbsp;<?= $new_user_msg_num_html ?></a></li>
							<li role="presentation"><a class="dropdown-item" href="<?= HTML::url('/user/system-msg') ?>"><?= UOJLocale::get('system message') ?>&nbsp;&nbsp;<?= $new_system_msg_num_html ?></a></li>
							<?php if (isSuperUser(Auth::user())): ?>
								<li role="presentation"><a class="dropdown-item" href="<?= HTML::url('/super-manage') ?>"><?= UOJLocale::get('system manage') ?></a></li>
							<?php endif ?>
						</ul>
					</li>
					<li class="nav-item" role="presentation"><a class="nav-link" href="<?= HTML::url('/logout?_token='.crsf_token()) ?>"><?= UOJLocale::get('logout') ?></a></li>
				<?php else: ?>
					<li class="nav-item" role="presentation"><a class="nav-link" href="<?= HTML::url('/login') ?>"><?= UOJLocale::get('login') ?></a></li>
					<li class="nav-item" role="presentation"><a class="nav-link" href="<?= HTML::url('/register') ?>"><?= UOJLocale::get('register') ?></a></li>
				<?php endif ?>
				</ul>
				<h1 class="d-none d-sm-block"><a href="<?= HTML::url('/') ?>"><img src="<?= HTML::url('/images/logo_small.png') ?>" alt="Logo" class="img-rounded" style="width:39px; height:39px;" /></a> <?= $PageMainTitle ?></h1>
				<h1 class="d-block d-sm-none"><?= $PageMainTitleOnSmall ?></h1>
			</div>
			
			<?php uojIncludeView($PageNav) ?>
			<?php endif ?>
			
			<div class="uoj-content">
