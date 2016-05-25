/*
 * Copyright (C) 2015 xiuno.com
 * 依赖 xiunoui
 用法：
     var editor = new $.XNEditor({textarea:'textarea1'});
     editor.clear();
 */

var file_type_map = {
	video:["av","wmv","wav","wma","avi","rm","rmvb","mp4"],
	music:["mp3","mp4"],
	exe:["exe","bin"],
	flash:["swf","fla","as"],
	image:["gif","jpg","jpeg","png","bmp"],
	office:["doc","xls","ppt"],
	pdf:["pdf"],
	text:["c","cpp","cc", "txt"],
	zip:["tar","zip","gz","rar","7z","bz"],
	book:["chm"],
	torrent:["bt","torrent"],
	font:["ttf","font","fon"]
};

var file_type = function(suffix) {
	for(k in file_type_map) {
		if($.inArray(suffix, file_type_map[k]) != -1) return k;
	}
	return 'unknown';
}

var file_suffix = function(filename) {
	var i = filename.lastIndexOf(".");  
	var len = filename.length;
	var suffix = filename.substring(i+1, len);
	return suffix; 
}

var color_map = {lightpink:"#FFB6C1", pink:"#FFC0CB", crimson:"#DC143C", lavenderblush:"#FFF0F5", palevioletred:"#DB7093", hotpink:"#FF69B4", deeppink:"#FF1493", mediumvioletred:"#C71585", orchid:"#DA70D6", thistle:"#D8BFD8", plum:"#DDA0DD", violet:"#EE82EE", magenta:"#FF00FF", fuchsia:"#FF00FF", darkmagenta:"#8B008B", purple:"#800080", mediumorchid:"#BA55D3", darkviolet:"#9400D3", darkorchid:"#9932CC", indigo:"#4B0082", blueviolet:"#8A2BE2", mediumpurple:"#9370DB", mediumslateblue:"#7B68EE", slateblue:"#6A5ACD", darkslateblue:"#483D8B", lavender:"#E6E6FA", ghostwhite:"#F8F8FF", blue:"#0000FF", mediumblue:"#0000CD", midnightblue:"#191970", darkblue:"#00008B", navy:"#000080", royalblue:"#4169E1", cornflowerblue:"#6495ED", lightsteelblue:"#B0C4DE", lightslategray:"#778899", slategray:"#708090", dodgerblue:"#1E90FF", aliceblue:"#F0F8FF", steelblue:"#4682B4", lightskyblue:"#87CEFA", skyblue:"#87CEEB", deepskyblue:"#00BFFF", lightblue:"#ADD8E6", powderblue:"#B0E0E6", cadetblue:"#5F9EA0", azure:"#F0FFFF", lightcyan:"#E0FFFF", paleturquoise:"#AFEEEE", cyan:"#00FFFF", aqua:"#00FFFF", darkturquoise:"#00CED1", darkslategray:"#2F4F4F", darkcyan:"#008B8B", teal:"#008080", mediumturquoise:"#48D1CC", lightseagreen:"#20B2AA", turquoise:"#40E0D0", aquamarine:"#7FFFD4", mediumaquamarine:"#66CDAA", mediumspringgreen:"#00FA9A", mintcream:"#F5FFFA", springgreen:"#00FF7F", mediumseagreen:"#3CB371", seagreen:"#2E8B57", honeydew:"#F0FFF0", lightgreen:"#90EE90", palegreen:"#98FB98", darkseagreen:"#8FBC8F", limegreen:"#32CD32", lime:"#00FF00", forestgreen:"#228B22", green:"#008000", darkgreen:"#006400", chartreuse:"#7FFF00", lawngreen:"#7CFC00", greenyellow:"#ADFF2F", darkolivegreen:"#556B2F", yellowgreen:"#9ACD32", olivedrab:"#6B8E23", beige:"#F5F5DC", lightgoldenrodyellow:"#FAFAD2", ivory:"#FFFFF0", lightyellow:"#FFFFE0", yellow:"#FFFF00", olive:"#808000", darkkhaki:"#BDB76B", lemonchiffon:"#FFFACD", palegoldenrod:"#EEE8AA", khaki:"#F0E68C", gold:"#FFD700", cornsilk:"#FFF8DC", goldenrod:"#DAA520", darkgoldenrod:"#B8860B", floralwhite:"#FFFAF0", oldlace:"#FDF5E6", wheat:"#F5DEB3", moccasin:"#FFE4B5", orange:"#FFA500", papayawhip:"#FFEFD5", blanchedalmond:"#FFEBCD", navajowhite:"#FFDEAD", antiquewhite:"#FAEBD7", tan:"#D2B48C", burlywood:"#DEB887", bisque:"#FFE4C4", darkorange:"#FF8C00", linen:"#FAF0E6", peru:"#CD853F", peachpuff:"#FFDAB9", sandybrown:"#F4A460", chocolate:"#D2691E", saddlebrown:"#8B4513", seashell:"#FFF5EE", sienna:"#A0522D", lightsalmon:"#FFA07A", coral:"#FF7F50", orangered:"#FF4500", darksalmon:"#E9967A", tomato:"#FF6347", mistyrose:"#FFE4E1", salmon:"#FA8072", snow:"#FFFAFA", lightcoral:"#F08080", rosybrown:"#BC8F8F", indianred:"#CD5C5C", red:"#FF0000", brown:"#A52A2A", firebrick:"#B22222", darkred:"#8B0000", maroon:"#800000", white:"#FFFFFF", whitesmoke:"#F5F5F5", gainsboro:"#DCDCDC", lightgrey:"#D3D3D3", silver:"#C0C0C0", darkgray:"#A9A9A9", gray:"#808080", dimgray:"#696969", black:"#000000"};

var color_format = function(value) {
	value = value + '';
	if(value.indexOf('#') != -1) {
		return value;
	} else if(value.indexOf('rgb(') != -1 || value.indexOf('rgba(') != -1) {
		var matches = value.match(/^rgb\s*\(([0-9]+),\s*([0-9]+),\s*([0-9]+)\)$/);
		if(matches) {
			var hex = (matches[1] < 16 ? '0' : '') + parseFloat(matches[1]).toString(16) + (matches[2] < 16 ? '0' : '') + parseFloat(matches[2]).toString(16) + (matches[3] < 16 ? '0' : '') + parseFloat(matches[3]).toString(16);
			return '#'+hex.toUpperCase();
		} else {
			return '#FFFFFF';
		}
	} else {
		var v = value.toLowerCase();
		return color_map[v] ? color_map[v] : value;
	}
}

var font_size_format = function(value) {
	var r = '';
	switch(intval(value)) {
		case 1: r = '12px'; break;
		case 2: r = '13px'; break;
		case 3: r = '16px'; break;
		case 4: r = '18px'; break;
		case 5: r = '24px'; break;
		case 6: r = '32px'; break;
		case 7: r = '48px'; break;
	}
	return r;
}

// nodeType==1时才是元素节点，2是属性节点，3是文本节点。
var node_is_empty = function(node) {
	if(!node || !node.childNodes) return true;
	for(var i=0; i<node.childNodes.length; i++) {
		var n = node.childNodes[i];
		if(n.nodeType == 1) {
			if(n.innerText != '') return false;
		} else if(n.nodeType == 3) {
			if(n.innerText != '') return false;
		}
	}
	return true;
}

// 将 b i u strong em font 相关的标签转换为 span 标签 + style
var font_tag_to_span = function(s) {
	s = s.replace(/<strong]*>/ig, '<b>');
	s = s.replace(/<em>/ig, '<i>');
	s = s.replace(/<b>/ig, '<span style="font-weight: bold">');
	s = s.replace(/<i>/ig, '<span style="font-style: italic">');
	s = s.replace(/<u>/ig, '<span style="text-decoration: underline">');
	s = s.replace(/<\/(font|b|i|u)>/ig, '</span>');
	
	// <b><i><u> 带属性转换
	s = s.replace(/<(b|i|u)(\s+[^>]*)>/ig, function(all, tag, attrs) {
		var css = '';
		attrs = $.trim(attrs);
		
		attrs = attrs.replace(/(style)\s*=\s*['"]?([^'"]*)['"]?/ig, function(all, name, value) {
			if(tag == 'b') value = value.indexOf('font-weight') == -1 ? value += '; font-weight: bold' : value.replace(/font\-weight:\s*\w+/ig, 'font-weight: bold');
			else if(tag == 'i') value = value.indexOf('font-style') == -1 ? value += '; font-style: italic' : value.replace(/font\-style:\s*\w+/ig, 'font-weight: italic');
			else if(tag == 'u') value = value.indexOf('text-decoration') == -1 ? value += '; text-decoration: underline' : value.replace(/font\-weight:\s*\w+/ig, 'text-decoration: underline');
			return 'style="'+value+'"' 
		});
		return '<span '+attrs+'>';
	});
	
	s = s.replace(/<(font)\s*([^>]*)>/ig, function(all, tag, attrs) {
		var css = '';
		attrs = $.trim(attrs);
		if(!attrs) return '<span>'; // 如果没有属性则直接返回<span>
		attrs = attrs.replace(/(\w+)\s*=\s*['"]?([^'"]*)['"]?/ig, function(all, name, value) {
			name = $.trim(name.toLowerCase());
			if(name == 'size') {
				css += 'font-size: '+font_size_format(value)+'; ';
			} else if(name == 'family') {
				css += 'font-family: '+value+';';
			} else if(name == 'style') {
				css += 'font-style: '+value+';';
			} else if(name == 'color') {
				css += 'color: '+color_format(value)+';';
			}
		});
		css = css ? ' style="'+css+'"' : '';
		return '<span'+css+'>';
	});
	return s;
}

HTMLSpanElement.prototype.XNEditorKeys = ["background", "backgroundAttachment", "backgroundBlendMode", "backgroundClip", "backgroundColor", 
		"backgroundImage", "backgroundOrigin", "backgroundPosition", "backgroundPositionX",
		"backgroundPositionY", "backgroundRepeat", "backgroundRepeatX", "backgroundRepeatY",
		"backgroundSize", "font", "fontKerning", "fontKerning",
		"fontSize", "fontStretch", "fontStyle", "fontVariant",
		"fontVariantLigatures", "fontWeight", "color", "textDecoration"];

/*HTMLSpanElement.prototype.__defineGetter__('runtimeStyleArray', function() {
	return this.getStyleArray(true);
});
HTMLSpanElement.prototype.__defineGetter__('styleArray', function() {
	return this.getStyleArray(false);
});*/
HTMLSpanElement.prototype.getStyleArray = function(isRuntime) {
	var node = this;
	var arr = [];
	if(isRuntime) {
		var r = node.currentStyle ? node.currentStyle : document.defaultView.getComputedStyle(node, null); // currentStyle runtimeStyle
	} else {
		var r = node.style;
	}
	// 判断是否等价于 b i u
	for(var i = 0; i < HTMLSpanElement.prototype.XNEditorKeys.length; i++) {
		var k = HTMLSpanElement.prototype.XNEditorKeys[i];
		var v = r[k];
		if(v && k == 'background') {
			v = v.replace(/,\s/ig, ',');
			var arr2 = v.split(' ');
			if(arr2[0].substr(0, 3) == 'rgb') {
				arr2[0] = color_format(arr2[0]);
			}
			v = arr2.join(' ');
		} else if(k == 'backgroundColor' || k == 'color') {
			var v = color_format(v);
		}
		arr.push(v);
	}
	return arr;
};

