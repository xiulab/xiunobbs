/*
* Copyright (C) 2015 xiuno.com
*/

/*
	参考 jquery 1.4 对话框参数设计
	用法：
		<div class="dialog bg2 border shadow" title="对话框标题" id="dialog1" style="display: none;">文字HTML等内容...</div>
		<script>
		// 弹出指定的 div
		$('#id1').dialog();
		$('#id1').dialog('open');
		$('#id1').dialog('close');
		$('#id1').dialog({ modal: true });
		$('#id1').dialog({ title: 'Dialog Title' });
		$('#id1').dialog({ width: 460});

		// 模仿三种标准对话框
		$.box('用户登录用户登录用户登录');
		$.box('用户登录用户登录用户登录', 'ok');
		$.box('用户登录用户登录用户登录', 'error');
		$.box('用户登录用户登录用户登录', 'error', function() {alert('closed');});
		$.alert('用户登录用户登录用户登录');
		$.alert('用户登录用户登录用户登录', 'ok');
		$.alert('用户登录用户登录用户登录', 'error');
		$.alert('用户登录用户登录用户登录', 'error', function() {alert('closed');});
		$.confirm('用户登录用户登录用户登录');
		$.confirm('用户登录用户登录用户登录', function() {alert('ok');}, function() {alert('cancel');});
		
		// ajax dialog
		$.ajax_dialog('http://www.xxx.com/', true, {}, function() {alert(123);});
		</script>
		
	// todo: ajax_dialog css link 未动态加载！

*/

// 兼容 jquery 自带的 dialog

$.fn.dialog = function(settings) {
	if(!settings) {
		settings = {};
	} else if(settings == 'open') {
		settings = {open: true};
	} else if(settings == 'close') {
		settings = {open: false};
	} else if(settings == 'destory') {
		settings = {open: false, closedestory: true};
	}

	// 此处 this 为 jquery 集合
	this.each(function() {
		// 此处 this 为 <div> 元素
		if(this.dialog) {
			var oldbody = settings ? settings.body : '';
			//settings = $.extend(this.dialog.settings, settings);
			if(oldbody == '') settings.body = '';
			if(settings.position === 0) settings.position = this.dialog.settings.position;
			this.dialog.settings = $.extend(this.dialog.settings, settings);
			this.dialog.set(settings);
		} else {
			// 如果 dialog 已经不存在了，则啥也不干，不要再初始化了。
			if(settings.open === false && settings.closedestory === true) return;
			
			settings = $.extend({
				width: settings.width ? settings.width : $(this).width(),
				height: 'auto',
				modal: true,
				open: true,
				closedestory: false,
				drag: true,
				position: 'center',
				fullscreen: false,
				timeout: 0,
				showtitle: true,
				title: '',
				body: '',
				zindex: 100,
				onclose: null,
				pos_element: null
			}, settings);

		 	this.dialog = new $.dialog(this, settings);
		 	//$(this).dialog = this.dialog;
		 	this.dialog.init();
		 	this.dialog.set(settings);
		}
	});
	return this;
};

