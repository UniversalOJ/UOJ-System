<?php

$REQUIRE_LIB['shjs'] = "";
$REQUIRE_LIB['dracula'] = "";
$REQUIRE_LIB['base64'] = "";
$REQUIRE_LIB['raphael'] = "";
echoUOJPageHeader("图可视化");

?>
<div class="container">
	<div class="row">
		<div class="col-md-3">
			<div class="form-check">
				<input class="form-check-input" type="checkbox" id="directed" name="directed">
				<label class="form-check-label" for="directed">
					是否有向
				</label>
			</div>
			<div class="form-group">
				<textarea class="form-control" id="edges" rows="3" placeholder="格式:
第一条边起点 第一条边终点 [第一条边权重]
第二条边起点 第二条边终点 [第二条边权重]
第三条边起点 第三条边终点 [第三条边权重]
如:
1 2
2 3
1 3 2
"></textarea>
			</div>
			<?php if (UOJConfig::$data['tools']['map-copy-enabled']): ?>
			<button type="button" class="btn btn-secondary" id="copy">复制图片</button>
			<?php endif ?>
		</div>
		<div class="col-md-9" id="paper">
		</div>
	</div>
</div>
<script>
	(function () {
		<?php if (UOJConfig::$data['tools']['map-copy-enabled']): ?>
		$("#copy").click(function () {
			if(navigator.permissions) {
				navigator.permissions.query({name: "clipboard-write"}).then(result => {
					if (result.state === "granted" || result.state === "prompt") {
						let paper = $("#paper")
						// First, we create an canvas and a img which has the same size with the canvas.
						let canvas = document.createElement('canvas');
						let s_img = document.createElement('img');
						s_img.height = paper.height()
						s_img.width = paper.width()
						canvas.height = paper.height()
						canvas.width = paper.width()
						// After this image is loaded, we draw it on our canvas
						s_img.onload = function () {
							// On some browsers, the exported png have a black background.
							// So we will draw a white background first.
							ctx = canvas.getContext('2d');
							ctx.beginPath();
							ctx.rect(0, 0, paper.width(), paper.height());
							ctx.fillStyle = "white";
							ctx.fill();
							// We put our image created from the svg to the canvas
							canvas.getContext('2d').drawImage(s_img, 0, 0);
							// Then we export canvas as a png file, paste it to clipboard.
							canvas.toBlob(function (blob) {
								const item = new ClipboardItem({[blob.type]: blob});
								navigator.clipboard.write([item]).then(function () {
									alert("图片已经复制到剪切板中");
								}, function (err) {
									alert(err);
								});
							});
						}
						// Load our svg to this image.
						s_img.src = "data:image/svg+xml;base64," + Base64.encode(new XMLSerializer().serializeToString(document.querySelector("#paper > svg")));
					} else {
						alert("获取剪切板权限失败, 请打开网页设置并授予剪切板权限!")
					}
				});
			} else {
				alert("获取剪切板权限失败, 请使用最新Chrome浏览器, 打开网页设置并授予剪切板权限!")
			}
		})
		<?php endif ?>
		let repaint = function () {
			let paper = $("#paper");
			// Clear existing elements.
			paper.html("");
			let directed = $('#directed').is(":checked");
			let Graph = Dracula.Graph;
			let Renderer = Dracula.Renderer.Raphael;
			let Layout = Dracula.Layout.Spring;
			let graph = new Graph();
			let render = function(r, n) {
				let color = Raphael.getColor();
				return r.set()
					.push(
						r.ellipse(0, 0, 30, 20).attr({stroke: color, "stroke-width": 2, fill: color, "fill-opacity": 0})
					)
					.push(r.text(0, 0, n.id).attr({ opacity: 1, 'font-size': 20,}));
			}
			$("#edges").val().split("\n").forEach(function (edge) {
				if (edge.split(" ").length >= 2) {
					if (edge.split(" ")[0].length !== 0 && edge.split(" ")[1].length !== 0 ){
						graph.addNode(edge.split(" ")[0],  {
							render: render
						});
						graph.addNode(edge.split(" ")[1],  {
							render: render
						});
						graph.addEdge(edge.split(" ")[0], edge.split(" ")[1], {
							directed: directed,
							label:edge.split(" ")[2],
							'label-style' : {
								'font-size': 20,
								"fill-opacity":"1"
							}
						});
					}
				}
			})
			var layout = new Layout(graph)
			var renderer = new Renderer('#paper', graph, paper.width(), paper.height())
			renderer.draw()
		}
		$("#edges").on("input", repaint)
		$("#directed").on("input", repaint)
	})()
</script>

<?php
echoUOJPageFooter();