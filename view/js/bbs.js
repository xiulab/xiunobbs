
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

