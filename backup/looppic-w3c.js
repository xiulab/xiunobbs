$.fn.looppic = function(args) {
	if(!args.big || args.big.length == 0) return this;
	
	var jthis = $(this);
	var jplayer =  $('<div class="player"></div>').appendTo(jthis);
	var jthumber = $('<div class="thumber"></div>').appendTo(jthis); // args.jthumb_container ? args.jthumb_container : jthis
	
	for(var i=0; i<args.big.length; i++) {
		jplayer.append('<div class="wrap" style="background: '+ args.bgcolor[i] + ';'+(i == 0 ? 'display:block; opacity: 1' : '')+'"><div class="loop" style="background-image:url('+args.big[i]+'); ">'+args.inner[i]+'</div></div>');
		jthumber.append('<span class="icon circle thumb"></span>');
	}
	
	var t; 				// 定时器句柄
	var t2;
	var total = args.big.length; 	// 总张数
	var last = 0; 			// 最后播放的序号
	var jthumbs = jthumber.find('span');
	var jbigs = jplayer.find('div.loop');
	var jwraps = jplayer.find('div.wrap');
	var jwrap_height = jwraps.height();
	var jwrap_width = jwraps.width();
	var loop = function(i) {
		var n = typeof i != 'undefined' ? i : last + 1; // 播放哪一个？
	        if(n >= total) n = 0;
        	jwraps.eq(last).css({left: "0px"}).show().animate({left:"-"+jwrap_width+"px"});
        	jwraps.eq(n).css({left: jwrap_width+"px"}).show().animate({left:"0px"});
	        jthumbs.removeClass('active').eq(n).addClass('active').css('opacity', 100);
		last = n;
	}
	
	jthumber.find('span.thumb').each(function(i) {
		var jthumb = $(this);
		jthumb.on('click', function() {
			// 上一张隐藏，切换其他张
	            	if(last == i) return;
	        	loop(i);
	        	
	        	// 清理掉，重新计时
	        	clearInterval(t);
	        	clearTimeout(t2);
	        	t2 = setTimeout(function() {
	        		t = setInterval(function() {loop();}, 3000);
	        	}, 3000);
		});
	});
	jplayer.children('div').each(function(i) {
		var jbig = $(this);
		jbig.on('click', function() {
			window.location = args.link[i];
		});
	});
	
	jthumbs.eq(0).addClass('active');
	t = setInterval(function() {loop();}, 3000);
	
	return this;
};

/* 轮播用法：

<style>
#loop_play {
	width: 1015px;
	height: 409px;
	overflow: hidden;
	transition: all 2s;
	-ms-transition: all 2s;
	-moz-transition: all 2s;
	-webkit-transition: all 2s;
	-o-transition: all 2s;
	margin: auto;
}
#loop_play div.player {
	width: 1015px;
	height: 409px;
	position: relative;
	margin: auto;
}
#loop_play div.thumber {
	text-align: center;
	z-index: 1001;
	position: relative;
	top: -80px;
}
#loop_play span.thumb {
	background: #000000;
	width: 192px;
	height: 80px;
	display: inline-block;
	margin-right: 1 px;
}
#loop_play span.thumb img {
	cursor:pointer;
	opacity: 0.7;
}
#loop_play span.thumb.active img{
	border: 1px solid #EE0000;
}
#loop_play span.thumb {
	margin-right: 1px;
}
#loop_play div.wrap {
	width: 100%;
	position: absolute;
	left: 0px;
	top: 0px;
	z-index: 100;
	display: none;
}
#loop_play div.loop {
	min-width: 1004px;
	max-width: 1440px;
	height: 600px;
	margin: auto;
	cursor: pointer;
}
</style>

<div id="loop_play" class="loop_play"></div>

<script>
var args = {
	"big":["/upload/diy/street-5/loop-0.jpg","/upload/diy/street-5/loop-1.jpg","/upload/diy/street-5/loop-2.jpg"],
	"thumb":["/upload/diy/street-5/loop-0.jpg","/upload/diy/street-5/loop-1.jpg","/upload/diy/street-5/loop-2.jpg"],
	"bgcolor":["#ffffff","#ffffff","#ffffff"],
	"bgimage":["","",""],
	"link":["/item-1.htm","/item-2.htm","/item-3.htm"],
	"inner":["","",""]
};
$('#loop_play').looppic(args);
</script>

*/