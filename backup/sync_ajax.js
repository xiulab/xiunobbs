var json_decode = function(s) {
	if(!s) return null;
	try {
		// 去掉广告代码。这行代码挺无语的，为了照顾国内很多人浏览器中广告病毒的事实。
		// s = s.replace(/\}\s*<script[^>]*>[\s\S]*?<\/script>\s*$/ig, '}');
		var json = JSON.parse(s);
		return json;
	} catch(e) {
		//alert('JSON格式错误：' + s);
		//window.json_error_string = s;	// 记录到全局
		return null;
	}
}

var json_type = function(o) {
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

var json_encode = function(o) {
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

/*

ReadyState取值	描述
0 描述一种"未初始化"状态；此时，已经创建一个XMLHttpRequest对象，但是还没有初始化。
1 描述一种"发送"状态；此时，代码已经调用了XMLHttpRequest open()方法并且XMLHttpRequest已经准备好把一个请求发送到服务器。
2 描述一种"发送"状态；此时，已经通过send()方法把一个请求发送到服务器端，但是还没有收到一个响应。
3 描述一种"正在接收"状态；此时，已经接收到HTTP响应头部信息，但是消息体部分还没有完全接收结束。
4 描述一种"已加载"状态；此时，响应已经被完全接收。
*/
onmessage = function(event) {
	var urlarr = event.data;
	for(var i=0; i<urlarr.length; i++) {
		+function() {
			var url = urlarr[i];
			var r = new XMLHttpRequest();
			r.onreadystatechange = function() {
				if (r.readyState == 4) {
					if(r.status == 200) {
						var s = r.responseText;
						postMessage(s);
					} else {
						postMessage(r.status);
					}
				}
			};
			r.open("GET", url, false); // 同步
			r.send();	
		}();
	}
};



/*sync_get(["1.htm", "2.htm"], function(code, message) {}, function(code, message) {

});

sync_post(["1.htm", "2.htm"], ["a=b", "a=2"], function(code, message) {}, function(code, message) {

});

function a() {
	console.log('test');
}

var worker = new Worker('fibonacci.js');  
worker.onmessage = function(event) {
	console.log("收到消息:" + event.data);
	alert("收到消息:" + event.data);
};
worker.onerror = function(error) {console.log("Error:" + error.message);};
worker.postMessage(a);

*/