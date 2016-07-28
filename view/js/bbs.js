$('.tap').on('click', function() {
	var href = $(this).attr('href');
	window.location = href;
});
$('li').on('click', function() {
	var jthis = $(this);
	var href = jthis.children('a').attr('href');
	if(href) window.location = href;
});

