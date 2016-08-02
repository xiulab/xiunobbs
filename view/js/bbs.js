
// 点击响应整行
$('.tap').on('click', function() {
	var href = $(this).attr('href');
	window.location = href;
});

$('ul.nav > li').on('click', function() {
	var jthis = $(this);
	var href = jthis.children('a').attr('href');
	if(href) window.location = href;
});

// 响应整行，但是不响应 checkbox 的点击
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
	var radios = xn.form_radio('fid', {"0": "取消置顶", "1": "版块置顶", "3": "全站置顶"});
	$.confirm('置顶主题', function() {
		var tids = xn.implode('_', modtid);
		$.xpost(xn.url('mod-top-'+tids), function(code, message) {
			if(code != 0) return $.alert(message);
			$.alert(message).delay(1000).location('');
		});
	}, {'body': '<p>置顶范围：'+radios+'</p>'});
})