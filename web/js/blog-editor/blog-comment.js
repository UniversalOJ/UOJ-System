var editor_js= "<script type=\"text/javascript\">uojHome = 'http://127.0.0.1'</script>\n" +
	"\n" +
	"<!-- Bootstrap core CSS -->\n" +
	"<link type=\"text/css\" rel=\"stylesheet\" href=\"http://127.0.0.1/css/bootstrap.min.css?v=2019.5.31\" />\t<!-- Bootstrap Glyphicons CSS-->\n" +
	"<link type=\"text/css\" rel=\"stylesheet\" href=\"http://127.0.0.1/css/bootstrap-glyphicons.min.css?v=2019.5.31\" />\n" +
	"<!-- Custom styles for this template -->\n" +
	"<link type=\"text/css\" rel=\"stylesheet\" href=\"http://127.0.0.1/css/uoj-theme.css?v=2.3333\" />\n" +
	"<!-- jQuery (necessary for Bootstrap\\'s JavaScript plugins) -->\n" +
	"<script src=\"http://127.0.0.1/js/jquery.min.js\"></script>\n" +
	"<!-- jQuery autosize -->\n" +
	"<script src=\"http://127.0.0.1/js/jquery.autosize.min.js\"></script>\t<script type=\"text/javascript\">\n" +
	"\t$(document).ready(function () {\n" +
	"\t\t$('textarea').autosize();\n" +
	"\t});\n" +
	"</script>\n" +
	"\n" +
	"<!-- jQuery cookie -->\n" +
	"<script src=\"http://127.0.0.1/js/jquery.cookie.min.js\"></script>\n" +
	"<!-- jQuery modal -->\n" +
	"<script src=\"http://127.0.0.1/js/jquery.modal.js\"></script>\n" +
	"\n" +
	"<!-- Include all compiled plugins (below), or include individual files as needed -->\n" +
	"<script src=\"http://127.0.0.1/js/popper.min.js?v=2019.5.31\"></script>\n" +
	"<script src=\"http://127.0.0.1/js/bootstrap.min.js?v=2019.5.31\"></script>\n" +
	"<!-- Color converter -->\n" +
	"<script src=\"http://127.0.0.1/js/color-converter.min.js\"></script>\n" +
	"<!-- uoj -->\n" +
	"<script src=\"http://127.0.0.1/js/uoj.js?v=2017.01.01\"></script>\n" +
	"<!-- readmore -->\n" +
	"<script src=\"http://127.0.0.1/js/readmore/readmore.min.js\"></script>\n" +
	"<!-- LAB -->\n" +
	"<script src=\"http://127.0.0.1/js/LAB.min.js\"></script>\t<!-- favicon -->\n" +
	"<link rel=\"shortcut icon\" href=\"http://127.0.0.1/images/favicon.ico\"/>\n" +
	"<!-- MathJax -->\n" +
	"<script type=\"text/x-mathjax-config\">\n" +
	"\t\t\tMathJax.Hub.Config({\n" +
	"\t\t\t\tshowProcessingMessages: false,\n" +
	"\t\t\t\ttex2jax: {\n" +
	"\t\t\t\t\tinlineMath: [[\"$\", \"$\"], [\"\\\\\\\\(\", \"\\\\\\\\)\"]],\n" +
	"\t\t\t\t\tprocessEscapes:true\n" +
	"\t\t\t\t},\n" +
	"\t\t\t\tmenuSettings: {\n" +
	"\t\t\t\t\tzoom: \"Hover\"\n" +
	"    \t\t\t}\n" +
	"\t\t\t});\n" +
	"\t\t</script>\n" +
	"<script src=\"//cdn.bootcss.com/mathjax/2.6.0/MathJax.js?config=TeX-AMS_HTML\"></script>\n" +
	"<!-- shjs -->\n" +
	"<link type=\"text/css\" rel=\"stylesheet\" href=\"http://127.0.0.1/css/sh_typical.min.css\" />\n" +
	"<script src=\"http://127.0.0.1/js/sh_main.min.js\"></script>\n" +
	"<script type=\"text/javascript\">\n" +
	"\t$(document).ready(function () {\n" +
	"\tsh_highlightDocument()\n" +
	"\t})\n" +
	"</script>\n" +
	"<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->\n" +
	"<!--[if lt IE 9]>\n" +
	"<script src=\"https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js\"></script>\n" +
	"<script src=\"https://oss.maxcdn.com/respond/1.4.2/respond.min.js\"></script>\n" +
	"<![endif]-->\n" +
	"\n" +
	"<script type=\"text/javascript\">\n" +
	"\tbefore_window_unload_message = null;\n" +
	"\t$(window).on('beforeunload', function () {\n" +
	"\t\tif (before_window_unload_message !== null) {\n" +
	"\t\t\treturn before_window_unload_message;\n" +
	"\t\t}\n" +
	"\t});\n" +
	"</script>";
