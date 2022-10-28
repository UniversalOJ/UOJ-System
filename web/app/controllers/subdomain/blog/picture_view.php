<?php
global $REQUIRE_LIB;
$REQUIRE_LIB['picture_preview'] = 1; ?>

<!-- bootstrap 5.x or 4.x is supported. You can also use the bootstrap css 3.3.x versions -->
<!-- default icons used in the plugin are from Bootstrap 5.x icon library (which can be enabled by loading CSS below) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.min.css" crossorigin="anonymous">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
<!-- following theme script is needed to use the Font Awesome 5.x theme (`fa5`). Uncomment if needed. -->
<?php
echoUOJPageHeader(UOJContext::user()['username'] . '的博客');
?>
<div class="row">
	<div class="col-md-2"></div>
	<div class="col-md-8">
		<h1>Image Upload</h1>
		5 MB max per file. 10 files max per request.
		<div class="container kv-main">
			<br>
			<form>
				<div class="form-group">
					<input id="file" class="file" multiple type="file" name="file" data-theme="fas"> <!-- 初始化插件 -->
				</div>
			</form>
		</div>
		<div id="picture_box"></div>
	</div>
	<div class="col-md-2"></div>

	<script>
		function addurl(url){
			html = "<div class='media'>" +
				"<div class='media-left media-middle'>"+
				"<a href='" +
				url +
				"'>"+
				"<img class='media-object' style='height: 64px;width: 64px' src='" +
				url +
				"' alt=''></a></div>"+
				"<div class='media-body' style='margin-left: 20px'>" +
				"<h4>url:</h4>" +
				"<input class='form-control' type='text' value='" +
				url +
				"'> </div> </div>"
			return html;
		}
		$("#file").fileinput({
			language: 'zh',  //語言設定
			uploadUrl: '/picture/upload',  //上傳地址
			overwriteInitial: false,
			allowedFileExtensions : ['jpeg','jpg','png','gif','bmp','webp'],
			maxFileSize: "5120",
			maxFileCount: "10",
		}).on("fileuploaded", function (event, data){
			var obj = data.response;
			address = window.location.host;
			address = "http://" + address;
			address += obj["path"];
			var box = $("#picture_box");
			box.append(addurl(address));
		});
	</script>
</div>
<?php echoUOJPageFooter(); ?>

