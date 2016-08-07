// 表单快捷键提交 CTRL+ENTER
$('form').keyup(function(e) {
	if((e.ctrlKey && (e.which == 13 || e.which == 10)) || (e.altKey && e.which == 83)) {
		$('form').trigger('submit');
		return false;
	}
});

// 点击响应整行：方便手机浏览
$('.tap').on('click', function() {
	var href = $(this).attr('href');
	window.location = href;
});
// 点击响应整行：导航栏下拉菜单
$('ul.nav > li').on('click', function() {
	var jthis = $(this);
	var href = jthis.children('a').attr('href');
	if(href) window.location = href;
});
// 点击响应整行：，但是不响应 checkbox 的点击
$('.thread input[type="checkbox"]').parents('td').on('click', function(e) {
	e.stopPropagation();
})

// 版主管理：删除
$('.mod-button button.delete').on('click', function() {
	var modtid = $('input[name="modtid"]').checked();
	if(modtid.length == 0) return $.alert('请选择主题');
	$.confirm('确定删除选中的('+modtid.length+')篇主题吗？', function() {
		var tids = xn.implode('_', modtid);
		$.xpost(xn.url('mod-delete-'+tids), function(code, message) {
			if(code != 0) return $.alert(message);
			$.alert(message).delay(1000).location('');
		});
	});
})

// 版主管理：移动
$('.mod-button button.move').on('click', function() {
	var modtid = $('input[name="modtid"]').checked();
	if(modtid.length == 0) return $.alert('请选择主题');
	var select = xn.form_select('fid', forumarr, fid);
	$.confirm('移动版块', function() {
		var tids = xn.implode('_', modtid);
		var newfid = $('select[name="fid"]').val();
		$.xpost(xn.url('mod-move-'+tids+'-'+newfid), function(code, message) {
			if(code != 0) return $.alert(message);
			$.alert(message).delay(1000).location('');
		});
	}, {'body': '<p>选择移动的版块：'+select+'</p>'});
})

// 版主管理：置顶
$('.mod-button button.top').on('click', function() {
	var modtid = $('input[name="modtid"]').checked();
	if(modtid.length == 0) return $.alert('请选择主题');
	var radios = xn.form_radio('top', {"0": "取消置顶", "1": "版块置顶", "3": "全站置顶"});
	$.confirm('置顶主题', function() {
		var tids = xn.implode('_', modtid);
		var top = $('input[name="top"]').checked();
		var postdata = {top: top};
		$.xpost(xn.url('mod-top-'+tids), postdata, function(code, message) {
			if(code != 0) return $.alert(message);
			$.alert(message).delay(1000).location('');
		});
	}, {'body': '<p>置顶范围：'+radios+'</p>'});
})

// 确定框
$('a.confirm').on('click', function() {
	var jthis = $(this);
	var text = jthis.data('confirm-text');
	$.confirm(text, function() {
		window.location = jthis.attr('href');
	})
	return false;
});

// 选中所有
$('input.checkall').on('click', function() {
	var jthis = $(this);
	var target = jthis.data('target');
	jtarget = $(target);
	jtarget.prop('checked', this.checked);
});
/*
jmobile_collapsing_bavbar = $('#mobile_collapsing_bavbar');
jmobile_collapsing_bavbar.on('touchstart', function(e) {
	//var h = $(window).height() - 120;
	var h = 350;
	jmobile_collapsing_bavbar.css('overflow-y', 'auto').css('max-height', h+'px');
	e.stopPropagation();
});
jmobile_collapsing_bavbar.on('touchmove', function(e) {
	//e.stopPropagation();
	//e.stopImmediatePropagation();
});*/