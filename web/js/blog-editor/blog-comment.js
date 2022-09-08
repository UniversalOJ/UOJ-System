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

	function get_comment_preview_url(url){
		let strings = url.split("/");
		let new_url = "";
		for(let i = 0; i < strings.length - 2; i++){
			new_url+=strings[i];
			new_url+="/";
		}
		new_url+="comment/preview";
		return new_url;
	}
	function preview(html) {
		var iframe = $('<iframe frameborder="0"></iframe>');
		blog_contend_md_editor.append(
			$('<div class="blog-content-md-editor-preview" style="display: none;"></div>')
				.append(iframe)
		);
		var iframe_document = iframe[0].contentWindow.document;
		iframe_document.open();
		iframe_document.write(html);
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
			var post_data = {};
			$($(this_form).serializeArray()).each(function() {
				post_data[this["name"]] = this["value"];
			});
			$.ajax({
				type : 'POST',
				data : post_data,
				url : get_comment_preview_url(window.location.href),
				success : function(data) {
					try {
						data = JSON.parse(data)
					} catch (e) {
						set_preview_status(0);
						return;
					}
					var ok = true;
					if (!ok) {
						set_preview_status(0);
						return;
					}
					preview(data['html']);
				}
			}).fail(function() {
				set_preview_status(0);
			}).always(function() {
				last_save_done = true;
			});
			// save({need_preview: true});
			// var post_data = {};
			// $($(this_form).serializeArray()).each(function() {
			// 	post_data[this["name"]] = this["value"];
			// });
			// console.log(post_data);
			// marked.setOptions({
			// 	pedantic: false,
			// 	gfm: true,
			// 	tables: true,
			// 	breaks: false,
			// 	sanitize: false,
			// 	smartLists: true,
			// 	smartypants: false,
			// 	xhtml: false
			// });
			// console.log(marked(post_data['comment']));
			// preview(marked(post_data['comment']));
			// $(document).ready(function () {
			// 	sh_highlightDocument()
			// });
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