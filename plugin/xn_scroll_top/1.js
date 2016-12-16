
// 回到顶部
var document_scroll_top = function() {
	var jscroll_top = $('#scroll_top');
	var jwindow_height = $(window).height();
	jscroll_top.off('click').on('click', function() {
		if(document.body.scrollTop) {
			$(document.body).animate({scrollTop: 0}, 120);
		} else if(document.documentElement.scrollTop) {
			$(document.documentElement).animate({scrollTop: 0}, 120);
		}
	});
	var scroll_top_function = function(e) {
		var st = document.body.scrollTop || document.documentElement.scrollTop; // 兼容 ie ff chrome
		if(st > 400) {
			if(!jscroll_top.is_show) jscroll_top.fadeIn();
			jscroll_top.is_show = 1;
		} else {
			jscroll_top.fadeOut();
			jscroll_top.is_show = 0;
		}
	};
	$(document).on("scroll DOMMouseScroll", scroll_top_function);
	scroll_top_function();
}