// 这种写法并不是最正宗的 OP 的写法，会在每次 new 的时候对 this 进行扩展，很耗费资源！最省资源的方法参看 xneditor.js
$.joverlay = null;
$.dialog = function(div, settings) {
	var _this = this;	// this pointer
	var jdiv = $(div);
	if(!div.id) div.id = Math.random();
	this.settings = settings;
	this.width = settings.width;
	this.height = settings.height;
	this.last_height = settings.height;
	
	var window_width = $(window).width();		
	var document_height = $(document).height();
	
	// 内容改变，调整高度
	/*function dialog_dom_change() {
		return;
		// 如果高度发生改变，则移动位置
		var jdiv_height = jdiv.height();
		if(jdiv_height && jdiv_height != this.last_height) {
			//console.log("DOM change, this.last_height: %d, jdiv.height(): %d", this.last_height, jdiv.height());
			_this.set_position(0);
			this.last_height = jdiv_height;
		}
	}
	jdiv.off('DOMSubtreeModified').on('DOMSubtreeModified', dialog_dom_change);*/
	
	// 创建对话框, 初始化对话框, 但是不显示
	this.init = function() {

		// 构造对话框
		$.joverlay = $('#dialog_overlay');
		if($.joverlay.length == 0) $.joverlay = $('<div class="dialog-overlay" id="dialog_overlay" ref="0" unselectable="on" onselect="return false;"></div>').hide().appendTo('body');
		$.joverlay.height($(window).height()).keydown(function(e) {e.stopPropagation();}).keyup(function(e) {e.stopPropagation();});
		
		var html = jdiv.html();
		var divcontent = '<div class="header"'+(settings.showtitle ? '' : ' style="display: none;"')+'><a href="javascript: void(0)" class="icon close" style="float:right; margin-right: 0px; margin-top: 0px;" title="关闭"></a><a href="javascript: void(0)" class="icon icon-max" style="float:right; margin-right: 4px; margin-top: 2px; display: none;" title="最大/小化"></a><span>' + div.title + '</span></div>' + '<div class="body"></div>';// ' + jdiv.html() + '
		var jcontent = $(divcontent).appendTo('body');
		var jchildren = jdiv.contents();
		var jbody = jcontent[1];
		jchildren.appendTo(jbody);
		jcontent.appendTo(jdiv);
		
		// 按照参数进行设置
		this.set(_this.settings);

		// 层拖动效果 -------------> start
		var title = jcontent[0];
		var jtitle = $(title);
		jtitle.css('cursor', 'move');
		function title_mousemove(e) {
			if(title.startdrag) {
				var x = e.pageX - title.mouse_offset_x;
				var y = e.pageY - title.mouse_offset_y;
				// 判断超出页面
				if(_this.width + x > window_width) x = window_width - _this.width;
				if(_this.height + y > document_height) y = document_height - _this.height;
				if(x < 0) x = 0;
				if(y < 0) y = 0;
				jdiv.css({ left: x, top: y});
			}
		}
		function title_mouseup() {
			$(document).off('mousemove'+'.dialog_'+div.id);		// 比较耗费资源，用完 unbind 掉。
			$(document).off('mouseup'+'.dialog_'+div.id);			// 比较耗费资源，用完 unbind 掉。
			title.startdrag = 0;

			document.unselectable = 'off';
			$('body').removeClass('unselect');
			//document.body.onselectstart = function() {return true;};
		}
		function title_mousedown(e) {
			if(_this.settings.drag) {
				title.startdrag = 1;
				document.unselectable = 'on';
				$('body').addClass('unselect');
				//document.body.onselectstart = function() {return false;};
			} else {
				title.startdrag = 0;
				return false;
			}
			title.mouse_offset_x = e.pageX - jdiv[0].offsetLeft; // IE 不支持：jdiv.attr('offsetLeft');
			title.mouse_offset_y = e.pageY - jdiv[0].offsetTop;

			// 保存 <body> style overflow 属性，设置为 overflow: hidden;
			//$('body').css('overflow', 'hidden');
			$(document).on('mousemove'+'.dialog_'+div.id, function(e) {title_mousemove(e)});
			$(document).on('mouseup'+'.dialog_'+div.id, function(e) {title_mouseup(e)});
		}
		jtitle.on('mousedown', function(e) {title_mousedown(e)});

		// ----------------> end

		// jquery: $('div.header:first a.icon-max', jdiv).
		var jdiv_header = jdiv.children('div.header');
		if(jdiv_header.length > 0) {
			jdiv_header.eq(0).find('a.close').click(function() {
				if(!_this.settings.onclose || _this.settings.onclose && _this.settings.onclose() !== false) {
					_this.close();
				}
			});
			jdiv_header.eq(0).find('a.icon-max').click(function() {
				// 保存当前状态
				_this.set_fullscreen($(this).hasClass('icon-max'));
				$(this).toggleClass('icon-max');
				$(this).toggleClass('icon-min');
			});
		}

		// 点击层时，调整当前层的z-index。
		/*
		jdiv.on('mousedown', function() {
			_this.set_top(this); // 查找所有层中的最大值，最大值 +1
			return true;
		});
		*/
		return true;
	};

	this.set = function(settings) {
//		if(merge) _this.settings = $.extend(_this.settings, settings);
		
		if(settings.title)  this.set_title(settings.title);
		if(settings.body)  this.set_body(settings.body);
		
		if(settings.width)  this.set_width(settings.width);
		if(settings.height)  this.set_height(settings.height);
		
		if(isset(settings.modal))  _this.settings.modal = settings.modal;
		if(settings.position)  _this.set_position(settings.position);
		if(settings.timeout)  this.set_timeout();
		if(settings.zindex)  this.set_zindex(settings.zindex);
		if(settings.open === true)  this.open();
		if(settings.open === false)  this.close();
		
	};

	// 打开对话框
	this.open = function() {
		// 清除上面的定时器
		if(div.htime) {
			clearTimeout(div.htime);
			div.htime = null;
		}
		// 已经打开
		if(jdiv.css('display') != 'none') {
			return;
		}
		if(_this.settings.modal) {
			var layref = parseInt($.joverlay.attr('ref')) + 1;
			$.joverlay.width('100%').height($(document).height()).show().attr('ref', layref);
		} else {
			//$('#overlay').width(0).hide();
		}
		//jdiv.on('DOMSubtreeModified', dialog_dom_change);

		// 设置位置，添加动画效果。
		
		this.set_zindex();
		jdiv.show();
		
		//jdiv.fadeIn('middle');
		//_this.set_top(div);
	};

	// destory 是否销毁对话框，还是隐藏
	this.close = function(destory) {
		if(_this.settings.modal) {
			// 如果已经关闭，不要再重复
			if(jdiv && jdiv.css('display') != 'none') {
				var layref = parseInt($.joverlay.attr('ref'));
				$.joverlay.attr('ref', Math.max(0, --layref));
				if(layref < 1) $.joverlay.width(0).hide();
			}
		}
		jdiv.off('DOMSubtreeModified');
		if(destory || settings.closedestory) {
			div.dialog = null;
			$(document).off('.dialog_'+div.id); // 这里需要释放掉，否则会有内存泄露
			// 删除缓存 ?
			if(jdiv) {
				jdiv.removeDeep();
				jdiv = null;
			}
			//this = null; // 交给 js 引擎释放掉对象。
			
			// todo: 干净的释放资源应该遍历 $.ajax_dialog_url, 删除其中元素。
		} else {
			//jdiv.fadeOut('slow');
			jdiv.hide();
		}
		
	};

	/*
		X: 为点击对象， 1 - 9 表示它的周围的位置，再加一个 center, 默认屏幕居中。

		1	2	3
		4	XXX	6
		7	8	9

	*/
	this.set_position = function(position) {
		// <a> 标签所在位置，决定了弹出层的位置，如果 options.position != 'center'
		if(!_this.settings.pos_element) position = 'center';
		
		// 如果需要居中，将 dialog div 移动到 <body> 下
		if(position == 'center') {
			jdiv.appendTo(document.body); // 此处可能会有问题。编辑器里面的控件也会移动到 form 外面。
		}
		var offset = $.link_div_position($(_this.settings.pos_element), jdiv, position);
		if(!offset) return;
		jdiv.css(offset);
	};

	this.set_width = function(width) {
		jdiv.width(width);
		_this.width = jdiv.width();
		//var jcontent = $('div.body', div);
		//var subpadding = parseInt(jcontent.css('padding-left')) + parseInt(jcontent.css('padding-right')) + parseInt(jcontent.css('margin-left')) + parseInt(jcontent.css('margin-right'));
		//jcontent.width(width - subpadding); // jdiv.width() 转换为绝对宽度，ie6 会有问题。ie6取出来的宽度为未缩小的宽度，也就是实际撑开的宽度。需要设置 overflow: ?
	};

	this.set_height = function(height) {
		jdiv.height(height);
		_this.height = jdiv.height();
	};

	this.set_title = function(title) {
		jdiv.children('div.header').eq(0).children('span').html(title);
	};

	this.set_body = function(s) {
		try {
			if(typeof s == 'string') {
				$('div.body', div).html(s);
			} else if(is_element(s)){
				$('div.body', div).replaceWith(s);
			}
			setTimeout(function() {
				_this.set_position(_this.settings.position);
			}, 10);
		} catch(e) {
			alert('dialog.set_body() error: ' + e.message + "\nbody:" + s);
		}
	};
	
	this.get_body = function() {
		return $('div.body', div).html();
	}

	// 设置 div 为顶层的 div
	/*
	this.set_top = function(div) {
		var maxzindex = 1;
		$('div.dialog').each(function() {
			if($(this).not(div).css('z-index') >= maxzindex) maxzindex = $(this).css('z-index') + 1;
		});
		if(maxzindex > jdiv.css('z-index')) {
			jdiv.css('z-index', maxzindex);
		}
	};
	*/

	this.set_zindex = function(zindex) {
		// 查找当前打开的 dialog，设置为最大值
		var maxzindex = 1;
		var currindex = jdiv.css('z-index');
		if(!zindex) {
			var zindex = currindex;
			$('div.dialog').each(function() {
				if($(this).not(div).css('z-index') >= maxzindex) maxzindex = $(this).css('z-index') + 1;
			});
			if(maxzindex > currindex) {
				zindex = maxzindex + 1;
			}
		}
		jdiv.css('z-index', zindex);
	}

	this.set_timeout = function() {
		if(_this.settings.timeout) {
			jdiv.on('mouseover', function() {
				if(div.htime) {
					clearTimeout(div.htime);
					div.htime = null;
				}
				return true;
			});
			jdiv.on('mouseout', function() {
				if(!div.htime) {
					div.htime = setTimeout(function() {
						_this.close();
						div.htime = null;
					}, _this.settings.timeout);
				}
				return true;
			});
		}
	};

	// 关闭其他 dialog
	this.close_other = function() {
		$('div.dialog').not(div).dialog('close');
		
	};

	// ESC 关闭
	function document_key_esc(e) {
		//e = e || document.parentWindow.event;
		var e = e ? e : window.event;
		var kc = e.keyCode ? e.keyCode : e.charCode;
		if(kc == 27) {
			_this.close();
			//$(document).off('keyup', document_key_esc);
		}
		return true;
	}

	$(document).on('keyup.dialog_'+div.id, document_key_esc);

	return this;
};

