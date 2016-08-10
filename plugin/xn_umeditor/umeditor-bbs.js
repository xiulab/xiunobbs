$(function() {
	
	// 先执行
	UM.plugins['xnimg'] = function() {
	};
	
	// 后执行
	UM.registerUI('xnimg', function( name ){
		var me = this;
		var $btn = $.eduibutton({
			icon: name,
			title: '上传图片',
			html: '<input type="file" value="" name="file" accept=".jpg,.jpeg,.png,.gif,.bmp"  multiple="multiple" />', 
			click: function(e) {
				//console.log(e);
			}
		});
		
		// 对图片进行缩略
		$btn.find('input[type="file"]').on('change', function(e) {
			var files = xn.get_files_from_event(e);
			if(!files) return;
			// 并发下会 服务端 session 写入会有问题，由客户端控制改为串行
			$.each_sync(files, function(i, callback) {
				var file = files[i];
				xn.upload_file(file, xn.url('attach-create'), {is_image: 1}, function(code, json) {
					if(code == 0) {
						var s = '<img src="'+json.url+'" width="'+json.width+'" height=\"'+json.height+'\" />';
						me.execCommand('inserthtml', s);
					} else {
						$.alert(json);
					}
					callback();
				});
			});
		});
	    
		me.addListener('selectionchange', function () {
			var state = this.queryCommandState(name);
			$btn.edui().disabled(state == -1).active(state == 1);
		});
	
		return $btn;
		
	
	});
	
	jform.find('[name="doctype"]').val(0);
	
	var um = UM.getEditor('message', {
	    lang:/^zh/.test(navigator.language || navigator.browserLanguage || navigator.userLanguage) ? 'zh-cn' : 'en',
	    langPath:UMEDITOR_CONFIG.UMEDITOR_HOME_URL + "lang/",
	    focus: true
	});
	
	um.ready(function() {
		
		// 如果浏览器不支持，直接返回。
		if(!window.FormData || !window.FileReader)  return;
		
		var me = this;
		var xn_upload_handler = function(e) {
			var files = e.type == 'paste' ? get_paste_image(e.originalEvent) : get_drop_image(e.originalEvent);
			if(!files) return;
			$.each_sync(files, function(i, callback) {
				var file = files[i];
				if(file.getAsFile) file = file.getAsFile();
				if(!file || file.size == 0 || file.type.indexOf('image') == -1) return;
				xn.upload_file(file, 'plugin/xn_umeditor/upload.php', {is_image: 1}, function(code, json) {
					if(code == 0) {
						var s = '<img src="'+json.url+'" width="'+json.width+'" height=\"'+json.height+'\" />';
						me.execCommand('inserthtml', s);
					} else {
						console.log(json);
					}
					callback();
				});
			});
		}
		function get_paste_image(e) {
			return e.clipboardData && e.clipboardData.items && e.clipboardData.items.length == 1 && /^image\//.test(e.clipboardData.items[0].type) ? e.clipboardData.items : null;
		}
		function get_drop_image(e) {
			return e.dataTransfer && e.dataTransfer.files ? e.dataTransfer.files : null;
		}
		me.getOpt('pasteImageEnabled') && me.$body.on('paste', xn_upload_handler);
		me.getOpt('dropFileEnabled') && me.$body.on('drop', xn_upload_handler);
		
		// 小屏幕下隐藏一些工具
		var jtoolbar = me.$container.find('.edui-btn-toolbar');
		jtoolbar.children().not('.edui-btn-xnimg').addClass('hidden-md-down');
		//jtoolbar.son('.edui-btn-name-fontfamily,.edui-btn-name-fontsize,.edui-splitbutton-forecolor,.edui-splitbutton-backcolor,.edui-btn-link,.edui-btn-unlink,.edui-btn-fullscreen').addClass('hidden-md-down');
	});

});
