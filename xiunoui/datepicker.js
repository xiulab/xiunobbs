/*
* Copyright (C) 2015 xiuno.com
*/

/*
	兼容 zepto jquery 的日历
	用法：
		$('#date1').datepicker();
		$('#date2').datepicker('setdefault', {year: 2015, month: 1, day: 2, time: '24:00'});
		$('#date2').datepicker('setdate', '2015/1/7 23:00');
*/
$.fn.datepicker = function(act, opt) {
	if($('#datepicker').length < 1) {
		$.datepicker.obj = new $.datepicker();
		$.datepicker.obj.init();
	}

	this.each(function() {
		var jthis = $(this);

		// 设置初始值
		if(act == 'setdefault') {
			if(typeof(opt) != 'object') opt = {};
			if(opt.year) jthis.attr('year', opt.year);
			if(opt.month) jthis.attr('month', opt.month);
			if(opt.day) jthis.attr('day', opt.day);
			if(opt.time) jthis.attr('time', opt.time);

		// 设置时间
		} else if(act == 'setdate') {
			if(typeof(opt) != 'string') opt = '';
			var arr = opt.split(' ');
			var d = new Date(arr[0]);
			$.datepicker.element = jthis;
			$.datepicker.obj.set(d.getFullYear(), d.getMonth() + 1, d.getDate(), arr[1]);
		}

		// 已绑定事件直接返回
		if(this.is_on) return true;

		jthis.on('focus', function() {
			// 定位日历框位置
			var o = jthis.offset();
			var h = jthis.height();

			var dw = $(document).width();
			if(o.left + 300 > dw) {
				$('#datepicker').css({top:o.top+o.height+2, left:o.left - (300 - o.width)}).show();
			} else {
				$('#datepicker').css({top:o.top+o.height+2, left:o.left}).show();
			}

			var d = new Date();
			var year = jthis.attr('year') || d.getFullYear();
			var month = jthis.attr('month') || d.getMonth() + 1;
			var day = jthis.attr('day') || d.getDate();
			var time = jthis.attr('time') || '00:00';

			$.datepicker.element = jthis;
			$.datepicker.obj.set(year, month, day, time);
		}).on('click', function() {
			return false;
		}).attr("readonly", "readonly");

		this.is_on = 1; // 设置已绑定事件
	});

	return this;
}