// 仅有一个实例，默认为空
// icon: ok|error|notice
$.box = function(s, icon, onclose) {
	//$('#dialog_box').dialog('close');
	
	if(!icon) icon = 'info-circle yellow';
	if(icon == 'ok') icon = 'check-circle green';
	if(icon == 'error') icon = 'times-circle red';
	s = '<p class="center body">' + (icon ? '<span class="icon '+icon+'"></span>' : '') + s + '</p>';;
	if(!document.getElementById('dialog_box')) $('<div class="dialog border shadow" title="提示信息" id="dialog_box" style="display: none;"></div>').appendTo('body');
	var jdialog_box = $('#dialog_box');
	if(s == 'close') {
		if(!onclose || onclose && onclose() !== false) {
			jdialog_box.dialog('close');
		}
	} else {
		jdialog_box.dialog({open: true, body: s, onclose: onclose});
	}
	return jdialog_box;
}

// 仅有一个实例
// icon: ok|error|notice
$.alert = function(s, icon, onclose) {
	//$('#dialog_box').dialog('close');
	
	if(!icon) icon = 'info-circle yellow';
	if(icon == 'ok') icon = 'check-circle green';
	if(icon == 'error') icon = 'times-circle red';
	s = '<p class="center body">' + (icon ? '<span class="icon '+icon+'"></span>' : '') + s + '</p><p class="center"><button type="button" class="button blue" id="dialog_ok">确定</button></p>';
	if(!document.getElementById('dialog_box')) $('<div class="dialog border shadow" title="提示信息" id="dialog_box" style="display: none;"></div>').appendTo('body');
	var jdialog_box = $('#dialog_box');
	jdialog_box.dialog({open: true, body: s, onclose: onclose});
	var dialog_ok = $('#dialog_ok');
	dialog_ok.off('click').on('click', function() {
		if(!onclose || onclose && onclose() !== false) {
			$('#dialog_box').dialog('close');
		}
	});
	return jdialog_box;
}

