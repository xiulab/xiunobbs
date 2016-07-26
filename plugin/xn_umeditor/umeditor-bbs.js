$(function() {
	function xn_upload_image(file, callback) {
		var reader = new FileReader();
	        reader.readAsDataURL(file);
	        reader.onload = function() {
	        	if(xn.substr(this.result, 0, 10) != 'data:image') return;
	        	var width = 400;
	        	var height = 300;
	        	var action = 'thumb';
	        	var filename = file.name ? file.name : (file.type == 'image/png' ? 'capture.png' : 'capture.jpg');
	        	xn.image_resize(this.result, width, height, action, function(code, message) {
	        		if(code != 0) return alert(message);
	        		var thumb_width = message.width;
	        		var thumb_height = message.height;
	        		var postdata = {width: thumb_width, height: thumb_height, name: filename, data: message.data};
	        		$.xpost('plugin/xn_umeditor/upload.php', postdata, function(code, message) {
	        			if(code != 0) return alert(message);
	        			var s = '<img src="'+message.url+'" width="'+thumb_width+'" height=\"'+thumb_height+'\" />';
		        		if(callback) callback(s);
	        		});
	        	});
	        }
	}
	
	// 先执行
	UM.plugins['xnimg'] = function() {
	};
	
	// 后执行
	UM.registerUI('xnimg', function( name ){
		var me = this;
		var $btn = $.eduibutton({
			icon: name,
			title: '上传图片',
			html: '<input type="file" value="" name="file" accept=".jpg,.jpeg,.png,.gif,.bmp"  />', 
			click: function(e) {
				console.log(e);
			}
		});
		
		// 对图片进行缩略
		$btn.find('input[type="file"]').on('change', function(e) {
			xn_upload_image(e.target.files[0], function(s) {
				me.execCommand('inserthtml', s);
			});
		});
	    
		me.addListener('selectionchange', function () {
			var state = this.queryCommandState(name);
			$btn.edui().disabled(state == -1).active(state == 1);
		});
	
		return $btn;
		
	
	});
	
	// 代码高亮插件
	
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
			var items = e.type == 'paste' ? get_paste_image(e.originalEvent) : get_drop_image(e.originalEvent);
			if(!items) return;
			for(var i=0; i<items.length; i++) {
				var file = items[i];
				if(file.getAsFile) file = file.getAsFile();
				if(file && file.size > 0 && /image\/\w+/i.test(file.type)) {
					// sendAndInsertImage(file, me);
					xn_upload_image(file, function(s) {
						me.execCommand('inserthtml', s);
					});
	                        }
			}
			console.log(e);
		}
		function get_paste_image(e) {
			return e.clipboardData && e.clipboardData.items && e.clipboardData.items.length == 1 && /^image\//.test(e.clipboardData.items[0].type) ? e.clipboardData.items : null;
		}
		function get_drop_image(e) {
			return e.dataTransfer && e.dataTransfer.files ? e.dataTransfer.files : null;
		}
		me.getOpt('pasteImageEnabled') && me.$body.on('paste', xn_upload_handler);
		me.getOpt('dropFileEnabled') && me.$body.on('drop', xn_upload_handler);
		
	});

});
