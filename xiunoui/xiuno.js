/*
* Copyright (C) 2015 xiuno.com
*/

var global = window;

global.jumpdelay = global.debug ? 20000000 : 2000;

if(typeof console == 'undefined') {
	console = {};
	console.log = function() {};
}

// 兼容 IE8
/*
var ua = navigator.userAgent.toLowerCase();
global.is_ie = window.ActiveXObject ? true : false;			// ua.match(/msie ([\d.]+)/)[1]
global.is_ff = document.getBoxObjectFor ? true : false;			// ua.match(/firefox\/([\d.]+)/)[1]
global.is_chrome = window.MessageEvent && !document.getBoxObjectFor;	// ua.match(/chrome\/([\d.]+)/)[1]
global.is_opera = window.opera ? true : false;				// ua.match(/opera.([\d.]+)/)[1]
global.is_safari = window.openDatabase ? true : false;			// ua.match(/version\/([\d.]+)/)[1];
*/

// 针对国内的山寨套壳浏览器检测不准确
global.is_ie = (!!document.all) ? true : false;// ie6789
global.is_ie_10 = navigator.userAgent.indexOf('Trident') != -1;
global.is_ff = navigator.userAgent.indexOf('Firefox') != -1;
global.webgl = ( function () { try { return !! window.WebGLRenderingContext && !! document.createElement( 'canvas' ).getContext( 'experimental-webgl' ); } catch( e ) { return false; } } )();
global.canvas = !!window.CanvasRenderingContext2D;

// 兼容 ie89
if(!Object.keys) {
	Object.keys = function(o) {
		var arr = [];
		for(var k in o) {
			if(o.hasOwnProperty(k)) arr.push(o[k]);
		}
		return arr;
	}
}
Object.first = function(obj) {
	for(var k in obj) return obj[k];
}
Object.length = function(obj) {
	var n = 0;
	for(var k in obj) n++;
	return n;
}
Object.count = function(obj) {
	if(!obj) return 0;
	if(obj.length) return obj.length;
	var n = 0;
	for(k in obj) {
		if(obj.hasOwnProperty(k)) n++;
	}
	return n;
}
Object.sum = function(obj) {
	var sum = 0;
	$.each(obj, function(k, v) {sum += intval(v)});
	return sum;
}

global.htmlspecialchars = function(s) {
	s = s.replace('<', "&lt;");
	s = s.replace('>', "&gt;");
	return s;
}

global.urlencode = function(s) {
	s = encodeURIComponent(s);
	s = strtolower(s);
	return s;
}

global.urldecode = function(s) {
	s = decodeURIComponent(s);
	return s;
}

global.xn_urlencode = function(s) {
	s = encodeURIComponent(s);
	s = s.replace(/_/g, "%5f");
	s = s.replace(/\-/g, "%2d");
	s = s.replace(/\./g, "%2e");
	s = s.replace(/\~/g, "%7e");
	s = s.replace(/\!/g, "%21");
	s = s.replace(/\*/g, "%2a");
	s = s.replace(/\(/g, "%28");
	s = s.replace(/\)/g, "%29");
	s = s.replace(/\%/g, "_");
	return s;
}

global.xn_urldecode = function(s) {
	s = s.replace('_', "%");
	s = decodeURIComponent(s);
	return s;
}

global.nl2br = function(s) {
	s = s.replace("\r\n", "\n");
	s = s.replace("\n", "<br>");
	s = s.replace("\t", "&nbsp; &nbsp; &nbsp; &nbsp; ");
	return s;
}

global.time = function() {
	return intval(Date.now() / 1000);
}

global.intval = function(s) {
	var i = parseInt(s);
	return isNaN(i) ? 0 : i;
}

global.floatval = function(s) {
    if(!s) return 0;
    if(s.constructor === Array) {
        for(var i=0; i<s.length; i++) {
            s[i] = floatval(s[i]);
        }
        return s;
    }
    var r = parseFloat(s);
    return isNaN(r) ? 0 : r;
}

global.isset = function(k) {
	var t = typeof k;
	return t != 'undefined' && t != 'unknown';
}

global.empty = function(s) {
	if(s == '0') return true;
	if(!s) {
		return true;
	} else {
		//$.isPlainObject
		if(s.constructor === Object) {
			return Object.keys(s).length == 0;
		} else if(s.constructor === Array) {
			return s.length == 0;
		}
		return false;
	}
}

global.ceil = Math.ceil;
global.round = Math.round;
global.floor = Math.floor;
global.f2y = function(i, callback) {
	if(!callback) callback = round;
	var r = i / 100;
	return callback(r);
}
global.y2f = function(s) {
	var r = round(intval(s) * 100);
	return r;
}
global.strtolower = function(s) {
	s += '';
	return s.toLowerCase();
}

global.json_type = function(o) {
	var _toS = Object.prototype.toString;
	var _types = {
		'undefined': 'undefined',
		'number': 'number',
		'boolean': 'boolean',
		'string': 'string',
		'[object Function]': 'function',
		'[object RegExp]': 'regexp',
		'[object Array]': 'array',
		'[object Date]': 'date',
		'[object Error]': 'error'
	};
	return _types[typeof o] || _types[_toS.call(o)] || (o ? 'object' : 'null');
};