// 仅有一个实例
$.confirm = function(s, onok, onclose) {
	var s = '<p class="center body"><span class="icon question-circle yellow"></span>'+s+'</p><p class="center"><button type="button" class="button blue" id="dialog_ok">确定</button> <button type="button" class="button grey"  id="dialog_cancel">取消</button></p>';
	if(!document.getElementById('dialog_box')) $('<div class="dialog border shadow" title="提示信息" id="dialog_box" style="display: none;"></div>').appendTo('body');
	var jdialog_box = $('#dialog_box');
	jdialog_box.dialog({open: true, body: s, onclose: onclose});
	var jdialog_ok = $('#dialog_ok');
	var jdialog_cancel = $('#dialog_cancel');
	jdialog_ok.off('click').on('click', function() {
		if(!onok || onok && onok() !== false) {
			$('#dialog_box').dialog('close');
		}
	});
	jdialog_cancel.off('click').on('click', function() {
		if(!onclose || onclose && onclose() !== false) {
			$('#dialog_box').dialog('close');
		}
	});
	return jdialog_box;
}

// ajax 获取 url 数据在层中显示内容，居中，模态
// callback 基本很少用到
$.ajax_dialog_url = {};
$.ajax_dialog = function(url, cache, settings) {
	if(cache === undefined) cache = true;
	if(!settings) settings = {};
	var s = '<div style="text-align: center;"><div class="loading"><img src="static/loading.gif" /></div></div> <p class="center"><button type="button" class="button grey cancel">取消</button></p>';
	
	// 关闭相同的前缀的 dialog
	$.ajax_dialog_remove_pre_url(url);
	
	var jdialog = $.ajax_dialog_url[url];
	if(jdialog && cache && jdialog.cache) {
		if(settings.pos_element && settings.pos_element !== jdialog.pos_element) {
			jdialog.dialog({open: true, position: settings.position, pos_element: settings.pos_element});
		} else {
			jdialog.dialog({open: true});
		}
		if(jdialog.script_sections) eval_script(jdialog.script_sections, url, jdialog);
	} else {
		
		// 清理掉内容，否则会导致ID冲突。
		if(jdialog) jdialog.removeDeep();
		
		// 重新 new 一个 dialog
		var jdialog = $('<div class="dialog border shadow" title="提示信息" style="width: 400px;"></div>').appendTo('body');
		$.ajax_dialog_url[url] = jdialog;
	
		settings.open = true;
		settings.title = '提示信息';
		settings.body = s;
		settings.onclose = null;
		settings.closedestory = (cache ? false : true);
		jdialog.dialog(settings);
		jdialog.attr("url", url); // 用来调试
		jdialog.find('button.cancel').on('click', function() {
			jdialog.dialog('close');
		});
		$.xget(url, function(code, message) {
			// 普通业务逻辑错误
			if(code > 0) {
				$.alert(message);
				jdialog.dialog('close');
				return;
			}
			// == 0 或者 < 0 都有可能是返回的正确的 dialog 数据
			var r = null;
			if(code == 0) {
				if(typeof message == 'object') {
					if(!message.body) return; // 格式不对
					r = get_title_body_script_css(message.body);
					r.title = message.title;
				} else {
					r = get_title_body_script_css(message);
				}
			}  else if(code < 0) {
				if(message.indexOf('<body') != -1) {
					r = get_title_body_script_css(message);
				} else {
					//var body = '<p class="center body"><span class="icon yellow"></span>' + message + '</p><p class="center"><button type="button" class="button blue" id="dialog_ok">确定</button></p>';
					//jdialog.dialog({body: body, title: '错误信息', width:'auto'});
					jdialog.dialog('close');
					$.alert(message);
					jdialog.cache = false;
					return;
				}
			}
			if(!r) return;
			eval_stylesheet(r.stylesheet_links);
			jdialog.script_sections = r.script_sections;
			
			settings.title = r.title;
			settings.body = r.body;
			settings.width = 'auto';
			jdialog.dialog(settings);
			
			if(r.script_srcs.length > 0) {
				$.require(r.script_srcs, function() { 
					eval_script(r.script_sections, url, jdialog);
				});
			} else {
				eval_script(r.script_sections, url, jdialog);
			}
			jdialog.cache = true;
		});
	}
	if(settings.pos_element) jdialog.pos_element = settings.pos_element;
	window.jdialog = jdialog;
	return jdialog;
}
$.ajax_dialog_close = function(url, jdialog) {
	if(!$.ajax_dialog_url[url]) return;
	var jpdialog = $.ajax_dialog_url[url]; // 需要关闭的上一个 dialog
	if(jpdialog) jpdialog.dialog('close');
	jdialog[0].dialog.settings.onclose = jpdialog[0].dialog.settings.onclose;
}
// 移除相同前缀 url 的 dialog，释放资源，防止 id 冲突。
$.ajax_dialog_remove_pre_url = function(url) {
	url = url + '';
	var pre = url.indexOf('-', url.indexOf('-') + 1);
	if(pre == -1) return;
	pre = substr(url, 0, pre + 1);
	for(k in $.ajax_dialog_url) {
		if(k == url) continue;
		if(k.indexOf(pre) != -1) {
			var jdialog = $.ajax_dialog_url[k];
			jdialog.dialog('destory');
			jdialog = null;
			delete $.ajax_dialog_url[k];
		}
	}
}

// evalscript 并且传递参数
/*
function dialog_eval_script(jdialog, arr) {
	if(!arr) return;
	jdialog.scripts_functions = [];
	for(var i=0; i<arr.length; i++) {
		var s = arr[i].replace(/<script(\s*type="text\/javascript")?\s*>([\s\S]+?)<\/script>/i, '$2');
		try {
			if(jdialog.scripts_functions[i]) {
				func = jdialog.scripts_functions[i];
			} else {
				var func = new Function('jdialog', s);
				jdialog.scripts_functions[i] = func;
			}
			//func(jdialog);
			func.call(window, jdialog);
		} catch(e) {
			console.log("dialog_eval_script() error: %s, script: %s", e, s);
		}
	}
	//var func2 = new Function('window.on_user_login = function() {alert(\'xx 9999\');}');
	//func2();
}

*/
