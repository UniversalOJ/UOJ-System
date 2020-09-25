<div class="navbar navbar-light navbar-expand-md bg-light mb-4" role="navigation">
	<a class="navbar-brand" href="<?= HTML::url('/') ?>"><?= UOJConfig::$data['profile']['oj-name-short'] ?></a>
	<button type="button" class="navbar-toggler collapsed" data-toggle="collapse" data-target=".navbar-collapse">
		<span class="navbar-toggler-icon"></span>
	</button>
	<div class="collapse navbar-collapse" id="navbarSupportedContent">
		<ul class="nav navbar-nav mr-auto">
			<li class="nav-item"><a class="nav-link" href="<?= HTML::url('/contests') ?>"><span class="glyphicon glyphicon-stats"></span> <?= UOJLocale::get('contests') ?></a></li>
			<li class="nav-item"><a class="nav-link" href="<?= HTML::url('/problems') ?>"><span class="glyphicon glyphicon-list-alt"></span> <?= UOJLocale::get('problems') ?></a></li>
			<li class="nav-item"><a class="nav-link" href="<?= HTML::url('/submissions') ?>"><span class="glyphicon glyphicon-tasks"></span> <?= UOJLocale::get('submissions') ?></a></li>
			<li class="nav-item"><a class="nav-link" href="<?= HTML::url('/hacks') ?>"><span class="glyphicon glyphicon-flag"></span> <?= UOJLocale::get('hacks') ?></a></li>
			<li class="nav-item"><a class="nav-link" href="<?= HTML::blog_list_url() ?>"><span class="glyphicon glyphicon-edit"></span> <?= UOJLocale::get('blogs') ?></a></li>
			<li class="nav-item"><a class="nav-link" href="<?= HTML::url('/faq') ?>"><span class="glyphicon glyphicon-info-sign"></span> <?= UOJLocale::get('help') ?></a></li>
		</ul>
		<form id="form-search-problem" class="form-inline my-2 my-lg-0" method="get">
			 <div class="input-group">
				<input type="text" class="form-control" name="search" id="input-search" placeholder="<?= UOJLocale::get('search')?>" />  
				<div class="input-group-append">
					<button type="submit" class="btn btn-search btn-outline-primary" id="submit-search"><span class="glyphicon glyphicon-search"></span></button>
				</div>
			</div>
		</form>
	</div><!--/.nav-collapse -->
</div>
<script type="text/javascript">
	var zan_link = '';
	$('#form-search-problem').submit(function(e) {
		e.preventDefault();
		
		url = '<?= HTML::url('/problems') ?>';
		qs = [];
		$(['search']).each(function () {
			if ($('#input-' + this).val()) {
				qs.push(this + '=' + encodeURIComponent($('#input-' + this).val()));
			}
		});
		if (qs.length > 0) {
			url += '?' + qs.join('&');
		}
		location.href = url;
	});
</script>