global.json_encode = function(o) {
	var json_replace_chars = function(chr) {
		var specialChars = { '\b': '\\b', '\t': '\\t', '\n': '\\n', '\f': '\\f', '\r': '\\r', '"': '\\"', '\\': '\\\\' };
		return specialChars[chr] || '\\u00' + Math.floor(chr.charCodeAt() / 16).toString(16) + (chr.charCodeAt() % 16).toString(16);
	};

	var s = [];
	switch (json_type(o)) {
		case 'undefined':
			return 'undefined';
			break;
		case 'null':
			return 'null';
			break;
		case 'number':
		case 'boolean':
		case 'date':
		case 'function':
			return o.toString();
			break;
		case 'string':
			return '"' + o.replace(/[\x00-\x1f\\"]/g, json_replace_chars) + '"';
			break;
		case 'array':
			for (var i = 0, l = o.length; i < l; i++) {
				s.push(json_encode(o[i]));
			}
			return '[' + s.join(',') + ']';
			break;
		case 'error':
		case 'object':
			for (var p in o) {
				s.push('"' + p + '"' + ':' + json_encode(o[p]));
			}
			return '{' + s.join(',') + '}';
			break;
		default:
			return '';
			break;
	}
};

global.json_decode = function(s) {
	if(!s) return null;
	try {
		// 去掉广告代码。这行代码挺无语的，为了照顾国内很多人浏览器中广告病毒的事实。
		// s = s.replace(/\}\s*<script[^>]*>[\s\S]*?<\/script>\s*$/ig, '}');
		if(s.match(/^<!DOCTYPE/i)) return null;
		var json = $.parseJSON(s);
		return json;
	} catch(e) {
		//alert('JSON格式错误：' + s);
		//window.json_error_string = s;	// 记录到全局
		return null;
	}
}

global.arrlist_values = function(arrlist, key) {
	var arr = [];
	$.each(arrlist, function() {
		arr.push(this[key]);
	})
	return arr;
}

global.arrlist_read = function(arrlist, key, value) {
	for(k in arrlist) {
		if(arrlist[k][key] == value) return arrlist[k];
	}
	return false;
}

global.arrlist_delete = function(arrlist, key, value) {
	for(k in arrlist) {
		if(arrlist[k][key] == value) delete arrlist[k];
	}
	return false;
}

/*
global.arrlist_sort = function(arrlist, key, sortby) {
	for(k in arrlist) {
		if(arrlist[k][key] == value) return arrlist[k];
	}
	return false;
}*/

// 方便移植 PHP 代码
global.min = function() {return Math.min.apply(this, arguments);}
global.max = function() {return Math.max.apply(this, arguments);}
global.str_replace = function(s, d, str) {return str.replace(s, d);}
global.strrpos = function(str, s) {return str.lastIndexOf(s);}
global.strpos = function(str, s) {return str.indexOf(s);}
global.substr = function(str, start, len) {
	// 支持负数
	var end = length;
	var length = str.length;
	if(start < 0) start = length + start;
	if(!len) {
		end = length;
	} else if(len > 0) {
		end = start + len;
	} else {
		end = length + len;
	}
	return str.substring(start, end);
}
global.explode = function(sep, s) {return s.split(sep);}
global.implode = function(glur, arr) {return arr.join(glur);}
global.array_merge = function(arr1, arr2) {return arr1 && arr1.__proto__ === Array.prototype && arr2 && arr2.__proto__ === Array.prototype ? arr1.concat(arr2) : $.extend(arr1, arr2);}
// 比较两个数组的差异，在 arr1 之中，但是不在 arr2 中。返回差异结果集的新数组，
global.array_diff = function(arr1, arr2) {
	if(arr1.__proto__ === Array.prototype) {
		var o = {};
		for(var i = 0, len = arr2.length; i < len; i++) o[arr2[i]] = true;
		var r = [];
		for(i = 0, len = arr1.length; i < len; i++) {
			var v = arr1[i];
			if(o[v]) continue;
			r.push(v);
		}
		return r;
	} else {
		var r = {};
		for(k in arr1) {
			if(!arr2[k]) r[k] = arr1[k];
		}
		return r;
	}
}

global.template = function(s, json) {
	//console.log(json);
	for(k in json) {
		var r = new RegExp('\{('+k+')\}', 'g');
		s = s.replace(r, function(match, name) {
			return json[name];
		});
	}

	return s;
}

global.is_mobile = function(s) {
	var r = /^\d{11}$/;
	if(!s) {
		return false;
	} else if(!r.test(s)) {
		return false;
	}
	return true;
}

global.is_email = function(s) {
	var r = /^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/i
	if(!s) {
		return false;
	} else if(!r.test(s)) {
		return false;
	}
	return true;
}

global.is_element = function(obj) {
    return !!(obj && obj.nodeType === 1);
};

/* 
	js 版本的翻页函数
*/
// 用例：pages('user-list-{page}.htm', 100, 10, 5);
global.pages = function (url, totalnum, page, pagesize) {
	if(!page) page = 1;
	if(!pagesize) pagesize = 20;
	var totalpage = ceil(totalnum / pagesize);
	if(totalpage < 2) return '';
	page = min(totalpage, page);
	var shownum = 5;	// 显示多少个页 * 2

	var start = max(1, page - shownum);
	var end = min(totalpage, page + shownum);

	// 不足 $shownum，补全左右两侧
	var right = page + shownum - totalpage;
	if(right > 0) start = max(1, start -= right);
	left = page - shownum;
	if(left < 0) end = min(totalpage, end -= left);

	var s = '';
	if(page != 1) s += '<a href="'+str_replace('{page}', page-1, url)+'">◀</a>';
	if(start > 1) s += '<a href="'+str_replace('{page}', 1, url)+'">1 '+(start > 2 ? '... ' : '')+'</a>';
	for(i=start; i<=end; i++) {
		if(i == page) {
			s += '<a href="'+str_replace('{page}', i, url)+'" class="active">'+i+'</a>';// active
		} else {
			s += '<a href="'+str_replace('{page}', i, url)+'">'+i+'</a>';
		}
	}
	if(end != totalpage) s += '<a href="'+str_replace('{page}', totalpage, url)+'">'+(totalpage - end > 1 ? '... ' : '')+totalpage+'</a>';
	if(page != totalpage) s += '<a href="'+str_replace('{page}', page+1, url)+'">▶</a>';
	return s;
}

global.parse_url = function(url) {
	if(url.match(/^(([a-z]+):)\/\//i)) {
		var arr = url.match(/^(([a-z]+):\/\/)?([^\/\?#]+)\/*([^\?#]*)\??([^#]*)#?(\w*)$/i);
		if(!arr) return null;
		var r = {
		    'schema': arr[2],
		    'host': arr[3],
		    'path': arr[4],
		    'query': arr[5],
		    'anchor': arr[6],
		    'requesturi': arr[4] + (arr[5] ? '?'+arr[5] : '') + (arr[6] ? '#'+arr[6] : '')
		};
		console.log(r);
		return r;
	} else {
		
		var arr = url.match(/^([^\?#]*)\??([^#]*)#?(\w*)$/i);
		if(!arr) return null;
		var r = {
		    'schema': '',
		    'host': '',
		    'path': arr[1],
		    'query': arr[2],
		    'anchor': arr[3],
		    'requesturi': arr[1] + (arr[2] ? '?'+arr[2] : '')  + (arr[3] ? '#'+arr[3] : '')
		};
		console.log(r);
		return r;
	}
}

global.parse_str = function (str){
	var sep1 = '=';
	var sep2 = '&';
	var arr = str.split(sep2);
	var arr2 = {};
	for(var x=0; x < arr.length; x++){
		var tmp = arr[x].split(sep1);
		arr2[unescape(tmp[0])] = unescape(tmp[1]).replace(/[+]/g, ' ');
	}
	return arr2;
}

// 解析 url 参数获取 $_GET 变量
global.parse_url_param = function(url) {
	var arr = parse_url(url);
	var q = arr.path;
	var pos = strrpos(q, '/');
	q = substr(q, pos + 1);
	var r = [];
	if(substr(q, -4) == '.htm') {
		q = substr(q, 0, -4);
		r = explode('-', q);
	// 首页
	} else if (url && url != window.location && url != '.' && url != '/' && url != './'){
		r = ['thread', 'seo', url];
	}

	// 将 xxx.htm?a=b&c=d 后面的正常的 _GET 放到 $_SERVER['_GET']
	if(!empty(arr['query'])) {
		var arr2 = parse_str(arr['query']);
		r = array_merge(r, arr2);
	}
	return r;
}

// 从参数里获取数据
global.param = function(key) {

}

// 二级数组排序
/*var first = function(obj) {for(var k in obj) return k;}
Array.prototype.proto_sort = Array.prototype.sort;
Array.prototype.sort = function(arg) {
	if(arg === undefined) {
		return this.proto_sort();
	} else if(arg.constructor === Function) {
		return this.proto_sort(arg);
	} else if(arg.constructor === Object) {
		var k = first(arg);
		var v = arg[k];
		return this.proto_sort(function(a, b) {return v == 1 ? a[k] > b[k] : a[k] < b[k];});
	} else {
		return this;
	}
}*/
// var arrlist = [{id:1, name:"zhangsan"}, {id:2, name:"lisi"}];
// arrlist.sort(function(a, b) {a.name > b.name});
// arrlist.sort({name:1});
// console.log(arrlist);

if($.is_ie) document.documentElement.addBehavior("#default#userdata");
$.pdata = function(key, value) {
	var r = '';
	if(typeof value != 'undefined') {
		value = json_encode(value);
	}

	// HTML 5
	try {
		// ie10 需要 try 一下
		if(window.localStorage){
			if(typeof value == 'undefined') {
				r = localStorage.getItem(key);
				return json_decode(r);
			} else {
				return localStorage.setItem(key, value);
			}
		}
	} catch(e) {}

	// HTML 4
	if(is_ie && (!document.documentElement || typeof document.documentElement.load == 'unknown' || !document.documentElement.load)) {
		return '';
	}
	// get
	if(typeof value == 'undefined') {
		if(is_ie) {
			try {
				document.documentElement.load(key);
				r = document.documentElement.getAttribute(key);
			} catch(e) {
				//alert('$.pdata:' + e.message);
				r = '';
			}
		} else {
			try {
				r = sessionStorage.getItem(key) && sessionStorage.getItem(key).toString().length == 0 ? '' : (sessionStorage.getItem(key) == null ? '' : sessionStorage.getItem(key));
			} catch(e) {
				r = '';
			}
		}
		return json_decode(r);
	// set
	} else {
		if(is_ie){
			try {
				// fix: IE TEST for ie6 崩溃
				document.documentElement.load(key);
				document.documentElement.setAttribute(key, value);
				document.documentElement.save(key);
				return  document.documentElement.getAttribute(key);
			} catch(error) {/*alert('setdata:'+error.message);*/}
		} else {
			try {
				return sessionStorage.setItem(key, value);
			} catch(error) {/*alert('setdata:'+error.message);*/}
		}
	}
};


// time 单位为秒，与php setcookie, 和  misc::setcookie() 的 time 参数略有差异。
$.cookie = function(name, value, time, path) {
	if(typeof value != 'undefined') {
		if (value === null) {
			var value = '';
			var time = -1;
		}
		if(typeof time != 'undefined') {
			date = new Date();
			date.setTime(date.getTime() + (time * 1000));
			var time = '; expires=' + date.toUTCString();
		} else {
			var time = '';
		}
		var path = path ? '; path=' + path : '';
		//var domain = domain ? '; domain=' + domain : '';
		//var secure = secure ? '; secure' : '';
		document.cookie = name + '=' + encodeURIComponent(value) + time + path;
	} else {
		var v = '';
		if(document.cookie && document.cookie != '') {
			var cookies = document.cookie.split(';');
			for(var i = 0; i < cookies.length; i++) {
				var cookie = $.trim(cookies[i]);
				if(cookie.substring(0, name.length + 1) == (name + '=')) {
					v = decodeURIComponent(cookie.substring(name.length + 1)) + '';
					break;
				}
			}
		}
		return v;
	}
};


// 改变Location URL ?
$.xget = function(url, callback, retry) {
	if(retry === undefined) retry = 1;
	$.ajax({
		type: 'GET',
		url: url,
		dataType: 'text',
		timeout: 15000,
		success: function(r){
			if(!r) return callback(-100, 'Server Response Empty!');
			var s = json_decode(r);
			if(!s) {
				return callback(-101, r); // 'Server Response json_decode() failed：'+
			}
			if(s.code === undefined) {
				if($.isPlainObject(s)) {
					return callback(0, s);
				} else {
					return callback(-102, r); // 'Server Response Not JSON 2：'+
				}
			} else if(s.code == 0) {
				return callback(0, s.message);
			//业务逻辑错误
			} else if(s.code > 0) {
				return callback(s.code, s.message);
			//系统错误
			} else if(s.code < 0) {
				return callback(s.code, s.message);
			}
		},
		// 网络错误，重试
		error: function(xhr, type) {
			if(retry > 1) {
				$.xget(url, callback, retry - 1);
			} else {
				if((type != 'abort' && type != 'error') || xhr.status == 403 || xhr.status == 404) {
					return callback(-1000, "xhr.responseText:"+xhr.responseText+', type:'+type);
				} else {
					return callback(-1001, "xhr.responseText:"+xhr.responseText+', type:'+type);
					console.log("xhr.responseText:"+xhr.responseText+', type:'+type);
				}
			}
		}
	});
}

$.xpost = function(url, postdata, callback) {
	if($.isFunction(postdata)) {
		callback = postdata;
		postdata = null;
	}
	
	$.ajax({
		type: 'POST',
		url: url,
		data: postdata,
		dataType: 'text',
		timeout: 60000,
		success: function(r){
			if(!r) return callback(-1, 'Server Response Empty!');
			var s = json_decode(r);
			if(!s || s.code === undefined) return callback(-1, 'Server Response Not JSON：'+r);
			if(s.code == 0) {
				return callback(0, s.message);
			//业务逻辑错误
			} else if(s.code > 0) {
				return callback(s.code, s.message);
			//系统错误
			} else if(s.code < 0) {
				return callback(s.code, s.message);
			}
		},
		error: function(xhr, type) {
			if(type != 'abort' && type != 'error' || xhr.status == 403) {
				return callback(-1000, "xhr.responseText:"+xhr.responseText+', type:'+type);
			} else {
				return callback(-1001, "xhr.responseText:"+xhr.responseText+', type:'+type);
				console.log("xhr.responseText:"+xhr.responseText+', type:'+type);
			}
		}
	});
}

/*
	异步转同步的方式执行 ajax 请求
	用法：
	$.xget_sync(['1.htm', 'index.htm', '3.htm'], function(code, message, i){
		console.log(i+', code:'+code);
	}, function(code, message) {
		console.log();
	});
*/
$.xget_sync = function(urlarr, once_callback, complete_callback) {
	var arr = [];
	for(var i=0; i<urlarr.length; i++) {
		+function(i) {
			var url = urlarr[i];
			arr.push(function(callback) {
				$.xget(url, function(code, message) {
					once_callback(code, message, i);
					callback(null, {code:code, message:message});
				});
			});
		}(i);
	};
	async.series(arr, function(err, result) {
		if(err) {
			complete_callback(-1, result);
		} else {
			complete_callback(0, result);
		}
	});
}

$.xpost_sync = function(urlarr, postdataarr, once_callback, complete_callback) {
	var arr = [];
	for(var i=0; i<urlarr.length; i++) {
		var url = urlarr[i];
		var postdata = postdataarr[i];
		+function(i, url, postdata, once_callback) {
			arr.push(function(callback) {
				$.xpost(url, postdata, function(code, message) {
					once_callback(code, message, i);
					callback(null, {code:code, message:message});
				});
			});
		}(i, url, postdata, once_callback);
	}
	async.series(arr, function(err, result) {
		if(err) {
			complete_callback(-1, result);
		} else {
			complete_callback(0, result);
		}
	});
}

/*


$.xpost_sync(['1.htm', 'index.htm', '3.htm'], ["a=b", "c=d", "e=f"], function(code, message, i){
	console.log(i+', code:'+code);
}, function(code, message) {
	console.log();
});
*/


/* 标准浏览器支持:
var urlarr = typeof url == 'string' ? [url] : url;
var worker = new Worker('sync_ajax.js');  
worker.onmessage = function(event) {
	console.log("收到消息:" + event.data);
	alert("收到消息:" + event.data);
};
worker.onerror = function(error) {console.log("Error:" + error.message);};
worker.postMessage(a);
*/

/*
	功能：
		异步加载 js, 加载成功以后 callback
	用法：
		$.require('1.js', '2.js', function() {
			alert('after all loaded');
		});
		$.require(['1.js', '2.js'] function() {
			alert('after all loaded');
		});
*/
// 区别于全局的 node.js require 关键字
$.required = [];
$.require = function() {
	var args = null;
	if(arguments[0] && typeof arguments[0] == 'object') { // 如果0 为数组
		args = arguments[0];
		if(arguments[1]) args.push(arguments[1]);
	} else {
		args = arguments;
	}
	this.load = function(args, i) {
		var _this = this;
		if(args[i] === undefined) return;
		if(typeof args[i] == 'string') {
			var js = args[i];
			// 避免重复加载
			if($.inArray(js, $.required) != -1) {
				if(i < args.length) this.load(args, i+1);
				return;
			}
			$.required.push(js);
			var script = document.createElement("script");
			script.src = js;
			script.onerror = function() {
				console.log('script load error:'+js);
				_this.load(args, i+1);
			}
			if(is_ie) {
				script.onreadystatechange = function() {
					if(script.readyState == 'loaded' || script.readyState == 'complete') {
						_this.load(args, i+1);
						script.onreadystatechange = null;
					}
				};
			} else {
				script.onload = function() { _this.load(args, i+1); };
			}
			document.getElementsByTagName('head')[0].appendChild(script);
		} else if(typeof args[i] == 'function'){
			var f = args[i];
			f();
			if(i < args.length) this.load(args, i+1);
		} else {
			_this.load(args, i+1);
		}
	};
	this.load(args, 0);
}

$.require_css = function(filename) {
	// 判断重复加载
	var tags = document.getElementsByTagName('link');
	for(var i=0; i<tags.length; i++) {
		if(tags[i].href.indexOf(filename) != -1) {
			return false;
		}
	}
	
	var link = document.createElement("link");
	link.rel = "stylesheet";
	link.type = "text/css";
	link.href = filename;
	document.getElementsByTagName('head')[0].appendChild(link);
}

// 会阻塞UI线程，尽量不要使用。
/*
global.require = function(jsfile, retry) {
	if(!retry) retry = 3;
	var r;
	$.ajax({
		type: 'GET',
		url: jsfile,
		data: {},
		dataType: 'text',
		async: false,
		timeout: 5000,
		success: function(s){
			r = s;
		},
		error: function(xhr, type) {
			// 重试
			if(retry > 1) {
				return require(jsfile, retry - 1);
			} else {
				console.log("xhr.responseText:"+xhr.responseText+', type:'+type);
			}
		}
	});
	if(!r) {
		return false;
	}
	// 尝试 json_decode
	var s = json_decode(r);
	if(s && $.isPlainObject(s)) {
		if(s.code === undefined) return s; 	// 格式: {a:b}
		if(s.code == 0) {
			return s.message;		 // 格式: {code:0, message:{a:b}}
		} else {
			return null;
		}
	} else {
		// 尝试 CommonJS 标准
		var exports = null;
		var s = r;
		try {
			eval(r);			// 格式: exports = {a:b};
		} catch(e) {
			console.log(e.message);
		}
		return exports;
		//return null;
	}
}
*/

// 在节点上显示 loading 图标
$.fn.loading = function(action) {
	return this.each(function() {
		var jthis = $(this);
		jthis.css('position', 'relative');
		if(!this.jloading) this.jloading = $('<div class="loading"><img src="static/loading.gif" /></div>').appendTo(jthis);
		var jloading = this.jloading.show();
		if(!action) {
			var offset = jthis.position();
			var left = offset.left;
			var top = offset.top;
			var w = jthis.width();
			var h = min(jthis.height(), $(window).height());
			var left = w / 2 - jloading.width() / 2;
			var top = (h / 2 -  jloading.height() / 2) * 2 / 3;
			jloading.css('position', 'absolute').css('left', left).css('top', top);
		} else if(action == 'close') {
			jloading.remove();
			this.jloading = null;
		}
	});
}


// eval script

// 获取当前已经加载的 js
global.get_loaded_script = function () {
	var arr = [];
	$('script[src]').each(function() {
		arr.push($(this).attr('src'));
	});
	return arr;
}
global.get_script_src = function (s) {
	var arr = [];
	var r = s.match(/<script[^>]*?src=\s*\"([^"]+)\"[^>]*><\/script>/ig);
	if(!r) return arr;
	for(var i=0; i<r.length; i++) {
		var r2 = r[i].match(/<script[^>]*?src=\s*\"([^"]+)\"[^>]*><\/script>/i);
		arr.push(r2[1]);
	}
	return arr;
}
global.get_stylesheet_link = function (s) {
	var arr = [];
	var r = s.match(/<link[^>]*?href=\s*\"([^"]+)\"[^>]*>/ig);
	if(!r) return arr;
	for(var i=0; i<r.length; i++) {
		var r2 = r[i].match(/<link[^>]*?href=\s*\"([^"]+)\"[^>]*>/i);
		arr.push(r2[1]);
	}
	return arr;
}
global.strip_script_src = function (s) {
	s = s.replace(/<script[^>]*?src=\s*\"([^"]+)\"[^>]*><\/script>/ig, '');
	return s;
}
global.strip_stylesheet_link = function (s) {
	s = s.replace(/<link[^>]*?href=\s*\"([^"]+)\"[^>]*>/ig, '');
	return s;
}

global.strip_script_section = function (s) {
	s = s.replace(/<script(\s*type="text\/javascript")?\s*>([\s\S]+?)<\/script>/ig, '');
	return s;
}

global.get_script_section = function (s) {
	var r = '';
	var arr = s.match(/<script(\s*type="text\/javascript")?\s*>([\s\S]+?)<\/script>/ig);
	return arr;
}

// eval_script 并且传递参数，第二个 key 是为了避免重复 new Function()
global.eval_script = function (arr, key, args) {
	if(!arr) return;
	if(key === undefined) key = '0';
	if(args === undefined) args = null;
	
	if(!$.eval_script_functions) $.eval_script_functions = {};
	if(!$.eval_script_functions[key]) $.eval_script_functions[key] = [];
	
	for(var i=0; i<arr.length; i++) {
		var s = arr[i].replace(/<script(\s*type="text\/javascript")?\s*>([\s\S]+?)<\/script>/i, '$2');
		try {
			if($.eval_script_functions[key][i]) {
				func = $.eval_script_functions[key][i];
			} else {
				var func = new Function('args', s);
				$.eval_script_functions[key][i] = func;
			}
			func(args);
			//func = null;
			//func.call(window, args); // 放到 windows 上执行会有内存泄露!!!
		} catch(e) {
			console.log("eval_script() error: %o, script: %s", e, s);
			alert(s);
		}
	}
}

global.get_title_body_script_css = function (s) {
	var s = $.trim(s);
	
	var title = '';
	var body = '';
	var script_sections = get_script_section(s);
	var stylesheet_links = get_stylesheet_link(s);
	
	var arr1 = get_loaded_script();
	var arr2 = get_script_src(s);
	var script_srcs = array_diff(arr2, arr1); // 避免重复加载 js
	
	s = strip_script_src(s);
	s = strip_script_section(s);
	s = strip_stylesheet_link(s);
	
	var r = s.match(/<title>([^<]+?)<\/title>/i);
	if(r && r[1]) title = r[1];
	
	var r = s.match(/<body[^>]*>([\s\S]+?)<\/body>/i);
	if(r && r[1]) body = r[1];
	
	// jquery 更方便
	var jtmp = $('<div>'+body+'</div>');
	var t = jtmp.find('div.ajax_body');
	if(t.length == 0) t = jtmp.find('div#body');
	if(t.length > 0)  body = t.html();
	
	/*
	for(var i=0; i<jtmp.length; i++) { 
		var jeq = jtmp.eq(i);
		var t = jeq.find('div[id="body"]');
		if(t.length == 0) t = jeq.filter('div[id="body"]');
		if(t.length == 0) t = jeq.filter('div.ajax_body');
		if(t.length == 0) t = jeq.find('div.ajax_body');
		if(t.length > 0) { 
			body = t.html();
			break;
		}
	}*/
	
	if(!body) body = s;
	if(body.indexOf('<meta ') != -1) {
		console.log('加载的数据有问题：body: %s: ', body);
		body = '';
	}
	jtmp.remove();

	return {title: title, body: body, script_sections: script_sections, script_srcs: script_srcs, stylesheet_links: stylesheet_links};
}

global.eval_stylesheet = function(arr) {
	if(!arr) return;
	if(!$.required_css) $.required_css = {};
	for(var i=0; i<arr.length; i++) {
		if($.required_css[arr[i]]) continue;
		$.require_css(arr[i]);
	}
}

// 获取所有的 父节点集合，一直到最顶层节点为止。, IE8 没有 HTMLElement
global.nodeHasParent = function(node, topNode) {
	if(!topNode) topNode = document.body;
	var pnode = node.parentNode;
	while(pnode) {
		if(pnode == topNode) return true;
		pnode = pnode.parentNode;
	};
	return false;
}

/*window.onerror = function(msg, url, line) {
	alert("error: "+msg+"\r\n line: "+line+"\r\n url: "+url);
	return false;
}*/

// remove() 并不清除子节点事件！！用来替代 remove()，避免内存泄露
$.fn.removeDeep = function() {
	 this.each(function() {
		$(this).find('*').off();
	});
	this.off();
	this.remove();
	return this;
}

// empty 清楚子节点事件，释放内存。
$.fn.emptyDeep = function() {
	this.each(function() {
		$(this).find('*').off();
	});
	this.empty();
	return this;
}

/*
	link 与 弹出的 div 对象的相对位置示意图: 1 - 9 表示它的周围的位置，再加一个 center, 默认屏幕居中。
	1	2	3
	4	jlink	6
	7	8	9
*/
// jlink 与 jdiv 在同一个 offsetParent() 下
$.link_div_position = function (jlink, jdiv, position, sub_parent_offset) {
	
	if(typeof position == 'object') return position;
	// 如果不指定 jlink，一般就是相对 windows 绝对居中了。
	if(!jlink || jlink.length == 0) {
		var link_left = 0;
		var link_top = 0;
		var link_width = 0;
		var link_height = 0;
	} else {
		var offset = jlink.offset();
		var jpnode = jlink.offsetParent();
		var poffset = jpnode.offset();
		console.log("%o, %o, %o, %o", jpnode, poffset, offset, jlink.position());
		var link_left = sub_parent_offset ? offset.left - poffset.left : offset.left; //  - poffset.left
		var link_top = sub_parent_offset ? offset.top - poffset.top : offset.top;; //  - poffset.top
		var link_width = jlink.width();
		var link_height = jlink.height();
	}
	
	var div_width = jdiv.width();
	var div_height = jdiv.height();
	var window_width = $(window).width();
	var window_height = $(window).height();
	if(div_width == 0) return false; // 不显示的时候宽度为0，不需要调整。
	
	var left = 0;
	var top = 0;
	
	if(position == 1) {
		left = link_left - div_width;
		top = link_top - div_height - 2;
	} else if(position == 2) {
		left = link_left - div_width / 2;
		top = link_top - div_height - 2;
	} else if(position == 3) {
		left = link_left + link_width;
		top = link_top - div_height - 2;
	} else if(position == 4) {
		left = link_left - div_width;
		top = link_top - div_height / 2;
	} else if(position == 5) {
		left = link_left - div_width / 2;
		top = link_top - div_height / 2;
	} else if(position == 6) {
		left = link_left + link_width;
		top = link_top;
	} else if(position == 7) {
		left = link_left - div_width;
		top = link_top + link_height + 2;
	} else if(position == 8) {
		left = link_left - div_width / 2;
		top = link_top + link_height + 2;
	} else if(position == 9) {
		left = link_left + link_width;
		top = link_top + link_height + 2;
	} else if(position == 'center') {
		// 相对于父窗口的绝对居中
		top = ((window_height / 2 - div_height / 2) * 0.7) + $(window).scrollTop();
		left = (window_width / 2 - div_width / 2);
		if(top < 0) top = 10;
	}
	left = Math.max(0, left);
	top = Math.max(0, top);
	return {left: left, top: top};
}

$.fn.son = $.fn.children;

// 得到选中的值，设置选中项，支持 select checkbox radio
$.fn.select_option = function(v) {
	return this.each(function() {
		$(this).find('option').filter(function() {return this.value == v}).prop('selected', true);
	});
}


/*
	用来选中和获取 select radio checkbox 的值，用法：
	$('#select1').checked(1);			// 设置 value="1" 的 option 为选中状态
	$('#select1').checked();			// 返回选中的值。
	$('input[type="checkbox"]').checked([2,3,4]);	// 设置 value="2" 3 4 的 checkbox 为选中状态
	$('input[type="checkbox"]').checked();		// 获取选中状态的 checkbox 的值，返回 []
	$('input[type="radio"]').checked(2);		// 设置 value="2" 的 radio 为选中状态
	$('input[type="radio"]').checked();		// 返回选中状态的 radio 的值。
*/
$.fn.checked = function(v) {
	// 转字符串
	if(v) v = v instanceof Array ? v.map(function(vv) {return vv+""}) : v + "";
	var filter = function() {return !(v instanceof Array) ? (this.value == v) : ($.inArray(this.value, v) != -1)};
	// 设置
	if(v) {
		this.each(function() {
			if(strtolower(this.tagName) == 'select') {
				$(this).find('option').filter(filter).prop('selected', true);
			} else if(strtolower(this.type) == 'checkbox' || strtolower(this.type) == 'radio') {
				// console.log(v);
				$(this).filter(filter).prop('checked', true);
			}
		});
		return this;
	// 获取，值用数组的方式返回
	} else {
		if(this.length <= 0) return [];
		var tagtype = strtolower(this[0].tagName) == 'select' ? 'select' : strtolower(this[0].type);
		var r = (tagtype == 'checkbox' ? [] : '');
		for(var i=0; i<this.length; i++) {
			var tag = this[i];
			if(tagtype == 'select') {
				var joption = $(tag).find('option').filter(function() {return this.selected == true});
				if(joption.length > 0) return joption.attr('value');
			} else if(tagtype == 'checkbox') {
				if(tag.checked) r.push(tag.value);
			} else if(tagtype == 'radio') {
				if(tag.checked) return tag.value;
			}
		}
		return r;
	}
}

// 鼠标离开 obj(<A>) 后，几秒消失, 消失后回调 recall
$.fn.mouseout_hide = function(timeout, jobj, recall) {
	if(!timeout) { return this;}
	return this.each(function() {
		var jthis = $(this);
		// 如果有 obj, 一般为A标签，则鼠标放在A标签上时不启动定时器。
		if(jobj) {
			jobj.on('mouseover', function() { if(jthis.htime) { clearTimeout(jthis.htime); jthis.htime = false; } return false; });
			jobj.on('mouseout', function() {
				if(jthis.htime) { clearTimeout(jthis.htime); jthis.htime = false; }
				if(!jthis.htime) { jthis.htime = setTimeout(function() { jthis.fadeOut(); jthis.htime = false; if(recall) recall();}, timeout); return false;}
			});
		// 否则直接启动定时器
		} else {
			jthis.htime = setTimeout(function() { jthis.fadeOut(); jthis.htime = false;}, timeout);
		}
		jthis.on('mouseover', function() { if(jthis.htime) { clearTimeout(jthis.htime); jthis.htime = false; } return false; });
		jthis.on('mouseout', function() { 
			if(jthis.htime) { clearTimeout(jthis.htime); jthis.htime = false; }
			if(!jthis.htime) { jthis.htime = setTimeout(function() { jthis.fadeOut(); jthis.htime = false; if(recall) recall();}, timeout); return false;}
		});
	});
};

// 将菜单浮动于 this 对象的周围, pos 默认为 8
/*
	1		2		3
	4		<this>		6
	7		8		9
*/
$.fn.menu_show = function(menuid, pos, timeout) {
	return this.each(function() {
		
		var jthis = $(this);
		var jmenu = $(menuid);
		jmenu.css({position: "absolute", top: '-1000px', left : '-1000px', 'z-index':10000}).show();
		if(!pos) pos = 8;
		var offset2 = $.link_div_position(jthis, jmenu, pos, true);
		if(!offset2) return;
		jmenu.css(offset2);
		jmenu.fadeIn('fast');
		if(timeout) jmenu.mouseout_hide(timeout, jthis);
	});
};

// 提示行错误
$.fn.line_ok = function() {
	if(is_ie) return this;
	return this.css('background', '#00AA33').animate({'background': '#FFFFFF'}, 1000, 'linear');
}
$.fn.line_error = function() {
	if(is_ie) return this;
	return this.css('background', 'red').animate({'background': 'transparent'}, 1000, 'linear');
}
// 将当前节点设置为 class="active"
$.fn.class_active = function() {
	var jnode = this.eq(0);
	jnode.siblings().removeClass('active');
	jnode.addClass('active');
	return this;
}

$.fn.slide = function() {

}

// 标准宽度，兼容 jquery.js, zepto.js
$.fn.w = function(w) {
	if(w === undefined) {
		return intval(this[0].style.width);
	} else {
		this[0].style.width = w+'px';
		return this;
	}
}
$.fn.h = function(h) {
	if(h === undefined) {
		return intval(this[0].style.height);
	} else {
		this[0].style.height = h - intval(this[0].style.marginTop) + intval(this[0].style.marginBottom) + 'px';
		console.log("height: %d, marginTop: %d, marginBottom: %d, obj:%o, obj:%o", this[0].style.height, this[0].style.marginTop, this[0].style.marginBottom, this[0], this[0].style);
		return this;
	}
}

$.fn.button = function(status) {
	return this.each(function() {
		if(status == 'loading') {
			$(this).prop('disabled', true).addClass('disabled').attr('default-text', $(this).text());
			$(this).html($(this).attr('loading-text'));
		} else if(status == 'disabled') {
			$(this).prop('disabled', true).addClass('disabled');
		} else if(status == 'enable') {
			$(this).prop('disabled', false).removeClass('disabled');
		} else if(status == 'reset') {
			$(this).prop('disabled', false).removeClass('disabled');
			if($(this).attr('default-text')) {
				$(this).text($(this).attr('default-text'));
			}
		} else {
			$(this).text(status);
		}
	});
}

$.fn.popover = function(message, pos, classname) {
	if(!pos) pos = 'top';
	classname = classname ? ' ' + classname : '';
	var jthis = $(this);
	if(jthis.length == 0) return;
	var offset = jthis.position(); // offsetParent

	var width = jthis.width();
	var height = jthis.height();

	var left = offset.left;
	var top = offset.top;

	// 创建一个代码片段 ie67 *display: inline; _display: inline
	/*
	var jmessage = $('<div style="display: table;">'+message+'</div>').appendTo('body');
	var w = jmessage.width();
	var h = jmessage.height();
	jmessage.remove();
	*/

	// 清理掉重复的 节点
	
	// 创建 popover
	var jpopover = $('<div class="popover'+classname+'" style="display: block"><span class="arrow '+pos+'"></span>'+message+'</div>').insertAfter(jthis);
	var pop_width = jpopover.width();
	var pop_height = jpopover.height();

	if(pos == 'top') {
		jpopover.css({left: left, top: top - pop_height - 2});
	} else if(pos == 'bottom') {
		jpopover.css({left: left, top: top + height + 4});
	} else if(pos == 'left') {
		jpopover.css({left: left - pop_width - 2, top: top + (height > pop_height ? ((height - pop_height) / 2) : 0)});
	} else if(pos == 'right') {
		jpopover.css({left: left + width + 2, top: top + (height > pop_height ? ((height - pop_height) / 2) : 0)});
	}
	jthis.on('change keyup click blur', function() {
		jpopover.remove();
	});
	this.jpopover = jpopover;
	return this;
}

$.fn.fadeIn = function(ms, complete){
	ms = ms || 250;
	$(this).show().css({opacity: 0}).animate({opacity: 1}, ms, complete);
	return this;
}

$.fn.fadeOut = function(ms, complete){
	if(!ms) ms = 250;
	$(this).animate({opacity: 0}, ms, complete);
	return this;
}

$.fn.serializeObj = function() {
	var formobj = {};
	$([].slice.call(this.get(0).elements)).each(function() {
		var jthis = $(this);
		var type = jthis.attr('type');
		var name = jthis.attr('name');
		if (name && strtolower(this.nodeName) != 'fieldset' && !this.disabled && type != 'submit' && type != 'reset' && type != 'button' &&
		((type != 'radio' && type != 'checkbox') || this.checked)) {
			// 还有一些情况没有考虑, 比如: hidden 或 text 类型使用 name 数组时
			if(type == 'radio' || type == 'checkbox') {
				if(!formobj[name]) formobj[name] = [];
				formobj[name].push(jthis.val());
			}else{
				formobj[name] = jthis.val();
			}
		}
	})
	return formobj;
}

// 美化滚动条
$.fn.xn_scroll = function() {
	return this.each(function() {
		var _this = this;
		var jthis = $(this);
		var jfirst = jthis.children('div').eq(0);
		var first = jfirst[0];
		var wrap_height = jthis.height();
		var height = first.scrollHeight;
		jthis.css('position', 'relative').css('overflow', 'hidden');
		jfirst.height(jthis.height()).css('overflow', 'hidden');
		
		if(height <= wrap_height) return; 
		
		var jscrollbar = $('<div class="scrollbar"></div>').appendTo(jthis).height(100);
		var jscrollrail = $('<div class="scrollrail"></div>').appendTo(jthis);
		var scrollbar = jscrollbar[0];
		
		var scale = wrap_height / height;
		var scrollbar_height = wrap_height * scale;
		jscrollbar.height(scrollbar_height);
		jscrollbar.css('top', (_this.scrollTop * scale) + 'px');
		
		// 键盘翻页
		function scroll_keyup(e) {
			var scrolltop = intval(first.scrollTop);
			if(e.keyCode == 32) {
				if(e.shiftKey) {
					first.scrollTop = scrolltop - 200;
					jscrollbar.css('top', (first.scrollTop * scale) + 'px');
				} else {
					first.scrollTop = scrolltop + 200;
					jscrollbar.css('top', (first.scrollTop * scale - 4) + 'px');
				}
			} else if(e.keyCode == 33) {
				first.scrollTop = scrolltop - wrap_height;
				jscrollbar.css('top', (first.scrollTop * scale) + 'px');
			} else if(e.keyCode == 34) {
				first.scrollTop = scrolltop + wrap_height;
				jscrollbar.css('top', (first.scrollTop * scale - 4) + 'px');
			}
			e.stopPropagation();
		}
		
		jthis.on('mouseenter', function(e) {
			jscrollrail.fadeIn();
			jscrollbar.fadeIn();
			$(document).on('keyup.scroll', function(e) {scroll_keyup(e);})
		});
		jthis.on('mouseleave', function(e) {
			//if(e.target != first) return;
			jscrollrail.fadeOut();
			jscrollbar.fadeOut();
			$(document).off('keyup.scroll');
		});
		jfirst.on('mousewheel DOMMouseScroll', function(e) {
			var scrolltop = intval(first.scrollTop);
			var delta = parseInt(e.wheelDelta || -e.detail || e.originalEvent.wheelDelta || -e.originalEvent.detail);
			if(delta > 0) {
				first.scrollTop = scrolltop - 60;
				jscrollbar.css('top', (first.scrollTop * scale) + 'px');
			} else {
				first.scrollTop = scrolltop + 60;
				jscrollbar.css('top', (first.scrollTop * scale - 4) + 'px');
			}
		});
		function scroll_mousedown(e) {
			scrollbar.startdrag = 1;
			$('body').addClass('unselect');
			scrollbar.mouse_offset_y = e.pageY - scrollbar.offsetTop;

			$(document).on('mousemove'+'.scrollbar', function(e) {scroll_mousemove(e)});
			$(document).on('mouseup'+'.scrollbar', function(e) {scroll_mouseup(e)});
		}
		function scroll_mousemove(e) {
			if(scrollbar.startdrag) {
				var y = e.pageY - scrollbar.mouse_offset_y;
				if(y < 0) y = 0;
				if(y >= wrap_height - scrollbar_height - 0) y = wrap_height - scrollbar_height - 0;
				first.scrollTop = y / scale + 10; // 这里误差10个像素，不知道怎么来的。
				jscrollbar.css('top', y);
			}
		}
		function scroll_mouseup(e) {
			$(document).off('mousemove'+'.scrollbar');		// 比较耗费资源，用完 unbind 掉。
			$(document).off('mouseup'+'.scrollbar');			// 比较耗费资源，用完 unbind 掉。
			scrollbar.startdrag = 0;
			$('body').removeClass('unselect');
		}
		/*
		function scroll_touchstart(e) {
			var touch = e.touches[0];
			scrollbar.touch_start_y = touch.pageY;
			scrollbar.scroll_top = first.scrollTop;
			console.log("touchstart: %o", e);
		}
		function scroll_touchmove(e) {
			if(!scrollbar.touch_start_y) return;
			var y1 = scrollbar.touch_start_y;
			var touch = event.touches[0];
			var y2 = touch.pageY;
			var n = y2 - y1; // 移动的距离
			jscrollbar.hide();
			jscrollrail.hide();
			// 滚动
			first.scrollTop = scrollbar.scroll_top - n;
			console.log("touchmove: %o", e);
			e.preventDefault();
		}
		function scroll_touchend(e) {
			scrollbar.touch_start_y = 0;
		}
		jfirst.on('touchstart', function(e) {scroll_touchstart(e)});
		jfirst.on('touchmove', function(e) {scroll_touchmove(e)});
		jfirst.on('touchend', function(e) {scroll_touchend(e)});
		*/
		jfirst.css('-webkit-overflow-scrolling', 'touch');
		jscrollbar.on('mousedown', function(e) {scroll_mousedown(e)});
		
	});
}

// 美化菜单 <select></select>

// 继承，抛弃掉。
/*
if(typeof Object.create !== 'function') {
	Object.create = function (o) {
		function F() {}
		F.prototype = o;
		return new F();
	};
}
var MyClass = Object.create(OldClass);
*/


// 更好的方案，也是符合 OP 思想的方案，直接使用 js prototype __proto__  constructor 关键词来实现继承

/*
var A = function(arg) {this.age = 999;};
A.prototype.age = 123;
A.prototype.method1 = function() {alert('method1, age:'+this.age)};
A.prototype.method2 = function() {};
A.prototype.method3 = function() {};

var AA = function(arg) {A.call(this, arg);};
AA.prototype.__proto__ = A.prototype;
//AA.prototype.constructor = A;
AA.prototype.method4 = function() {};
AA.prototype.method5 = function() {};
AA.prototype.method6 = function() {};
var aa = new AA(456);
aa.method1();
*/