// 删除当前 span 节点，并且移动孩子节点到上一级。$('xxx').unwrap();
HTMLElement.prototype.removeAndMoveUpChildren = function() {
	/* 直接进行 DOM 操作有 BUG*/
	// 这里。。。。不停变动，每次移动一个孩子，节点集合也会跟着变化。
	/*
	var children = this.childNodes;
	for(var i=0; i<children.length; i++) {
		this.parentNode.insertBefore(children[i], this);
		//this.parentNode.appendChild(children[i].cloneNode(true);
	}
	*/
	while(this.childNodes.length > 0) {
		this.parentNode.insertBefore(this.childNodes[0], this);
	}
	this.parentNode.removeChild(this);
}

// 替换节点
HTMLElement.prototype.relaceWithNode = function(node) {
	// 所有的孩子插入到 node, 
	this.parentNode.insertBefore(node, this);
	while(this.childNodes.length > 0) {
		node.appendChild(this.childNodes[0]);
	}
	this.parentNode.removeChild(this);
}

// 清理 span 节点
HTMLElement.prototype.clearSpanNodeDeep = function() {
	if(this.nodeType != 1) return;
	if(this.childNodes.length == 0) return;
	for(var i = 0; i < this.childNodes.length; i++) {
		var node = this.childNodes[i];
		if(node.nodeType != 1) continue;
		if(this.__proto__ === HTMLSpanElement.prototype && node.__proto__ === HTMLSpanElement.prototype) {
			if(this.cssEqual(node)) {
				node.removeAndMoveUpChildren();
			}
		}
		node.clearSpanNodeDeep();
	}
	return this;
}

// span 节点 转换为 b i u 标签
HTMLElement.prototype.spanToBIUDeep = function() {
	if(this.nodeType != 1) return;
	if(this.childNodes.length == 0) return;
	var pnode = this;
	if(this.__proto__ === HTMLSpanElement.prototype) {
		var tag = this.cssEqualTag();
		if(tag) {
			var newtag = document.createElement(tag);
			// 将当前节点的孩子全部插入到 newtag
			while(this.childNodes.length > 0) {
				newtag.appendChild(this.childNodes[0]);
			}
			this.parentNode.replaceChild(newtag, this); // 此处 this 会被替换掉，出现问题。
			pnode = newtag;
		}
	}
	for(var i = 0; i < pnode.childNodes.length; i++) {
		var node = pnode.childNodes[i];
		if(node.nodeType != 1) continue;
		node.spanToBIUDeep();
	}
	return this;
}

// 获取所有的 父节点集合，一直到最顶层节点为止。
HTMLElement.prototype.getParentNodes = function(topNode) {
	if(!topNode) topNode = document.body;
	var arr = [];
	if(this == topNode) return arr;
	var pnode = this.parentNode;
	while(pnode && pnode != topNode) {
		arr.push(pnode);
		pnode = pnode.parentNode;
	};
	return arr;
}

HTMLElement.prototype.hasParentTag = function(tagName, topNode) {
	if(this.tagName == tagName) return true;
	if(!topNode) topNode = document.body;
	var pnode = this.parentNode;
	while(pnode && pnode != topNode) {
		if(pnode.tagName == tagName) return true;
		pnode = pnode.parentNode;
	};
	return false;
}

// 获取所有的 父节点名称集合。
/*
HTMLElement.prototype.getParentNodeNames = function(topNode) {
	var r = [];
	var arr = this.getParentNodes(topNode);
	for(var i=0; i<arr.length; i++) {
		var v = arr[i];
		r.push(v.tagName);
	}
	return r;
}
*/

// 获取等价的 CSS 标签
HTMLSpanElement.prototype.cssEqualTag = function() {
	if(!document.simpleTagB) {
		var oFrag = document.createDocumentFragment();
		var b = document.createElement('span'); b.style.fontWeight = 'bold';
		var i = document.createElement('span'); i.style.fontStyle = 'italic';
		var u = document.createElement('span'); u.style.textDecoration = 'underline';
		oFrag.appendChild(b);
		oFrag.appendChild(i);
		oFrag.appendChild(u);
		document.simpleTagB = oFrag.childNodes[0].getStyleArray(false).toString();
		document.simpleTagI = oFrag.childNodes[1].getStyleArray(false).toString();
		document.simpleTagU = oFrag.childNodes[2].getStyleArray(false).toString();
	}
	var css = this.getStyleArray(false).toString();
	if(css == document.simpleTagB) return 'B';
	if(css == document.simpleTagI) return 'I';
	if(css == document.simpleTagU) return 'U';
	return null;
}

// 判断 span 是否css 是否为空
HTMLSpanElement.prototype.cssEmpty = function() {
	for(var i = 0; i < HTMLSpanElement.prototype.XNEditorKeys.length; i++) {
		var v = HTMLSpanElement.prototype.XNEditorKeys[i];
		if(this.style[v] != "") return false;
	}
	return true;
}

// css 是否与另外一个 span 节点是否相等
HTMLSpanElement.prototype.cssEqual = function(span2) {
	var span1 = this;
	var v1 = span1.getStyleArray(true);
	var v2 = span2.getStyleArray(true);
	// 判断两个数组的值是否相等。
	return v1.toString() == v2.toString();
}

// 判断当前的 Range 是否在 node 当中
Range.prototype.isChildOf = function(node) {
	var pnode = this.commonAncestorContainer;
	while(pnode && pnode.parentNode && pnode != document.body) {
		if(pnode == node) return true;
		pnode = pnode.parentNode;
	};
	return false;
}


