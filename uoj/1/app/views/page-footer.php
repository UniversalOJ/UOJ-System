<?php
	if (!isset($ShowPageFooter)) {
		$ShowPageFooter = true;
	}
?>
			</div>
			<?php if ($ShowPageFooter): ?>
			<div class="uoj-footer">
				<p>
					<a href="<?= HTML::url(UOJContext::requestURI(), array('params' => array('locale' => 'zh-cn'))) ?>"><img src="/pictures/lang/cn.png" alt="中文" /></a> 
					<a href="<?= HTML::url(UOJContext::requestURI(), array('params' => array('locale' => 'en'))) ?>"><img src="/pictures/lang/gb.png" alt="English" /></a>
				</p>
				
				<ul class="list-inline">
					<li><?= UOJConfig::$data['profile']['oj-name'] ?></li>
					<?php if (UOJConfig::$data['profile']['ICP-license'] != ''): ?> | 
					<li><a href="http://www.miitbeian.gov.cn" target="_blank"><?= UOJConfig::$data['profile']['ICP-license'] ?></a></li>
					<?php endif ?>
				</ul>
				
				<p><?= UOJLocale::get('server time') ?>: <?= UOJTime::$time_now_str ?> | <a href="http://github.com/UniversalOJ/UOJ-System" target="_blank"><?= UOJLocale::get('opensource project') ?></a></p>
			</div>
			<?php endif ?>
		</div>
		<!-- /container -->
	</body>
</html>