function comment_editor_init(name,editor_config){
	if (editor_config === undefined) {
		editor_config = {};
	}
	editor_config = $.extend({
		type: 'blog'
	}, editor_config);
	var input_content_md = $("#input-" + name);
	var this_form = input_content_md[0].form;

	// init buttons
	var preview_btn = $('<button type="button" class="btn btn-secondary btn-sm"><span class="glyphicon glyphicon-eye-open"></span></button>');
	var bold_btn = $('<button type="button" class="btn btn-secondary btn-sm ml-2"><span class="glyphicon glyphicon-bold"></span></button>');
	var italic_btn = $('<button type="button" class="btn btn-secondary btn-sm"><span class="glyphicon glyphicon-italic"></span></button>');

	preview_btn.tooltip({ container: 'body', title: '预览 (Ctrl-D)' 	});
	bold_btn.tooltip({ container: 'body', title: '粗体 (Ctrl-B)' });
	italic_btn.tooltip({ container: 'body', title: '斜体 (Ctrl-I)' });

	var all_btn = [preview_btn, bold_btn, italic_btn];

	// init toolbar
	var toolbar = $('<div class="btn-toolbar"></div>');
	toolbar.append($('<div class="btn-group"></div>')
		.append(preview_btn)
	);
	toolbar.append($('<div class="btn-group"></div>')
		.append(bold_btn)
		.append(italic_btn)
	);
	function set_preview_status(status) {
		// 0: normal
		// 1: loading
		// 2: loaded
		if (status == 0) {
			preview_btn.removeClass('active');
			for (var i = 0; i < all_btn.length; i++) {
				if (all_btn[i] != preview_btn) {
					all_btn[i].prop('disabled', false);
				}
			}
		} else if (status == 1) {
			for (var i = 0; i < all_btn.length; i++) {
				if (all_btn[i] != preview_btn) {
					all_btn[i].prop('disabled', true);
				}
			}
			preview_btn.addClass('active');
		}
	}
	// init codemirror
	input_content_md.wrap('<div class="blog-content-md-editor"></div>');
	var blog_contend_md_editor = input_content_md.parent();
	input_content_md.before($('<div class="blog-content-md-editor-toolbar"></div>')
		.append(toolbar)
	);
	input_content_md.wrap('<div class="blog-comment-md-editor blog-content-md-editor-in"></div>');
	var codeeditor;
	if (editor_config.type == 'blog') {
		codeeditor = CodeMirror.fromTextArea(input_content_md[0], {
			mode: 'gfm',
			lineNumbers: true,
			matchBrackets: true,
			lineWrapping: true,
			styleActiveLine: true,
			theme: 'default'
		});
	} else if (editor_config.type == 'slide') {
		codeeditor = CodeMirror.fromTextArea(input_content_md[0], {
			mode: 'plain',
			lineNumbers: true,
			matchBrackets: true,
			lineWrapping: true,
			styleActiveLine: true,
			theme: 'default'
		});
	}

	function preview(html) {
		var iframe = $('<iframe frameborder="0"></iframe>');
		blog_contend_md_editor.append(
			$('<div class="blog-content-md-editor-preview" style="display: none;"></div>')
				.append(iframe)
		);
		var iframe_document = iframe[0].contentWindow.document;
		iframe_document.open();
		iframe_document.write(editor_js);
		iframe_document.write("<div class=\"container theme-showcase\" role=\"main\"><div class='uoj-content'><article>")
		iframe_document.write(html);
		iframe_document.write("</article></div></div>")
		iframe_document.close();
		$(iframe_document).bind('keydown', 'ctrl+d', function() {
			preview_btn.click();
			return false;
		});
		blog_contend_md_editor.find('.blog-content-md-editor-in').slideUp('fast');
		blog_contend_md_editor.find('.blog-content-md-editor-preview').slideDown('fast', function() {
			set_preview_status(2);
			iframe.focus();
			iframe.find('body').focus();
		});
	}
	function add_around(sl, sr) {
		codeeditor.replaceSelection(sl + codeeditor.getSelection() + sr);
	}
	// event
	codeeditor.on('change', function() {
		codeeditor.save();
	});
	preview_btn.click(function() {
		if (preview_btn.hasClass('active')) {
			set_preview_status(0);
			blog_contend_md_editor.find('.blog-content-md-editor-in').slideDown('fast');
			blog_contend_md_editor.find('.blog-content-md-editor-preview').slideUp('fast', function() {
				$(this).remove();
			});
			codeeditor.focus();
		} else {
			// save({need_preview: true});
			var post_data = {};
			$($(this_form).serializeArray()).each(function() {
				post_data[this["name"]] = this["value"];
			});
			console.log(post_data);
			marked.setOptions({
				pedantic: false,
				gfm: true,
				tables: true,
				breaks: false,
				sanitize: false,
				smartLists: true,
				smartypants: false,
				xhtml: false
			});
			console.log(marked(post_data['comment']));
			preview(marked(post_data['comment']));
			$(document).ready(function () {
				sh_highlightDocument()
			});
			set_preview_status(1);
		}
	});
	bold_btn.click(function() {
		add_around("**", "**");
		codeeditor.focus();
	});
	italic_btn.click(function() {
		add_around("*", "*");
		codeeditor.focus();
	});

	// init hot keys
	codeeditor.setOption("extraKeys", {
		"Ctrl-B": function(cm) {
			bold_btn.click();
		},
		"Ctrl-D": function(cm) {
			preview_btn.click();
		},
		"Ctrl-I": function(cm) {
			italic_btn.click();
		}
	});
	$(document).bind('keydown', 'ctrl+d', function() {
		preview_btn.click();
		return false;
	});
	if (this_form) {
		$(this_form).submit(function() {
			before_window_unload_message = null;
		});
	}
}