$.datepicker = function() {
	var _this = this;

	_this.init = function() {
		if($('#datepicker').length > 0) return;

		var s = '<div id="datepicker" style="position:absolute; display:none;">\
			<table id="datepicker_head" cellspacing="0" cellpadding="0" width="100%" style="text-align:center">\
				<tr>\
					<td><i class="icon backward datepicker_change" month="-12"></i></td>\
					<td><i class="icon caret-left datepicker_change" month="-1"></i></td>\
					<td><select id="datepicker_year"></select></td>\
					<td><select id="datepicker_month"></select></td>\
					<td><select id="datepicker_time"></select></td>\
					<td><i class="icon caret-right datepicker_change" month="1"></i></td>\
					<td><i class="icon forward datepicker_change" month="12"></i></td>\
				</tr>\
			</table>\
			<table cellspacing="0" cellpadding="0" width="100%" style="text-align:center">\
				<thead id="datepicker_week"><tr><td>日</td><td>一</td><td>二</td><td>三</td><td>四</td><td>五</td><td>六</td></tr></thead>\
				<tbody id="datepicker_calendar"></tbody>\
				<tfoot><tr><td colspan="7"><button id="datepicker_clear" class="red small">清空</button> &nbsp; &nbsp;<button id="datepicker_today" class="blue small">今天</button></td></tr></tfoot>\
			</table>\
		</div>';
		$('body').append(s);

		// 初始时间数据
		var d = new Date();
		_this.date = {year: d.getFullYear(), month: d.getMonth()+1, day: d.getDate(), time: '00:00'};

		_this.jwrap = $('#datepicker');
		_this.jhead = $('#datepicker_head');
		_this.jyear = $('#datepicker_year');
		_this.jmonth = $('#datepicker_month');
		_this.jtime = $('#datepicker_time');
		_this.jcalendar = $('#datepicker_calendar');
		_this.jclear = $('#datepicker_clear');
		_this.jtoday = $('#datepicker_today');

		// 初始某月/某时
		_this.jmonth.html( _this.select_set(1, 12) );
		_this.jtime.html( _this.select_set(0, 24, ':00') );

		// 选择某年/某月/某时
		_this.jhead.on('change', 'select', function() {
			_this.set(_this.jyear.val(), _this.jmonth.val(), _this.date.day, _this.jtime.val());
		});

		// 上下翻 (年/月)
		_this.jhead.on('click', '.datepicker_change', function() {
			_this.set(_this.date.year, parseInt(_this.date.month) + parseInt( $(this).attr('month') ), _this.date.day, _this.date.time);
		});

		// 选择某天
		_this.jcalendar.on('click', 'td', function() {
			var arr = $(this).attr('idate').split('|');
			_this.set(arr[0], parseInt(arr[1]) + 1, arr[2], _this.date.time);
		});

		// 返回今天
		_this.jtoday.on('click', function() {
			var d = new Date();
			_this.set(d.getFullYear(), d.getMonth() + 1, d.getDate(), _this.date.time);
		});

		// 清空表单
		_this.jclear.on('click', function() {
			$.datepicker.element.val('');
		});

		// 隐藏日历框
		$(document).on('click', function(e) {
			_this.jwrap.hide();
		});
		_this.jwrap.on('click', function(e) {
			if($(e.target).is(".datepicker_td")) return;
			return false;
		});
	}

	// 生成日历
	_this.set = function(year, month, day, time) {
		var d = new Date();

		d.setFullYear(year, month-1, day);
		year = d.getFullYear();
		month = d.getMonth();
		day = d.getDate();
		var datestr = year+'|'+month+'|'+day;

		d.setFullYear(year, month, 1);
		var weekday = d.getDay(); // 这个月1号是周几

		d.setFullYear(year, month+1, 0);
		var dayend = d.getDate(); // 这个月最后一天

		var i_end = ceil((weekday + dayend) / 7) * 7;

		var s = '<tr>';
		for(var i = 1; i <= i_end; i++) {
			d.setFullYear(year, month, i-weekday);
			var iyear = d.getFullYear();
			var imonth = d.getMonth();
			var iday = d.getDate();
			var idatestr = iyear+'|'+imonth+'|'+iday;

			var classstr = '';
			if (datestr == idatestr) {
				classstr = ' today';
			} else if (month != imonth) {
				classstr = ' gray';
			}

			s += '<td class="datepicker_td'+classstr+'" idate="'+idatestr+'">'+iday+'</td>';
			if(i%7 == 0) s += '</tr>';
		}
		_this.jcalendar.html(s);

		// 重新生成年份下拉框数据
		_this.jyear.html( _this.select_set(year-6, year+3, '', true) );

		// 补零
		var zmonth = _this.fill_zero(month+1);
		var zday = _this.fill_zero(day);

		// 相关赋值
		_this.jyear.val(year);
		_this.jmonth.val(zmonth);
		_this.jtime.val(time);

		// 设置时间
		_this.date = {year: year, month: month+1, day: day, time: time};

		if($.datepicker.element) {
			$.datepicker.element.attr(_this.date).val(year+'/'+zmonth+'/'+zday+' '+time);
		}
	}

	// 生成下拉框
	_this.select_set = function(num_start, num_end, num_suffix, not_fill_zero) {
		suffix = num_suffix ? num_suffix : '';
		var s = '';
		for(var i = num_start; i <= num_end; i++) {
			var v = (!not_fill_zero ? _this.fill_zero(i) : i) + suffix;
			s += '<option value="'+ v +'">'+ v +'</option>';
		}
		return s;
	}

	// 格式化补零
	_this.fill_zero = function(num) {
		return num < 10 ? '0'+num : num;
	}

	return this;
}