$.XNEditor = function(args) {
	var _this = this;
	
	var jtextarea = this.jtextarea = $('#'+args.textarea);		// 
	var lasteditor = jtextarea[0].editor;
	
	if(lasteditor) {
		if(lasteditor.close) {
			lasteditor.close();				// 清理掉以后 textarea 消失?
			console.log('closed');
		}
		lasteditor = null;
	}
	
	var jeditor = this.jeditor = this.jclone.clone();		// 克隆公共的 editor div 节点，新申请的内存，析构里面应该删除
	var jtoolbar = this.jtoolbar = jeditor.son('div.toolbar');
	var jmenu = this.jmenu = jeditor.son('div.menu');
	var jbody = this.jbody = jeditor.son('div.body');
	var jfooter = this.jfooter = jeditor.son('div.footer');
	this.args = args;
	
	jtextarea.show();
	var w = jtextarea.outerWidth(); // ie outerWidth 准确
	var h = jtextarea.outerHeight();
	
	jtextarea.hide();
	
	jeditor.insertBefore(jtextarea).show();
	jtextarea.prop('spellcheck', false).hide();
	this.jtextareaclone = jtextarea.clone().attr('id', '');
	this.jtextareaclone.insertBefore(jbody);
	
	jtextarea[0].editor = this;
	this.jtextareaclone[0].editor = this;
	
	//var w = intval(jtextarea.css('width'));
	//var h = intval(jtextarea.css('height'));
	
	//jeditor.width(w + 2 + intval(jbody.css('padding-left')) + intval(jbody.css('padding-right'))); // 边框的宽度
	//jtextareaclone.width(w); // 边框的宽度
	//jtextareaclone.height(h + jtoolbar.height()); // 边框高度 + padding-top
	jbody.width(w - 2);
	jbody.height(h - 2);
	
	
	this.width = w;
	this.height = h;
	
	jtextarea[0].editor = this;
	this.jtextareaclone[0].editor = this;
	
	
	// 绑定事件
	var jtoolbar_click = function(obj) {
		_this.menu_hide();
		_this.range_save();
		return $(obj).hasClass('disabled');
	};
	jtoolbar.son('a.undo').on('click', function() {if(jtoolbar_click(this)) return true; _this.history(-1)});
	jtoolbar.son('a.redo').on('click', function() {if(jtoolbar_click(this)) return true; _this.history(1)});
	jtoolbar.son('a.bold').on('click', function() {if(jtoolbar_click(this)) return true; _this.execCommand('Bold')});
	jtoolbar.son('a.italic').on('click', function() {if(jtoolbar_click(this)) return true; _this.execCommand('Italic')});
	jtoolbar.son('a.underline').on('click', function() {if(jtoolbar_click(this)) return true; _this.execCommand('Underline')});
	jtoolbar.son('a.justifyleft').on('click', function() {if(jtoolbar_click(this)) return true; _this.execCommand('JustifyLeft')});
	jtoolbar.son('a.justifycenter').on('click', function() {if(jtoolbar_click(this)) return true; _this.execCommand('JustifyCenter')});
	jtoolbar.son('a.justifyright').on('click', function() {if(jtoolbar_click(this)) return true; _this.execCommand('JustifyRight')});
	jtoolbar.son('a.justifyfull').on('click', function() {if(jtoolbar_click(this)) return true; _this.execCommand('JustifyFull')});
	jtoolbar.son('a.file').on('click', function() {if(jtoolbar_click(this)) return true;});
	jtoolbar.son('a.image').on('click', function() {if(jtoolbar_click(this)) return true;});
	//jtoolbar.son('a.link').on('click', function() {_this.execCommand('CreateLink', true)});
	//jtoolbar.son('a.unlink').on('click', function() {_this.execCommand('Unlink')});
	
	// 标题大小
	jtoolbar.son('a.fontpg').on('click', function() {
		if(jtoolbar_click(this)) return true;
		$(this).menu_show(_this.jmenu.son('div.fontpg'), 0, 800);
	});
	//return;
	jmenu.son('div.fontpg').son('a').click(function() {
		var size = $(this).attr('title');
		_this.range_restore();
		_this.set_fontpg(size);
	});
	
	// 字体大小
	jtoolbar.son('a.fontsize').on('click', function() {
		if(jtoolbar_click(this)) return true;
		$(this).menu_show(_this.jmenu.son('div.fontsize'), 0, 800);
	});
	jmenu.son('div.fontsize').son('a').click(function() {
		var size = $(this).attr('title');
		_this.range_restore();
		_this.set_fontsize(size);
	});
	
	// 字体颜色
	jtoolbar.son('a.fontcolor').on('click', function() {
		if(jtoolbar_click(this)) return true;
		$(this).menu_show(_this.jmenu.son('div.fontcolor'), 0, 800);
	});
	jmenu.son('div.fontcolor').son('a').click(function() {
		var color = $(this).attr('title');
		_this.range_restore();
		_this.set_fontcolor(color);
	});
	
	// 源代码切换
	jtoolbar.son('a.html').on('click', function() {
		if(jtoolbar_click(this)) return true;
		// 判断当前的模式
		var jthis = $(this);
		if(jthis.hasClass('checked')) {
			_this.switch_mode(true);
		} else {
			_this.switch_mode(false);
		}
	});
	
	// 链接
	jtoolbar.son('a.link').on('click', function() {
		if(jtoolbar_click(this)) return true;
		_this.link_dialog();
	});
	jtoolbar.son('a.unlink').on('click', function() {
		if(jtoolbar_click()) return true;
		_this.link_clear();
	});
	
	// 全屏
	jtoolbar.son('a.fullscreen').on('click', function() {
		if(jtoolbar_click(this)) return true;
		_this.fullscreen();
	});
	
	// 格式刷
	jtoolbar.son('a.brush').on('click', function() {
		if(jtoolbar_click(this)) return true;
		document.execCommand("removeFormat", false, "");
	});
	
	// 代码
	jtoolbar.son('a.insertcode').on('click', function() {
		if(jtoolbar_click(this)) return true;
		_this.paste('<br><pre class="brush: js"></pre><br>');
	});
	
	// 表格
	var jtds = jmenu.son('div.table').find('td');
	jtoolbar.son('a.table').on('click', function() {
		if(jtoolbar_click(this)) return true;
		$(this).menu_show(_this.jmenu.son('div.table'), 7, 800);
	});
	
	jmenu.son('div.table').find('td').off('mouseover').on('mouseover', function() {
		// 查找当前 td 属于第几行第几列
		var td = this;
		jtds.each(function(k, v) {
			if(v.cellIndex <= td.cellIndex && v.parentNode.rowIndex <= td.parentNode.rowIndex) {
				$(v).addClass('checked');
			} else {
				$(v).removeClass('checked');
			}
		});
	});
	jmenu.son('div.table').find('td').off('click').on('click', function() {
		var tr = this.parentNode.rowIndex + 1; //: 3
		var td = this.cellIndex + 1;//: 3
		var width = Math.floor((_this.width - 21) / td);
		// console.log("tr: %d, td: %d", tr, td);
		// 插入表格
		var s = '<table class="tborder" width="'+ (_this.width - 21)+'">';
		for(var i=0; i<tr; i++) {
			s += '<tr>';
			for(var j=0; j<td; j++) s += '<td width="'+width+'">&nbsp;</td>';
			s += '</tr>';
		}
		s += '</table>';
		// 插入时，如果发现父节点是 p，则单独起一行
		// 判断 p 是否为空，为空则删除，非空则插入到最后
		_this.paste(s);
		_this.menu_hide();
	});
	
	// 仅显示 jtoolbar 移动相关功能
	if(args.in_mobile) {
		jtoolbar.son('a').hide();
		jtoolbar.son('a.undo').show();
		jtoolbar.son('a.redo').show();
		jtoolbar.son('a.bold').show();
		jtoolbar.son('a.file').show();
		jtoolbar.son('a.html').show();
		jtoolbar.son('a.image').show();
		jtoolbar.son('a.fullscreen').show();
	}
	
	// 点击 body 区域
	// 干掉 jpopover
	$(document).on('click.editor_'+this.jtextarea.attr('id'), function() {
		if(_this.last_jpopover && !_this.cancel_bubble) {
			_this.last_jpopover.removeDeep();
			_this.last_jpopover = null;
		}
		_this.cancel_bubble = false;
	});
	jbody.on('click', function(e) {
		var node = e.target;
		
		// 如果点击的元素为IMG，则显示调整大小的宽
		if(!is_ie_10) {
			if(node.nodeType == 1 && node.tagName == 'IMG') {
				_this.node_add_resize(node);
			} else {
				_this.node_remove_resize(node);
			}
		}
		
		// 查找所有当前鼠标点击的元素的父节点
		var pnodes = _this.get_parent_nodes(node); pnodes.push(node);
		var ptags = $.map(pnodes, function(v){return v.tagName});
		
		// 禁止嵌套表格
		if($.inArray('TABLE', ptags) != -1) {
			jtoolbar.son('a.table').addClass('disabled');
		} else {
			jtoolbar.son('a.table').removeClass('disabled');
		}
		
		if($.inArray('PRE', ptags) != -1) {
			jtoolbar.son('a.insertcode').addClass('disabled');
		} else {
			jtoolbar.son('a.insertcode').removeClass('disabled');
		}
		
		// 处理 A 标签
		var index = $.inArray('A', ptags);
		if(index != -1) {
			var link = pnodes[index];
			// 判断是否嵌套了 IMG 标签
			if($.inArray('IMG', ptags) != -1) {
				
			}
			_this.cancel_bubble = false;
			if(link.parentNode.className != 'popover color_yellow') {
				if(_this.last_jpopover) _this.last_jpopover.removeDeep();
				var jlink = $(link);
				jlink.popover('链接：<a href="'+link.href+'" target="_blank" class="grey">'+link.href.substr(0, 30)+(link.href.length > 30 ? '...' : '')+'</a>　<a href="javascript:void(0)" class="modify yellow" style="cursor: pointer" class="">修改</a>　<a href="javascript:void(0)" class="clear yellow" style="cursor: pointer">清除</a>', 'bottom', 'color_yellow');
				var jpopover = jlink.jpopover;
				_this.range_save();
				jpopover.insertAfter(jbody); // 移动到编辑器区域以外！否则弹出 popover 会可编辑
				jpopover.son('a.modify').one('click', function() {
					_this.range_restore();
					_this.link_dialog();
					_this.last_jpopover = null;
					jpopover.removeDeep();
					return false;
				});
				jpopover.son('a.clear').one('click', function() {
					_this.link_clear();
					_this.last_jpopover = null;
					jpopover.removeDeep();
					return false;
				});
				_this.last_jpopover = jpopover;
				// return false; // 太粗暴，会导致所有事件不向上冒泡
				// 本次点击冒泡事件阻止掉，防止 body.click 关闭 popover
				_this.cancel_bubble = true;
			}
		}
		
		_this.set_toolbar();
		//return false;
	});
	
	// 右键菜单，表格编辑插入删除等
	jbody.on('contextmenu', function(e) {
		var node = e.target;
		
		// 查找所有当前鼠标点击的元素的父节点
		var pnodes = _this.get_parent_nodes(node); pnodes.push(node);
		var ptags = $.map(pnodes, function(v){return v.tagName});
		
		var pre_index = $.inArray('PRE', ptags);
		if(pre_index != -1) {
			var pre = pnodes[pre_index];
			
			var jcontextmenu = _this.context_menu_code_show(e);
			var context_menu_code_active = function() {
				var classname = pre.className;
				if(classname.indexOf(':') != -1) {
					var arr = classname.split(':');
					var type = $.trim(arr[1]);
					jcontextmenu.son('ul').son('li').removeClass();
					jcontextmenu.son('ul').son('li[value="'+type+'"]').addClass('active');
				}
			}
			context_menu_code_active();
			
			// 插入一行
			jcontextmenu.son('ul').son('li').off('click').on('click', function() {
				var jli = $(this);
				var v = jli.attr('value');
				context_menu_code_active();
				jpre =  $(pre);
				jpre.removeClass().addClass('brush: '+v);
			});
			return false;
		}
		
		// 处理表格
		var td_index = $.inArray('TD', ptags);
		if(td_index != -1) {
			var tr_index = $.inArray('TR', ptags);
			var table_index = $.inArray('TABLE', ptags);
			var td = pnodes[td_index];
			var tr = pnodes[tr_index];
			var table = pnodes[table_index];
			var row_index = td.parentNode.rowIndex + 1; //: 3
			var col_index = td.cellIndex + 1;//: 3
			var jtable = $(table);
			var jtr = $(tr);
			var jcontextmenu = _this.context_menu_table_show(e);
			// 插入一行
			jcontextmenu.son('ul').son('li').eq(0).off('click').on('click', function() {
				var jclone = jtr.clone().insertAfter(jtr);
				jclone.son('td').map(function() {
					//$(this).son().remove();
					$(this).html('<br>');
				});
			});
			// 插入一列
			jcontextmenu.son('ul').son('li').eq(1).off('click').on('click', function() {
				jtable.find('tr').map(function() {
					var jtr2 = $(this);
					var jtd2 = jtr2.son('td').eq(col_index - 1);
					var jclone = jtd2.clone().html('<br>');
					jclone.insertAfter(jtd2);
					// 重新调整宽度
				});
			});
			// 删除一行
			jcontextmenu.son('ul').son('li').eq(2).off('click').on('click', function() {
				jtr.removeDeep();
			});
			// 删除一列
			jcontextmenu.son('ul').son('li').eq(3).off('click').on('click', function() {
				jtable.find('tr').map(function() {
					var jtr2 = $(this);
					var jtd2 = jtr2.son('td').eq(col_index - 1);
					jtd2.removeDeep();
				});
			});
			return false;
		}
		return true;
	});
	
	jbody.on('keyup input', function(e) {
		_this.history(true); // 纪录到历史
	});
	
	// fix br
	jbody.on('keydown', function(e) {
		
		var keycode = e.keyCode ? e.keyCode : (e.which ? e.which : e.charCode);
		
		// TAB
		if(keycode == 9) {
			// 查找是否在			
			// range.startContainer;
			var sel = window.getSelection();
			var range = sel.getRangeAt(0);
			
			// 查找所有当前鼠标点击的元素的父节点
			if(_this.has_parent_tag(range.commonAncestorContainer, 'PRE')) {
				_this.range_save();
				_this.paste("\t");
				return false;
			}
		// 回车
		} else if(keycode == 13) {
			// 判断自己是否在哪个容器当中， h1 - h5, div, p, span
			// 如果在 <h> 当中，则在 h 后插入p，并且聚焦
			
			// 查找是否在			
			// range.startContainer;
			var sel = window.getSelection();
			var range = sel.getRangeAt(0);
			
			// 查找所有当前鼠标点击的元素的父节点
			if(_this.has_parent_tag(range.commonAncestorContainer, 'PRE')) {
				_this.range_save();
				_this.paste("\r\n");
				return false;
			}
			
			if(range.commonAncestorContainer.tagName != 'P') {
				return true;
				// 切分 Range, 替换 div 标签
				var ancestor = range.commonAncestorContainer;
				var pancestor  = ancestor.nodeType == 3 ? ancestor.parentNode : ancestor;
				if(pancestor == jbody[0]) return true;
				
				// 切分为 3 段！ 判断当前的公共祖父节点的tagName
				// 向上查找 p, h, span, b, i, u，找到后选为父节点
				var findtags = ['DIV', 'P', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'SPAN', 'B', 'I', 'U', 'STRONG', 'EM'];
				var pnode = null;
				var pnodes = pancestor.getParentNodes(_this.jbody[0]);
				pnodes.push(pancestor);
				for(var j=0; j<pnodes.length; j++) {
					for(var i=0; i<findtags.length; i++) {
						if(pnodes[j].tagName == findtags[i]) {
							pnode = pnodes[j];
							break;
						}
					}
				}
				
				// 创建一个 div, 并且插入到 ancestor 的后面，如果 ancestor 为空，则删除掉
				// 找到 p h
				var startRange = document.createRange();
				if(!pnode) return true;
				startRange.setStartBefore(pnode);
				startRange.setEnd(range.startContainer, range.startOffset);
				var endRange = document.createRange();
				endRange.setStart(range.endContainer, range.endOffset);
				endRange.setEndAfter(pnode);
				var startContent = startRange.cloneContents();
				var endContent = endRange.cloneContents();
				
				if(!node_is_empty(startContent)) pancestor.parentNode.insertBefore(startContent, pancestor);
				var focusnode = document.createElement('br');
				pancestor.parentNode.insertBefore(focusnode, pancestor);
				if(!node_is_empty(endContent)) pancestor.parentNode.insertBefore(endContent, pancestor); // if(endContent.textContent != '') 
				range.selectNode(focusnode);
				
				pancestor.parentNode.removeChild(pancestor); // 删除
				
				// 插入P，并且定位进去
				range.deleteContents();
				
				
				var p = document.createElement('P');
				var focusnode = document.createElement('br');
				p.appendChild(focusnode);
				range.insertNode(p);
				range.selectNode(focusnode);
				
				if(is_ie_10) {
					focusnode.parentNode.removeChild(focusnode);
					range.deleteContents();
				}
				
				sel.removeAllRanges();
				sel.addRange(range);
				range.collapse(false);
				//focusnode.focus();
				return false;
			}
			return true;
			
		//
		} else if(keycode == 9) {
			//_this.paste(' &nbsp; &nbsp; ');
			return false;
		} else {
			return true;
		}
		
		// 在 td 中捕获 \t 定位到下一个 td
	});
	
	jbody.on('paste', function(e) {
		
		//var s = e.clipboardData.getData(); // 'Text'
		var clipboardData = e.clipboardData || window.clipboardData;
		if(!clipboardData) return;
		
		if(is_ie_10) {
			item = clipboardData.files.item(0);
		} else {
			// 文件类型
			var item = null;
			var items = clipboardData.items || [];
			var types = clipboardData.types || [];
			
			for(var i=0; i<types.length; i++) {
				if(types[i] == 'Files') {
					item = items[i];
					break;
				}
			}
			// 如果是火狐，则 == 0
			if(is_ff) item = items[0];
		}
		// item.kind == 'file' && ie 没有此项
		if(item && item.type.match(/^image\//i) ) {
			function image_reader(item) {
				var file = is_ie_10 ? item : item.getAsFile();
				var reader = new FileReader();
				reader.onload = function(e2) {
					var img = new Image();
					var filedata = e2.target.result;
					img.src = filedata;
					if(substr(filedata, 0, 10) != 'data:image') return;
					
					var s = filedata.substring(filedata.indexOf(',') + 1);
					var json = {name: "screen_capture_"+time()+".jpg", width: img.width, height: img.height, data: s};
					$.xpost(_this.args.image_upload_url, {upfile: json_encode(json)}, function(code, message) {
						if(code == 0) {
							var s = '<img src="'+message.url+'" width="'+message.width+'" height="'+message.height+'" />';
							//jbody.append(s);
							_this.range_save();
							_this.paste(s);
						} else {
							alert(message);
						}
					});
				}
				reader.readAsDataURL(file);
			}
			image_reader(item);
			// 阻止默认的粘贴动作。
			return false;
		} else {
			// ie : Text
			var s = clipboardData.getData(is_ie_10 ? 'Text' : 'text/html'); // firefox 下 office 格式居然取不到 application/office ?
			if(s && s.length > 0) {
				if(s.indexOf('<!--StartFragment-->') != -1 && s.indexOf('<!--EndFragment-->') != -1) {
					s = s.substring(s.indexOf('<!--StartFragment-->') + 20, s.indexOf('<!--EndFragment-->'));
				}
				var s2 = _this.clear_officeword(s);
				if(s2 && s2 != s) clipboardData.setData('text/html', s2);
			}
			
			// 检查 pre 标签
			if(s) {
				_this.range_save();
				_this.paste(s);
				return false;
			}
			// if(!s) s = clipboardData.getData('Text');
		}
		
		// 粘贴完后保存
		setTimeout(function() {_this.pdata_save();}, 200);
	});
	
	this.jtextareaclone.on('paste keyup', function() {
		_this.pdata_save(this.value);
	});
	
	// 如果 textarea 中有数据，则优先从 textarea 中获取数据？
	var val = this.jtextarea.val();
	if(val) {
		jbody.html(val);	
	} else {
		_this.pdata_load();
	}
	
	// 自动聚焦
	_this.focus();
	
	// 每隔2秒自动保存一次
	// setInterval(function() {_this.history()}, 4000);
	
	_this.init_image_upload();
	_this.init_file_upload();
		
};
$.XNEditor.prototype.width = null;  			// 编辑器的宽度
$.XNEditor.prototype.height = null;  			// 编辑器的高度
$.XNEditor.prototype.jclone = null;  			// 公共的编辑器 DOM 节点，用来克隆
$.XNEditor.prototype.jtextarea = null;  		// 
$.XNEditor.prototype.jtextareaclone = null;  		// 克隆一份，比较保险
$.XNEditor.prototype.jeditor = null;  			// 
$.XNEditor.prototype.jtoolbar = null;  			// 
$.XNEditor.prototype.jmenu = null;  			// 
$.XNEditor.prototype.jbody = null;  			// 
$.XNEditor.prototype.jfooter = null;  			// 
$.XNEditor.prototype.jtmp = null;  			// 临时节点，用来最大化最小化定位
$.XNEditor.prototype.issource = false;  		// 是否为源码模式
$.XNEditor.prototype.last_range = null;  		// 存储最后一个 range
$.XNEditor.prototype.last_jpopover = null;  		// 存储最后一个 popover
$.XNEditor.prototype.cancel_bubble = false;  		// 是否阻止冒泡
$.XNEditor.prototype.history_arr = [];  		// 历史记录的 innerHTML，用来保存上一步，下一步，纪录 50 步
$.XNEditor.prototype.history_index = -1;  		// 当前的记录点
$.XNEditor.prototype.history_last_time = 0;  		// 上一次存盘的时间
$.XNEditor.prototype.args = null;  			// 构造函数的参数

// 用来销毁
$.XNEditor.prototype.file_upload = null;  		// 文件上传
$.XNEditor.prototype.image_upload = null;  		// 图片上传
$.XNEditor.prototype.jlink = null;  			// link 对话框

// 清除dom节点和事件，释放资源，避免内存泄露！！
$.XNEditor.prototype.close = function() {
	// 清除新产生的 DOM 节点
	$(document).off('.editor_'+this.jtextarea.attr('id'));
	this.clear();
	this.file_upload = null;
	this.image_upload = null;
	if(this.jlink && this.jlink.dialog) this.jlink.dialog('destory');
	if(this.jmenu.son('div.fileupload')[0].dialog)this.jmenu.son('div.fileupload').dialog('destory');
	if(this.jmenu.son('div.imageupload')[0].dialog)this.jmenu.son('div.imageupload').dialog('destory');
	this.jeditor.removeDeep();
	
	//this = null; // 交给 js 引擎释放掉对象。	
}
$.XNEditor.prototype.execCommand = function(cmd, bool, arg) {
	if(!bool) bool = false;
	if(!arg) arg = null;
	document.execCommand(cmd, bool, arg);
	//_this.save();
	//_this.check_toolbar();
	this.set_toolbar();
	this.history(); // 纪录到历史
}
$.XNEditor.prototype.format_html = function() {
	var jbody = this.jbody;
	var s = font_tag_to_span(jbody.html());
	jbody.html(s);
	jbody[0].clearSpanNodeDeep();
	jbody[0].spanToBIUDeep();	
}

$.XNEditor.prototype.clear = function() {
	this.set_body('');
	this.history_arr = [];
	this.history_index = -1;
	this.history_last_time = 0;
	this.pdata_clear();
}

$.XNEditor.prototype.set_body = function(s) {
	console.log("set_body: %s", s);
	var jbody = this.jbody;
	var jtextarea = this.jtextarea;
	var jtextareaclone = this.jtextareaclone;
	jbody.html(s);
	jtextarea.val(s);
	jtextareaclone.val(s);
}

$.XNEditor.prototype.set_width = function() {
	
}

// 最多保存50步，如果发现超出50步，则干掉前面的25步
$.XNEditor.prototype.history = function(action) {
	var s = this.jbody.html();
	var jtoolbar = this.jtoolbar;     // 对象：引用
	var jbody = this.jbody;	    // 对象：引用
	var arr = this.history_arr; // 对象：引用
	var i = this.history_index; // 直接量：赋值
	var _this = this;
	// 强制保存，每隔2秒允许保存一次。
	if(!action || action === true) {
		// 如果内容发生了改变，则纪录
		this.history_saved = false;
		if(Date.now() - this.history_last_time  >= 2000) {
			if(i == -1 || (i > -1 && this.history_arr[i] != s)) {
				// 如果超出 30 步，则干掉前面的10个
				if(this.history_arr.length >= 30) {
					this.history_arr = this.history_arr.slice(10, this.history_arr.length);
					this.history_arr.length = 20;
					i = 19;
				}
				jtoolbar.son('a.undo').removeClass('disabled');
				i++;
				this.history_arr[i] = s;
				this.history_arr.length = i + 1;
				this.history_saved = true;
			}
			this.history_last_time = Date.now();
		} else {
			if(this.history_t) clearTimeout(this.history_t);
			this.history_t = setTimeout(function() {_this.history(true)}, 2000);
		}
		// 强制保存
		if(action === true && !this.history_saved) {
			this.history_arr[i] = s;
		}
	// 往前
	} else if(action === 1) {
		if(i < this.history_arr.length - 1) {
			i++;
			jbody.html(this.history_arr[i]);
		}
	// 倒退
	} else if(action === -1) {
		if(i >= 0) {
			i--;
			jtoolbar.son('a.undo').removeClass('disabled');
			jtoolbar.son('a.redo').removeClass('disabled');
			jbody.html(this.history_arr[i]);
		}
	}
	if(i >= this.history_arr.length - 1) {
		jtoolbar.son('a.redo').addClass('disabled');
	} else {
		jtoolbar.son('a.redo').removeClass('disabled');
	}
	if(i < 0) {
		jtoolbar.son('a.undo').addClass('disabled');
	} else {
		jtoolbar.son('a.undo').removeClass('disabled');
	}
	this.history_index = i; // 直接量：赋值回去
	this.pdata_save();
}

$.XNEditor.prototype.pdata_save = function(s) {
	if(!s) s = this.jbody.html();
	this.jtextarea.val(s);
	this.jtextareaclone.val(s);
	$.pdata('xneditor_pdata_'+this.jtextarea.attr('id'), s);
}

$.XNEditor.prototype.pdata_load = function() {
	var s = $.pdata('xneditor_pdata_'+this.jtextarea.attr('id'));
	if(s && s != '<p><br></p>') {
		this.jbody.html(s);
		this.jtextarea.val(s);
		this.jtextareaclone.val(s);
	}
}

$.XNEditor.prototype.pdata_clear = function() {
	$.pdata('xneditor_pdata_'+this.jtextarea.attr('id'), '');
}

// 切换编辑器模式
$.XNEditor.prototype.switch_mode = function(ishtml) {
	var jtextarea = this.jtextarea;
	var jtextareaclone = this.jtextareaclone;
	var jtoolbar = this.jtoolbar;
	var jbody = this.jbody;
	var jthis = jtoolbar.son('a.html');
	if(ishtml) {
		jtextareaclone.hide();
		var s = jtextareaclone.val();
		//s = this.clear_officeword(s);
		jbody.html(s);
		this.format_html();
		jbody.show();
		this.range_restore();
		this.focus();
		jthis.removeClass('checked');
		jtoolbar.son().not(jthis).not('a.fullscreen').removeClass('disabled');
	} else {
		this.range_save();
		jbody.hide();
		var s = $.trim(jbody.html());
		jtextareaclone.val(s);
		jtextareaclone.show().focus();
		jthis.addClass('checked');
		jtoolbar.son().not(jthis).not('a.fullscreen').addClass('disabled');
	}
	this.pdata_save();
}

// 全屏，将节点临时移动到 body 下。
$.XNEditor.prototype.fullscreen = function(type) {
	var jeditor = this.jeditor;
	var jtextarea = this.jtextarea;
	var jtextareaclone = this.jtextareaclone;
	var jtoolbar = this.jtoolbar;
	var jbody = this.jbody;
	var jthis = jtoolbar.son('a.fullscreen');
	
	// 缩小
	if(jthis.hasClass('checked')) {
		jeditor.insertBefore(this.jtmp).css('z-index', 0);
		this.jtmp.removeDeep(); this.jtmp = null;
		
		$(document.body).css('overflow', 'auto');
		var w = this.width;
		var h = this.height;
		jeditor.css({position: 'relative', left: 'auto', top: 'auto'});
		jeditor.width(w); // 边框的宽度
		jeditor.height(h + jtoolbar.height());
		//jbody.width(w - 2);
		jbody.width(w - 2);
		jbody.height(h - 2);
		//jtextarea.css('width', w+'px').css('height', h+'px');
		jtextareaclone.width(w).height(h - 4);
		jthis.removeClass('checked');
		
		// console.log('缩小: jtextarea w, h: %d, %d', w, h);
	// 放大
	} else {
		if(!this.jtmp) this.jtmp = $('<span></span>').insertAfter(jeditor);
		jeditor.appendTo(document.body).css('z-index', 2000);
		
		// 放大
		$(document.body).css('overflow', 'hidden');
		
		var w = $(window).width();
		var h = $(window).height();
		jeditor.css({position: 'absolute', left: '0px', top: '0px'});
		jeditor.width(w); // 边框的宽度
		jeditor.height(h);
		jbody.width(w - 22); // 滚动条+边框
		jbody.height(h - jtoolbar.height());
		//jtextarea.css('width', (w - 2)+'px').css('height', (h - jtoolbar.height() - 4)+'px');
		jtextareaclone.width(w - 2).height(h - jtoolbar.height() - 2).css('overflow', 'auto');
		jthis.addClass('checked');
		
		//console.log('放大: jtextarea w, h: %d, %d', w, h);
	}
	this.range_restore();
	
}

$.XNEditor.prototype.focus = function() {
	// 自动聚焦！ fix ie
	var _this = this;
	setTimeout(function() { _this.range_create(); }, 100);
}


$.XNEditor.prototype.range_create = function() {
	var node = this.jbody[0];
	window.focus();// ie 频繁刷新焦点会跑到浏览器地址输入栏
	var sel = window.getSelection();
	var range = document.createRange();
	if(!node.lastChild) node.appendChild(document.createElement('p'));
	range.selectNode(node.lastChild); // 选择节点
	// 如果发现最后一个节点是 p 标签，并且为空，则移动焦点进入
	if(node.lastChild.tagName == 'P') {
		if(!node.lastChild.lastChild || node.lastChild.lastChild.tagName != 'BR') {
			var br = document.createElement('br');
			node.lastChild.appendChild(br);
		} else {
			br = node.lastChild.lastChild;
		}
		range.setStartAfter(br);
		range.setEndAfter(br);
		//range.selectNode(br);
		//range.deleteContents();
		//range.collapse(false); 		// 尾部
		if(is_ie_10) node.lastChild.removeChild(br);	// ie 需要删除此节点。
	} else {
		range.selectNode(node.lastChild);
		range.collapse(false); 		// 尾部
	}
	sel.removeAllRanges();
	sel.addRange(range);
	
	//node.style.border = 'none';
	node.style.outline = '0';	// chrome 下会有蓝色的线框
	//sel.collapse(node.firstChild, 1);
	return range;
}


$.XNEditor.prototype.range_select = function(node) {
	window.focus();// ie 频繁刷新焦点会跑到浏览器地址输入栏
	var sel = window.getSelection();
	var range = document.createRange();
	range.selectNode(node); // 选择节点
	sel.removeAllRanges();
	sel.addRange(range);
	node.style.outline = '0';	// chrome 下会有蓝色的线框
	//sel.collapse(node.firstChild, 1);
	return range;
}

$.XNEditor.prototype.strip_tags = function (s) {
	s = s.replace(/<br[^>]*?>/ig, "\r\t\n");
	s = s.replace(/<tr[^>]*?>/ig, "\r\t\n");
	s = s.replace(/<p[^>]*?>/ig, "\r\t\n");
	s = s.replace(/<div[^>]*?>/ig, "\r\t\n");
	s = s.replace(/(\r\t\n)+/ig, "\n");
	s = s.replace(/<\/?\w+[^>]*?>/ig, '');
	return s;
}

/*
	<p><h1> 只能插入 span b i u 等内联标签
	查找第一级的孩子里是否有 block 标签：div, table, ul, dl，他们不能插入到 p h1 当中
	如果容器为 p ， 则需要替换为 div
	换行任何时候都需要插入 <p>
*/
$.XNEditor.prototype.paste = function(s, ishtml) {
	// 粘贴 对内容进行过滤
	s = this.clear_officeword(s);
	
	var sel = window.getSelection();
	var range = this.last_range;
	
	// 如果 range 不在当前编辑器当中，则聚焦到当前编辑器
	if(!range || !range.isChildOf(this.jbody[0])) {
		range = this.range_create();
	}
	var frag = document.createElement('div');
	frag.innerHTML = s;
	
	// 代码段
	if(this.has_parent_tag(range.commonAncestorContainer, 'PRE')) {
		frag.innerHTML = this.strip_tags(s);
		if(frag.firstChild && frag.firstChild.tagName == 'PRE') {
			frag.firstChild.removeAndMoveUpChildren();
		}
	}
	
	// -----------------> 判断非正常插入开始
	// 当前粘贴的节点是否包含 block 标签
	var haveblock = false;
	for(var i=0; i<frag.childNodes.length; i++) {
		if("_TABLE_DIV_UL_DL_".indexOf('_'+frag.childNodes[i].tagName+'_') != -1) {
			haveblock = true;
			break;
		}
	}
	// 查找选区的外面的容器！
	if(haveblock) {
		var ancestor = range.commonAncestorContainer;
		var pancestor  = ancestor.nodeType == 3 ? ancestor.parentNode : ancestor;
		
		// 切分为 3 段！
		if("_P_H1_H2_H3_H4_H5_H6_SPAN_B_I_U_".indexOf('_'+pancestor.tagName+'_') != -1) {
			
			// 向上查找 p, h, span, b, i, u，找到后选为父节点
			var pnode = null;
			var pnodes = pancestor.getParentNodes(this.jbody[0]);
			pnodes.push(pancestor);
			var findtags = ['P', 'H1', 'H2', 'H3', 'H4', 'H5', 'H6', 'SPAN', 'B', 'I', 'U', 'STRONG', 'EM'];
			for(var j=0; j<pnodes.length; j++) {
				for(var i=0; i<findtags.length; i++) {
					if(pnodes[j].tagName == findtags[i]) {
						pnode = pnodes[j];
						break;
					}
				}
			}
			
			// 创建一个 div, 并且插入到 ancestor 的后面，如果 ancestor 为空，则删除掉
			// 找到 p h
			var startRange = document.createRange();
			startRange.setStartBefore(pnode);
			startRange.setEnd(range.startContainer, range.startOffset);
			var endRange = document.createRange();
			endRange.setStart(range.endContainer, range.endOffset);
			endRange.setEndAfter(pnode);
			var startContent = startRange.cloneContents();
			var endContent = endRange.cloneContents();
			
			//var jstartContent = $(pancestor).insertBefore(startContent);
			//var jendContent = $(pancestor).insertBefore(endContent);
			pancestor.parentNode.insertBefore(startContent, pancestor);
			var focusnode = document.createElement('br');
			pancestor.parentNode.insertBefore(focusnode, pancestor);
			pancestor.parentNode.insertBefore(endContent, pancestor);
			range.selectNode(focusnode);
			
			pancestor.parentNode.removeChild(pancestor); // 删除
		}
	}
	// -----------------> 判断非正常插入结束
	
	range.deleteContents();
	var lastChild = frag.lastChild;
	while(frag.childNodes.length > 0) {
		range.insertNode(frag.childNodes[frag.childNodes.length - 1]);
	}
	
	sel.addRange(range);
	sel.removeAllRanges();
	
	var oRange = document.createRange();
	oRange.selectNode(lastChild);
	oRange.collapse(false);
	sel.addRange(oRange);
	
	this.history(); // 纪录到历史
}

$.XNEditor.prototype.range_restore = function() {
	var sel = window.getSelection();
	if(this.last_range) {
		sel.removeAllRanges();
		sel.addRange(this.last_range);
	}
}
$.XNEditor.prototype.range_save = function() {
	var sel = window.getSelection();
	if(sel.getRangeAt && sel.rangeCount && sel.anchorNode.nodeName != 'SCRIPT') {
		this.last_range = sel.getRangeAt(0);
	} else {
		this.last_range = null;
	}
}
$.XNEditor.prototype.range_select_node = function(node) {

}

$.XNEditor.prototype.menu_hide = function() {
	// 隐藏第一级的DIV
	this.jmenu.son('div').not(this.jmenu.son('div.upload')).hide();
}

$.XNEditor.prototype.set_fontcolor = function(color) {
	// 设置选中状态的字体颜色
	if(color == '清除') {
		document.execCommand("removeFormat", false, "foreColor"); // 此处有 bug, 会去掉所有的格式
		//document.execCommand("foreColor",false,"inherit")
		//document.execCommand("foreColor",false,"");
		//document.execCommand("foreColor", false, "inherit");
		//document.execCommand("foreColor",false,"rgba(0, 0, 0, 0)");
	} else {
		this.execCommand('ForeColor', false, color);
	}
	this.jtoolbar.son('a.fontcolor').css('background-color', color);
	this.jmenu.son('div.fontcolor').hide();
}
	
$.XNEditor.prototype.set_fontsize = function(size) {
	this.execCommand('fontsize', false, size);
	this.jmenu.son('div.fontsize').hide();
}

$.XNEditor.prototype.set_fontpg = function(size) {
	this.execCommand('formatblock', false, "<h"+size+">");
	this.jmenu.son('div.fontpg').hide();
}

$.XNEditor.prototype.link_dialog = function() {
	var _this = this;
	var jlink = this.jlink ? this.jlink : _this.jmenu.son('div.link');// dialog 弹出以后，第二次在 body 区域
	this.jlink = jlink;
	var jurl = jlink.find('input[name="url"]');
	var jtext = jlink.find('input[name="text"]');
	var jconfirm = jlink.find('button.confirm');
	var jcancel = jlink.find('button.cancel');
	jlink.dialog({model: true, open:true, zindex: 3000});
	jurl.val('');
	jtext.val('');
	
	// 初始化
	var is_link = false; // 是否为链接
	var is_img = false; // 是否为图片
	var img_node = null;
	var range = _this.last_range;
	if(range) {
		var pnode = range.startContainer;
		var link = $(pnode).closest('a')[0];
		if(link) {
			var href = link ? link.href : 'http://';
			var text = link.innerText ? link.innerText : href;
			jurl.val(href);
			jtext.val(text);
			is_link = true;
		} else {
			var text = range.cloneContents().textContent;
			jtext.val(text);
		}
	}
	// 判断是否为图片，则为编辑模式
	var clone = range.cloneContents();
	if(clone.firstChild && clone.firstChild.tagName == 'IMG') {
		is_img = true;
		img_node = range.startContainer.childNodes[range.startOffset];
	}
	if(is_link || is_img) {
		jlink.find('p').eq(1).hide(); // 如果为修改模式，则隐藏文本。只有在插入模式显示文本。
	} else {
		jlink.find('p').eq(1).show();
	}
	
	jurl.focus();
	jconfirm.off('click').on('click', function() {
		var text = jtext.val() ? jtext.val() : jurl.val();
		if(is_img && !is_link) {
			// 如果是图片，则对图片进行添加<a>
			// 节点前插入A，然后将 IMG 移入其中
			var ja = $('<a href="'+jurl.val()+'" target="_blank"></a>').insertBefore(img_node);
			ja.append(img_node);
			/*var a = document.createElement('a');
			a.href = jurl.val();
			a.target = '_blank';
			img_node.parentNode.insertBefore(a, img_node);*/
			//a.appendChlid(img_node); // HTMLAnchorElement has no appendChild
		} else {
			if(is_link) {
				link.href = jurl.val();
				//link.innerText = text;
			} else {
				if(jurl.val()) _this.paste('<a href="'+jurl.val()+'" target="_blank">'+text+'</a>');
			}
		}
		jlink.dialog('close');
	})
	jcancel.off('click').on('click', function() {
		jlink.dialog('close');
	});
}
$.XNEditor.prototype.link_clear = function() {
	var _this = this;
	var range = _this.last_range;
	if(range) {
		var pnode = range.startContainer;
		var link = $(pnode).closest('a')[0];
		if(link) link.removeAndMoveUpChildren();
	}
}

// 设置 toolbar 选中状态
$.XNEditor.prototype.set_toolbar = function(size) {
	var cmds = new Array('bold', 'italic', 'underline', 'justifyleft', 'justifycenter', 'justifyright');
	for(var i=0; i<cmds.length; i++) {
		try{
			var status = document.queryCommandState(cmds[i]);
			this.jtoolbar.son('a.'+cmds[i]).toggleClass('checked', status);
		} catch(e) {}
	}
	
	var cmds = new Array('fontsize', 'forecolor', 'formatblock');//'fontname'
	for(var i=0; i<cmds.length; i++) {
		try {
			var value = document.queryCommandValue(cmds[i]);
		} catch(e) { continue; }
		if(cmds[i] == 'forecolor') {
			value = color_format(value);
			this.jtoolbar.son('a.fontcolor').css('background-color', value);
			// 设置颜色
		} else if(cmds[i] == 'fontsize') {
			value2 = parseInt(value);
			value2 = font_size_format(value2);
			value2 = value2.replace(/px/ig, '像素');
			this.jtoolbar.son('a.fontsize').html(value2 ? value2 : '字号');
		} else if(cmds[i] == 'formatblock') {
			var value2 = '';
			switch(value) {
				case 'h1': value2 = 1; break;
				case 'h2': value2 = 2; break;
				case 'h3': value2 = 3; break;
				case 'h4': value2 = 4; break;
				case 'h5': value2 = 5; break;
				case 'h6': value2 = 6; break;
			}
			this.jtoolbar.son('a.fontpg').html(value2 ? 'H'+value2 : '标题');
		}
	}
};

// 图片上传
$.XNEditor.prototype.init_image_upload = function() {
	var _this = this;
	var url = this.args.image_upload_url;
	var fileinput = this.jtoolbar.son('a.image').son('input')[0];
	var jform = this.jtoolbar.closest('form');				// 查找编辑器所在的表单
	var postdata = jform.length > 0 ? jform.serializeObj() : ''; 
	this.image_upload = new FileUploader(fileinput, url, postdata);
	this.image_upload.thumb_width = (this.args.image_upload_width || 800);
	//this.image_upload.thumb_width2 = (this.args.image_upload_width2 || 200);
	var jimageupload = _this.jmenu.son('div.imageupload');
	this.image_upload.onprogress = function(file, percent) {
		var jli = file.jli;
		file.jli.son('span._progress_current').width(percent+'%');
	}
	this.image_upload.ononce = function(file, e) {
		console.log("file: %o", file);
		var jli = file.jli;
		// 将图片的 image_src 附加到 jli 节点。
		var json = json_decode(e.target.response);
		if(json && json.code == 0) {
			jli[0].message = json.message;
		} else {
			var err = json && json.message ? json.message : e.target.response;
			jli.removeClass('checked').son('span._error').show().son('span').text(err);
		}
		// 删除事件
		jli.son('span._trash').on('click', function() {
			// 删除节点
			jli.removeDeep();
			return false;
		}).show();
	}
	this.image_upload.oncomplete = function(code, files) {
		jimageupload.find('button.confirm').show();
	}
	this.image_upload.onselected = function(files) {
		var jul = jimageupload.find('ul');
		
		// 弹出层
		jimageupload.dialog({ title: '上传图片', modal: true, open: true, zindex: 3000});
		
		// 遍历 files, 克隆节点
		jul.son('li').not('li.clone').removeDeep(); // 清理掉所有节点，除了第一个。
		//jul.son('li').filter(function(){return !$(this).hasClass('clone')}).remove(); // 清理掉所有节点，除了第一个。
		var jclone = jul.son('li').eq(0).hide().clone().removeClass('clone').show();
		$.each(files, function() {
			var file = this;
			if(!/^image/.test(file.type) || !/(.jpg|.jpeg|.gif|.png|.bmp)$/i.test(file.type)) {
				jli.son('span._error').son('span').text('只允许上传jpg、jpeg、gif、png格式的图片'); return;
			}
			if(file.size > 10120000) {
				jli.son('span._error').son('span').text('图片不能超过10M'); return;
			}
			var jli = jclone.clone().appendTo(jul).addClass('checked');
			file.jli = jli;	// 此处关联
			jli.son('span._content').son('img').srcLocalFile(file);
			jli.son('span._progress_title').text(file.name.substr(0, 13));
			jli.on('click', function() {
				var errtext = jli.son('span._error').son('span').text();
				if(!errtext) $(this).toggleClass('checked');
			});
			jli.son('span._trash').hide();
			jli.son('span._error').hide();
		});
		console.log("onselect: %o", files);
		/*for(var i=0; i<files.length; i++) {
			+function(i) {
				var file = files[i];
				if(!/^image/.test(file.type) || !/(.jpg|.jpeg|.gif|.png|.bmp)$/i.test(file.type)) {
					jli.son('span._error').son('span').text('只允许上传jpg、jpeg、gif、png格式的图片'); return;
				}
				if(file.size > 10120000) {
					jli.son('span._error').son('span').text('图片不能超过10M'); return;
				}
				var jli = jclone.clone().appendTo(jul).addClass('checked');
				file.jli = jli;	// 此处关联
				jli.son('span._content').son('img').srcLocalFile(file);
				jli.son('span._progress_title').text(file.name.substr(0, 13));
				jli.on('click', function() {
					var errtext = jli.son('span._error').son('span').text();
					if(!errtext) $(this).toggleClass('checked');
				});
				jli.son('span._trash').hide();
				jli.son('span._error').hide();
			}(i);
		}*/
		
		// 隐藏确定按钮
		jimageupload.find('button.confirm').hide().off('click').on('click', function() {
			// 将图片插入禁区
			var s = '';
			jul.son('li').each(function() {
				// 服务端把图片缩略厚，返回宽高，并且约定最大值。
				//var w = this.message.width > _this.width ? _this.width : this.message.width;
				//var h = this.message.height > _this.height ? _this.height : this.message.height;
				if(this.message && $(this).hasClass('checked')) s += '<img src="'+this.message.url+'" width="'+this.message.width+'" height="'+this.message.height+'" />';
			});
			if(s) _this.paste(s); // 将内容粘贴进编辑器
			jimageupload.dialog('close');
		});
		jimageupload.find('button.cancel').off('click').on('click', function() {
			jimageupload.dialog('close');
		});
		_this.image_upload.start();
	}
	
	this.image_upload.onerror = function(file, e) {
		var jli = file.jli;
		var json = json_decode(e.target.response);
		var err = json && json.message ? json.message : e.target.response;
		jli.son('span._error').show().son('i').text(err);
	}
	this.image_upload.onabort = function(file, e) {}
	this.image_upload.init();
}

$.XNEditor.prototype.init_file_upload = function() {
	var _this = this;
	var url = this.args.file_upload_url;
	var fileinput = this.jtoolbar.son('a.file').son('input')[0];
	var jform = this.jtoolbar.closest('form');				// 查找编辑器所在的表单
	var postdata = jform.length > 0 ? jform.serializeObj() : ''; 
	this.file_upload = new FileUploader(fileinput, url, postdata);
	var jfileupload = _this.jmenu.son('div.fileupload');
	this.file_upload.onprogress = function(file, percent) {
		// file 找到 li
		var jli = file.jli;
		file.jli.son('span._progress_current').width(percent+'%');
	}
	this.file_upload.ononce = function(file, e) {
		var jli = file.jli;
		
		// 将文件的 file_src 附加到 jli 节点。
		var json = json_decode(e.target.response);
		if(json && json.code == 0) {
			jli[0].message = json.message;
		} else {
			var err = json && json.message ? json.message : e.target.response;
			jli.removeClass('checked').son('span._error').show().son('span').text(err);
		}
		
		jli.son('span._trash').on('click', function() {
			jli.removeDeep();
			return false;
		}).show();
	}
	this.file_upload.onselected = function(files) {
		var jul = jfileupload.find('ul');
		
		// 弹出层
		jfileupload.dialog({ title: '上传文件', modal: true, open: true, zindex: 3000});
		
		// 遍历 files, 克隆节点
		jul.son('li').not('li.clone').removeDeep(); // 清理掉所有节点，除了第一个。
		var jclone = jul.son('li').eq(0).hide().clone().removeClass('clone').show();
		for(var i=0; i<files.length; i++) {
			+function(i) {
				var file = files[i];
				if(file.size > 10120000) {
					jli.son('span._error').son('span').text('文件不能超过10M'); return;
				}
				var jli = jclone.clone().appendTo(jul).addClass('checked');
				file.jli = jli;	// 此处关联
				// 判断文件类型
				var type = file_type(file_suffix(file.name));
				if(type == 'image') {
					jli.son('span._content').son('img').srcLocalFile(file);
				} else {
					jli.son('span._content').html('<i class="icon filetype '+type+'"></i>');
				}
				jli.son('span._progress_title').text(file.name.substr(0, 13));
				jli.on('click', function() {
					var errtext = jli.son('span._error').son('span').text();
					if(!errtext) $(this).toggleClass('checked');
				});
				jli.son('span._trash').hide();
				jli.son('span._error').hide();
			}(i);
		}
		
		// 隐藏确定按钮
		jfileupload.find('button.confirm').hide().off('click').on('click', function() {
			var s = '';
			jul.son('li').each(function() {
				// 服务端把文件缩略厚，返回宽高，并且约定最大值。
				if(this.message && $(this).hasClass('checked')) {
					s += ' <a href="'+this.message.url+'" target="_blank"><i class="icon filetype small '+file_type(file_suffix(this.message.name))+'"></i>'+this.message.name+'</a> ';
				}
			});
			if(s) _this.paste(s + "&nbsp; "); // 将内容粘贴进编辑器
			jfileupload.dialog('close');
		});
		jfileupload.find('button.cancel').off('click').on('click', function() {
			jfileupload.dialog('close');
		});
		_this.file_upload.start();
	}
	this.file_upload.oncomplete = function(code, files) {
		jfileupload.find('button.confirm').show();
	}
	this.file_upload.onerror = function(file, e) {
		var jli = file.jli;
		var json = json_decode(e.target.response);
		var err = json && json.message ? json.message : e.target.response;
		jli.son('span._error').show().son('i').text(err);
	}
	this.file_upload.onabort = function(file, e) {}
	this.file_upload.init();
}

// 将当前节点放入其中
$.XNEditor.prototype.get_parent_nodes = function(node) {
	if(!node) return [];
	return node.getParentNodes(this.jbody[0]);
}

// 将当前节点放入其中
$.XNEditor.prototype.has_parent_tag = function(node, tagName, topNode) {
	return HTMLElement.prototype.hasParentTag.call(node, tagName, topNode);
}

// 对节点进行 resize
$.XNEditor.prototype.node_add_resize = function(node) {
	var jbody = this.jbody;
	var jeditor = this.jeditor;
	var jmenu = this.jmenu;
	var jresize = jmenu.son('div.resize');
	var jnode = $(node);
	var offset = jnode.position(); // offsetParent
	var pnode = jnode.offsetParent();
	var poffset = pnode.offset();
	var x = offset.left;
	var y = offset.top;
	var w = jnode.width();
	var h = jnode.height();
	
	// console.log("offset: %o, poffset: %o", offset, poffset);
	
	// 选中 Range 为当前图片
	this.last_range = this.range_select(node);
	
	//jnode.attr('unselectable', "off");
	var jresize_position = function(x, y, w, h) {
		jresize.width(w + 2).height(h + 2).css('left', x + 'px').css('top', y + 'px');
		x = 0; y = 0;
		jresize.son('span').eq(0).css('left', (x - 3) + 'px').css('top', (y - 3) + 'px');
		jresize.son('span').eq(1).css('left', (x + (w + 2) / 2 - 3) + 'px').css('top', (y - 3) + 'px');
		jresize.son('span').eq(2).css('left', (x + (w + 2) - 3) + 'px').css('top', (y - 3) + 'px');
		jresize.son('span').eq(3).css('left', (x - 3) + 'px').css('top', (y + (h + 2) / 2 - 3) + 'px');
		jresize.son('span').eq(4).css('left', (x + (w + 2) - 3) + 'px').css('top', (y + (h + 2) / 2 - 3) + 'px');
		jresize.son('span').eq(5).css('left', (x - 3) + 'px').css('top', (y + (h + 2) - 3) + 'px');
		jresize.son('span').eq(6).css('left', (x + (w + 2) / 2 - 3) + 'px').css('top', (y + (h + 2) - 3) + 'px');
		jresize.son('span').eq(7).css('left', (x + (w + 2) - 3) + 'px').css('top', (y + (h + 2) - 3) + 'px');
	};
	jresize_position(x, y, w, h);
	
	// 修正：选中状态的时候无法拖拽
	jresize.on('mousedown', function() {jresize.hide();});
	
	// 开始拖拽 .add(jresize)
	jnode.add(jresize).add(jresize.son('span')).on('drag', function() {return false;});
	jresize.son('span').eq(4).off('mousedown').on('mousedown', function() {
		jbody.add(jresize).off('mousemove').on('mousemove', function(e) {
			var w2 = e.pageX - x - poffset.left;
			if(w2 > 10) {
				w = w2;
				jnode.width(w2);
				jnode.height(h);
				jresize_position(x, y, w, h);
			}
			// console.log('x: %d, y: %d, w: %d, h: %d, e.clientX: %d, e.clientY, e: %o', x, y, w, h, e.pageX, e.pageY, e);
			return true;
		});
		jeditor.son('div.popover').hide();
		return false;
	});
	jresize.son('span').eq(6).off('mousedown').on('mousedown', function() {
		jbody.add(jresize).off('mousemove').on('mousemove', function(e) {
			var h2 = e.pageY - y - poffset.top;
			if(h2 > 10) {
				h = h2;
				jnode.width(w);
				jnode.height(h);
				jresize_position(x, y, w, h);
			}
			// console.log('x: %d, y: %d, w: %d, h: %d, e.clientX: %d, e.clientY, e: %o', x, y, w, h, e.pageX, e.pageY, e);
			return true;
		});
		jeditor.son('div.popover').hide();
		return false;
	});
	jresize.son('span').eq(7).off('mousedown').on('mousedown', function() {
		jbody.add(jresize).off('mousemove').on('mousemove', function(e) {
			var w2 = e.pageX - x - poffset.left;
			var h2 = e.pageY - y - poffset.top;
			if(w > 10 && h > 10) {
				h = (w2 / w) * h;
				w = w2;
				//h = h2;
				jnode.width(w);
				jnode.height(h);
				jresize_position(x, y, w, h);
			}
			// console.log('x: %d, y: %d, w: %d, h: %d, e.clientX: %d, e.clientY, e: %o', x, y, w, h, e.pageX, e.pageY, e);
			return true;
		});
		jeditor.son('div.popover').hide();
		return false;
	});
	var _this = this;
	jresize.off('mouseup').on('mouseup', function() {
		jbody.add(jresize).off('mousemove');
		return false;
	});
	$(document).off('mouseup.editor_'+this.jtextarea.attr('id')).on('mouseup.editor_'+this.jtextarea.attr('id'), function() {
		jbody.add(jresize).off('mousemove');
		return false;
	});
	
	jbody.on('scroll', function(e) {
		var jresize = jmenu.son('div.resize');
		jresize.hide();
		jbody.off('scroll');
		//jnode.attr('unselectable', "on");
	});
	
	jresize.show();
}

// 对节点进行 resize
$.XNEditor.prototype.node_remove_resize = function(node) {
	var jbody = this.jbody;
	var jeditor = this.jeditor;
	var jresize = this.jmenu.son('div.resize');
	var jnode = $(node);
	//jnode.attr('unselectable', "on");
	jresize.hide();
}

$.XNEditor.prototype.context_menu_table_show = function(e) {
	var _this = this;
	var pnode = this.jmenu.offsetParent();
	var poffset = pnode.offset();
	var jcontextmenu = this.jmenu.son('div.context_menu_table');
	jcontextmenu.css({position: "absolute", top: (e.pageY - poffset.top)  + 'px', left : (e.pageX - poffset.left) + 'px', 'z-index':3000}).show();
	$(document).one('click.editor_'+this.jtextarea.attr('id'), function() {_this.context_menu_table_hide(); });
	return jcontextmenu;
}

$.XNEditor.prototype.context_menu_table_hide = function() {
	var jcontextmenu = this.jmenu.son('div.context_menu_table');
	jcontextmenu.hide();
}

$.XNEditor.prototype.context_menu_code_show = function(e) {
	var _this = this;
	var pnode = this.jmenu.offsetParent();
	var poffset = pnode.offset();
	var jcontextmenu = this.jmenu.son('div.context_menu_code');
	jcontextmenu.css({position: "absolute", top: (e.pageY - poffset.top)  + 'px', left : (e.pageX - poffset.left) + 'px', 'z-index':3000}).show();
	$(document).one('click.editor_'+this.jtextarea.attr('id'), function() {_this.context_menu_code_hide(); });
	return jcontextmenu;
}

$.XNEditor.prototype.context_menu_code_hide = function() {
	var jcontextmenu = this.jmenu.son('div.context_menu_code');
	jcontextmenu.hide();
}

// 清除 word 格式
$.XNEditor.prototype.clear_officeword = function(s) {
	if(s.match(/mso-cellspacing:/i) || s.match(/<v|o:\w+/i) || s.match(/<w:WordDocument>/i) || s.match(/<!{1}--\[if gte mso \d+\]>/i)) {
		var allowtags = ['table', 'tbody', 'tr', 'td', 'th', 'div', 'p', 'br', 'a', 'img', 'h1', 'h2', 'h3', 'h4', 'h5', 'hr'];// 'span', 
		var allattrs = ['width', 'height', 'href', 'src', 'align', 'border', 'cellspaceing', 'cellspadding', 'border'];
		s = s.replace(/<!.*?>/img, '');
		s = s.replace(/<style[^>]*>[\s\S]+?<\/style>/ig, '');
		s = s.replace(/<xml[^>]*>[\s\S]+?<\/xml>/ig, '');
		s = s.replace(/\s+/ig, ' ');
		// 白名单过滤
		s = s.replace(/<([\w\-:]+)\s*([^>]*)>/ig, function(all, tag, attrs) {
			// 保留 table tr td p br a标签
			// 保留 width href align 属性
			// 不保留 font size class style ....
			tag = tag.toLowerCase();
			if($.inArray(tag, allowtags) == -1) {
				return '';
			}
			
			attrs = $.trim(attrs);
			attrs = attrs.replace(/(\w+)\s*=\s*['"]?([^'"]*)['"]?/ig, function(all, name, value) {
				name = $.trim(name.toLowerCase());
				if($.inArray(name, allattrs) == -1) {
					return '';
				} else {
					if(name == 'border') {
						return 'border="1"';
					} else {
						return all;
					}
				}
			});
			attrs = $.trim(attrs);
			
			return '<'+tag+''+(attrs ? ' ' : '')+attrs+'>';
		});
		s = s.replace(/<\/([\w\-:]+)\s*>/ig, function(all, tag) {
			if($.inArray(tag, allowtags) == -1) {
				return '';
			} else {
				return all;
			}
		});
	}
	s = this.clear_code(s);
	return s;
}

// 清理 code
$.XNEditor.prototype.clear_code = function(s) {
	s = s.replace(/<code([^>]*)>(.*?)<\/code>/ig, function(all, attrs, text) {
		/*attrs = $.trim(attrs);
		attrs = attrs.replace(/(\w+)\s*=\s*['"]?([^'"]*)['"]?/ig, function(all, name, value) {
			if(name == "class") {
				return 'class="'+value+'"';
			} else {
				return '';
			}
		});*/
		return '<code>'+text+'</code>';
	});
	return s;
}

/*
// 增加一行
$.XNEditor.prototype.table_row_insert = function() {
	var jcontextmenu = this.jmenu.son('div.context_menu');
	jcontextmenu.hide();
	
	// insertRow
}

// 删除一行
$.XNEditor.prototype.table_row_remove = function() {
	var jcontextmenu = this.jmenu.son('div.context_menu');
	jcontextmenu.hide();
}
// 增加一列
$.XNEditor.prototype.table_col_insert = function() {
	var jcontextmenu = this.jmenu.son('div.context_menu');
	jcontextmenu.hide();
	
}

// 删除一列
$.XNEditor.prototype.table_col_remove = function() {
	var jcontextmenu = this.jmenu.son('div.context_menu');
	jcontextmenu.hide();
}*/

// 全局初始化，实例化多个对象的时候节省资源。
$.XNEditor.prototype.global_init = function() {
	// 全局的初始化
	var s = '<div class="editor" style="display: none;">\
			<div class="toolbar">\
				<a class="html" href="javascript:void(0)" title="HTML 源代码"></a>\
				<a class="undo disabled" href="javascript:void(0)" title="上一步"></a>\
				<a class="redo disabled" href="javascript:void(0)" title="下一步"></a>\
				<a class="bold" href="javascript:void(0)" title="加粗"></a>\
				<a class="italic" href="javascript:void(0)" title="倾斜"></a>\
				<a class="underline" href="javascript:void(0)" title="下划线"></a>\
				<a class="fontpg" href="javascript:void(0)">标题</a>\
				<a class="fontsize" href="javascript:void(0)">字号</a>\
				<a class="fontcolor" href="javascript:void(0)" title="字体颜色"></a>\
				<a class="link" href="javascript:void(0)" title="超链接"></a>\
				<a class="unlink" href="javascript:void(0)" title="去除超链接"></a>\
				<a class="justifyleft" href="javascript:void(0)" title="左对齐"></a>\
				<a class="justifycenter" href="javascript:void(0)" title="居中对齐"></a>\
				<a class="justifyright" href="javascript:void(0)" title="右对齐"></a>\
				<a class="justifyfull" href="javascript:void(0)"></a>\
				<a class="image" href="javascript:void(0)" title="上传图片"><input type="file" multiple="multiple" accept=".jpg,.jpeg,.png,.gif,.bmp" /></a>\
				<a class="file" href="javascript:void(0)" title="上传文件"><input type="file" multiple="multiple" /></a>\
				<a class="brush" href="javascript:void(0)" title="清除格式"></a>\
				<a class="insertcode" href="javascript:void(0)" title="插入代码"></a>\
				<a class="table" href="javascript:void(0)" title="插入表格"></a>\
				<a class="fullscreen" href="javascript:void(0)" title="全屏"></a>\
				\
			</div>\
			<div class="body" contenteditable="true" spellcheck="false"><p></p></div>\
			<div class="footer">\
			</div>\
			<div class="menu">\
				<div class="fontpg">\
					<a title="1" href="javascript: void(0)"><h1>H1</h1></a>\
					<a title="2" href="javascript: void(0)"><h2>H2</h2></a>\
					<a title="3" href="javascript: void(0)"><h3>H3</h3></a>\
					<a title="4" href="javascript: void(0)"><h4>H4</h4></a>\
					<a title="5" href="javascript: void(0)"><h5>H5</h5></a>\
					<a title="6" href="javascript: void(0)"><h6>H6</h6></a>\
				</div>\
				<div class="fontsize">\
					<a title="1" href="javascript: void(0)" style="font-size: 12px;">12像素</a>\
					<a title="2" href="javascript: void(0)" style="font-size: 13px;">13像素</a>\
					<a title="3" href="javascript: void(0)" style="font-size: 16px;">16像素</a>\
					<a title="4" href="javascript: void(0)" style="font-size: 18px;">18像素</a>\
					<a title="5" href="javascript: void(0)" style="font-size: 24px;">24像素</a>\
					<a title="6" href="javascript: void(0)" style="font-size: 32px;">32像素</a>\
					<a title="7" href="javascript: void(0)" style="font-size: 48px;">48像素</a>\
				</div>\
				<div class="fontcolor">\
					<a href="javascript: void(0)" title="清除" style="background: #FFFFFF; width: 165px; height: 19px; font-size: 12px; line-height: 19px; text-align: center; padding-left: 0px;">清除</a>\
					<a href="javascript: void(0)" title="#FFFFFF" style="background: #FFFFFF;"></a>\
					<a href="javascript: void(0)" title="#CCCCCC" style="background: #CCCCCC;"></a>\
					<a href="javascript: void(0)" title="#C0C0C0" style="background: #C0C0C0;"></a>\
					<a href="javascript: void(0)" title="#999999" style="background: #999999;"></a>\
					<a href="javascript: void(0)" title="#666666" style="background: #666666;"></a>\
					<a href="javascript: void(0)" title="#333333" style="background: #333333;"></a>\
					<a href="javascript: void(0)" title="#000000" style="background: #000000;"></a>\
					<a href="javascript: void(0)" title="#FFCCCC" style="background: #FFCCCC;"></a>\
					<a href="javascript: void(0)" title="#FF6666" style="background: #FF6666;"></a>\
					<a href="javascript: void(0)" title="#FF0000" style="background: #FF0000;"></a>\
					<a href="javascript: void(0)" title="#CC0000" style="background: #CC0000;"></a>\
					<a href="javascript: void(0)" title="#990000" style="background: #990000;"></a>\
					<a href="javascript: void(0)" title="#660000" style="background: #660000;"></a>\
					<a href="javascript: void(0)" title="#330000" style="background: #330000;"></a>\
					<a href="javascript: void(0)" title="#FFCC99" style="background: #FFCC99;"></a>\
					<a href="javascript: void(0)" title="#FF9966" style="background: #FF9966;"></a>\
					<a href="javascript: void(0)" title="#FF9900" style="background: #FF9900;"></a>\
					<a href="javascript: void(0)" title="#FF6600" style="background: #FF6600;"></a>\
					<a href="javascript: void(0)" title="#CC6600" style="background: #CC6600;"></a>\
					<a href="javascript: void(0)" title="#993300" style="background: #993300;"></a>\
					<a href="javascript: void(0)" title="#663300" style="background: #663300;"></a>\
					<a href="javascript: void(0)" title="#FFFF99" style="background: #FFFF99;"></a>\
					<a href="javascript: void(0)" title="#FFFF66" style="background: #FFFF66;"></a>\
					<a href="javascript: void(0)" title="#FFCC66" style="background: #FFCC66;"></a>\
					<a href="javascript: void(0)" title="#FFCC33" style="background: #FFCC33;"></a>\
					<a href="javascript: void(0)" title="#CC9933" style="background: #CC9933;"></a>\
					<a href="javascript: void(0)" title="#996633" style="background: #996633;"></a>\
					<a href="javascript: void(0)" title="#663333" style="background: #663333;"></a>\
					<a href="javascript: void(0)" title="#FFFFCC" style="background: #FFFFCC;"></a>\
					<a href="javascript: void(0)" title="#FFFF33" style="background: #FFFF33;"></a>\
					<a href="javascript: void(0)" title="#FFFF00" style="background: #FFFF00;"></a>\
					<a href="javascript: void(0)" title="#FFCC00" style="background: #FFCC00;"></a>\
					<a href="javascript: void(0)" title="#999900" style="background: #999900;"></a>\
					<a href="javascript: void(0)" title="#666600" style="background: #666600;"></a>\
					<a href="javascript: void(0)" title="#333300" style="background: #333300;"></a>\
					<a href="javascript: void(0)" title="#99FF99" style="background: #99FF99;"></a>\
					<a href="javascript: void(0)" title="#66FF99" style="background: #66FF99;"></a>\
					<a href="javascript: void(0)" title="#33FF33" style="background: #33FF33;"></a>\
					<a href="javascript: void(0)" title="#33CC00" style="background: #33CC00;"></a>\
					<a href="javascript: void(0)" title="#009900" style="background: #009900;"></a>\
					<a href="javascript: void(0)" title="#006600" style="background: #006600;"></a>\
					<a href="javascript: void(0)" title="#003300" style="background: #003300;"></a>\
					<a href="javascript: void(0)" title="#99FFFF" style="background: #99FFFF;"></a>\
					<a href="javascript: void(0)" title="#33FFFF" style="background: #33FFFF;"></a>\
					<a href="javascript: void(0)" title="#66CCCC" style="background: #66CCCC;"></a>\
					<a href="javascript: void(0)" title="#00CCCC" style="background: #00CCCC;"></a>\
					<a href="javascript: void(0)" title="#339999" style="background: #339999;"></a>\
					<a href="javascript: void(0)" title="#336666" style="background: #336666;"></a>\
					<a href="javascript: void(0)" title="#003333" style="background: #003333;"></a>\
					<a href="javascript: void(0)" title="#CCFFFF" style="background: #CCFFFF;"></a>\
					<a href="javascript: void(0)" title="#66FFFF" style="background: #66FFFF;"></a>\
					<a href="javascript: void(0)" title="#33CCFF" style="background: #33CCFF;"></a>\
					<a href="javascript: void(0)" title="#3366FF" style="background: #3366FF;"></a>\
					<a href="javascript: void(0)" title="#3333FF" style="background: #3333FF;"></a>\
					<a href="javascript: void(0)" title="#000099" style="background: #000099;"></a>\
					<a href="javascript: void(0)" title="#000066" style="background: #000066;"></a>\
					<a href="javascript: void(0)" title="#CCCCFF" style="background: #CCCCFF;"></a>\
					<a href="javascript: void(0)" title="#9999FF" style="background: #9999FF;"></a>\
					<a href="javascript: void(0)" title="#6666CC" style="background: #6666CC;"></a>\
					<a href="javascript: void(0)" title="#6633FF" style="background: #6633FF;"></a>\
					<a href="javascript: void(0)" title="#6600CC" style="background: #6600CC;"></a>\
					<a href="javascript: void(0)" title="#333399" style="background: #333399;"></a>\
					<a href="javascript: void(0)" title="#330099" style="background: #330099;"></a>\
					<a href="javascript: void(0)" title="#FFCCFF" style="background: #FFCCFF;"></a>\
					<a href="javascript: void(0)" title="#FF99FF" style="background: #FF99FF;"></a>\
					<a href="javascript: void(0)" title="#CC66CC" style="background: #CC66CC;"></a>\
					<a href="javascript: void(0)" title="#CC33CC" style="background: #CC33CC;"></a>\
					<a href="javascript: void(0)" title="#993399" style="background: #993399;"></a>\
					<a href="javascript: void(0)" title="#663366" style="background: #663366;"></a>\
					<a href="javascript: void(0)" title="#330033" style="background: #330033;"></a>\
				</div>\
				<div class="imageupload dialog upload">\
					<ul>\
						<li class="clone">\
							<span class="_content"><img src="javascript:void(0)" width="120" height="120" /></span>\
							<span class="_progress_bg">\</span>\
							<span class="_progress_current">\</span>\
							<span class="_progress_title"></span>\
							<span class="red _error"><i class="icon close red"></i><span></span></span>\
							<span class="icon trash _trash"></span>\
						</li>\
					</ul>\
					<p class="center"><button type="button" class="button blue confirm" style="display: none;">确定</button> <button type="button" class="button grey cancel">取消</button></p>\
				</div>\
				<div class="fileupload dialog upload">\
					<ul>\
						<li class="clone">\
							<span class="_content"><img src="javascript:void(0)" width="120" height="120" /></span>\
							<span class="_progress_bg">\</span>\
							<span class="_progress_current">\</span>\
							<span class="_progress_title"></span>\
							<span class="red _error"><i class="icon close red"></i><span></span></span>\
							<span class="icon trash _trash"></span>\
						</li>\
					</ul>\
					<p class="center"><button type="button" class="button blue confirm" style="display: none;">确定</button> <button type="button" class="button grey cancel">取消</button></p>\
				</div>\
				<div class="link dialog" title="插入链接">\
					<p>网址：<input type="text" name="url" style="width: 530px; display: inline-block;" placeholder="http://" /></p>\
					<p>文本：<input type="text" name="text" style="width: 530px; display: inline-block;" placeholder="链接文本" /></p>\
					<p class="center"><button type="button" class="button blue confirm">确定</button> <button type="button" class="button grey cancel">取消</button></p>\
				</div>\
				<div class="table">\
					<table class="tborder">\
						<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>\
						<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>\
						<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>\
						<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>\
						<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>\
						<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>\
						<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>\
						<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>\
						<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>\
						<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>\
						<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>\
						<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>\
					</table>\
				</div>\
				<div class="resize">\
					<span class="point0"></span>\
					<span class="point1"></span>\
					<span class="point2"></span>\
					<span class="point3"></span>\
					<span class="point4"></span>\
					<span class="point5"></span>\
					<span class="point6"></span>\
					<span class="point7"></span>\
				</div>\
				<div class="context_menu context_menu_table">\
					<ul>\
						<li>增加一行</li>\
						<li>增加一列</li>\
						<li>删除当前行</li>\
						<li>删除当前列</li>\
					</ul>\
				</div>\
				<div class="context_menu context_menu_code">\
					<ul>\
						<li value="">无</li>\
						<li value="js">javascript</li>\
						<li value="php">PHP</li>\
						<li value="c">C/C++</li>\
					</ul>\
				</div>\
			</div>\
		</div>\
		';
	this.jclone = $(s).appendTo('body');
}
$.XNEditor.prototype.global_init();