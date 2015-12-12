/*
	backbone.js 太复杂，自己实现简单的 route, model 即可
*/

// 渲染左侧
var jforumlist = $('#forumlist');
var jthreadlist = $('#threadlist');
var render_forumlist = function() {
	var view_tids_pdata = $.pdata('view_tids');
	var total = 0;
	jforumlist.find('i.newthreads').each(function() {
		var jthis = $(this);
		var fid = jthis.attr('fid');
		if(fid == 0) return;
		var view_tids = get_view_tids(fid, view_tids_pdata);
		var new_tids = forumlist[fid] ? forumlist[fid]['newtids'] : {};
		var r = diff_new_tids(new_tids, view_tids); // 比较差异
		var len = Object.count(r);
		if(len > 0) {
			jthis.text(min(99, len)).show();
		} else {
			jthis.hide();
		}
		total += len;
	});
	// 首页等于总和
	
	if(total == 0) {
		jforumlist.find('i.newthreads[fid="0"]').hide();
	} else {
		jforumlist.find('i.newthreads[fid="0"]').show().text(total);
	}
}

// 渲染中间
var render_threadlist = function(fid) {
	
	// 从首页点击进入到详情页 thread-123.htm fid 会变为非 0
	var fid2 = fid;
	if(jforumlist.find('li[fid="0"]').hasClass('active')) {
		fid2 = 0;
	}
	var view_tids = get_view_tids(fid2, $.pdata('view_tids'));
	var ftids = get_new_tids(fid2);
	jthreadlist.find('div.thread').each(function() {
		var jthis = $(this);
		var tid = jthis.attr('tid');
		var last_date = intval(jthis.attr('last_date'));
		if(ftids[tid] && (!view_tids[tid] || view_tids[tid] < last_date)) {
			jthis.addClass('new');
		} else {
			jthis.removeClass('new');
		}
	});
	
	thread_list_script();
}

/*
// 表单提交
var jsrch_form = $('#srch_form');
jsrch_form.on('submit', function() {
	var href = 'search.htm?keyword='+jsrch_form.son('input').val();
	route_go(href, true);
	return false;
});
jsrch_form.find('i').on('click', function() {jsrch_form.submit();});
*/

// 详情页依赖的 js
function post_list_script(fid) {
	var jform = $('#quick_post_form');
	var jsubmit = jform.find('button[type="submit"]');
	var jmessage = jform.find('textarea');
	var jpostlist = $('#postlist');
	
	var myagreelist = $.pdata('myagreelist');
	if(!myagreelist) myagreelist = {};
	
	if(myagreelist) {
		jpostlist.find('dl[pid]').add('#firstpost').each(function() {
			var jline = $(this);
			var pid = jline.attr('pid');
			var jagree = jline.find('i.agree');
			var jagreed = jline.find('i.agreed');
			if(myagreelist[pid]) {
				jagree.hide();jagreed.show();
			} else {
				jagree.show();jagreed.hide();
			}
		});
	}
	$('#firstpost').find('a.delete').on('click', function() {
		var jthis = $(this);
		var href = jthis.attr('href');
		$.confirm('你确定删除主题吗？', function() {
			$.xpost(href, function(code, message) {
				if(code == 0) {
					$.alert('删除成功。');
					setTimeout(function() {
						window.location = 'mobile/forum-'+fid+'.htm'; // 跳转到主题列表
					}, 500);
				} else {
					$.alert(message);
				}
			});
		});
		return false;
	});
	// 删除
	jpostlist.on('click', 'a.delete', function() {
		var jthis = $(this);
		var href = jthis.attr('href');
		var jline = jthis.parents('dl[pid]');
		var pid = jline.attr('pid');
		$.confirm('你确定删除吗？', function() {
			$.xpost(href, function(code, message) {
				if(code == 0) {
					jline.next('p.hr').removeDeep();
					jline.removeDeep();
					
					var jposts = $('#posts');
					jposts.text(intval(jposts.text()) - 1);
					
					if(myagreelist) {
						delete myagreelist[pid];
						$.pdata('myagreelist', myagreelist);
					}
				
				} else {
					$.alert(message);
				}
			});
		});
		return false;
	});
	// 喜欢
	
	jpostlist.add('#firstpost').on('click', 'i.agree,i.agreed', function() {
		var jthis = $(this);
		var href = jthis.attr('href');
		var jline = jthis.parents('dl[pid]');
		var pid = jline.attr('pid');
		var jagrees = jthis.siblings('a.agrees');
		var jagree = jline.find('i.agree');
		var jagreed = jline.find('i.agreed');
		$.xpost('agree-update-'+pid+'.htm', function(code, message) {
			if(code == 0) {
				jagree.hide();jagreed.show();
				jagrees.text(intval(jagrees.text()) + 1);
				myagreelist[pid] = time();
				$.pdata('myagreelist', myagreelist);
			} else if(code == 1) {
				jagree.show();jagreed.hide();
				jagrees.text(intval(jagrees.text()) - 1);
				delete myagreelist[pid];
				$.pdata('myagreelist', myagreelist);
			} else {
				$.alert(message);
			}
		});
		return false;
	});
	
	if(!window.allowpost) {
		jmessage.attr('readonly', 'readonly').attr('placeholder', '您无权在此版块发帖');
		jsubmit.button('disabled');
	}
	
	// 看全部回复/只看喜欢的切换
	/*
	var postlist_title = $('#postlist_title');
	var jspans = postlist_title.son('span');
	jspans.eq(0).on('click', function() {
		jspans.eq(0).addClass('active');
		jspans.eq(2).removeClass('active');
		jpostlist.find('dl[pid]').each(function() {
			var jline = $(this);
			jline.show();
		});
	});
	jspans.eq(2).on('click', function() {
		jspans.eq(2).addClass('active');
		jspans.eq(0).removeClass('active');
		jpostlist.find('dl[pid]').each(function() {
			var jline = $(this);
			var agrees = intval(jline.find('a.agrees').text());
			if(agrees == 0) {
				jline.hide();
				jline.next('p.hr').hide();
			}
		});
	});
	*/
	
	jform.on('submit', function() {
	//jsubmit.on('click', function() {
		jsubmit.button('loading');
		var postdata = jform.serialize();
		
		$.xpost(jform.attr('action'), postdata, function(code, message) {
			if(code == 0) {
				var on_post_create = function(code, message) {
					var jform = $('#quick_post_form');
					var jsubmit = jform.find('button[type="submit"]');
					var jmessage = jform.find('textarea').focus();
					
					//var jdl = $(message).insertBefore('#post_create_dl');
					var jdl = $(message).appendTo('#postlist');
					jdl.son('dl').line_ok();
					jmessage.val('');
					
					var jposts = $('#posts');
					jposts.text(intval(jposts.text()) + 1);
					var jposts2 = $('#threadlist').find('div[tid="'+tid+'"]').find('span.posts');
					jposts2.text(intval(jposts2.text()) + 1);
					setTimeout(function() {jsubmit.button('reset');}, 500);
				}
				on_post_create(code, message);
				return;
			} else if(code == 1) {
				jsubject.popover(message).focus();
			} else if(code == 2) {
				jmessage.popover(message).focus();
			} else {
				$.alert(message);
			}
			jsubmit.button('reset');
		});
		return false;
	});
	
	save_view_tid(fid, tid, thread.last_date);
}


// 列表页依赖的 js
function thread_list_script() {
	var jthreadlist = $('#threadlist');	
	var jmod_top = $('#mod_top');	
	var jmod_move = $('#mod_move');	
	var jmod_delete = $('#mod_delete');
	var jcheckall = $('#threadlist_checkall');
	jmod_top.off('click').on('click', function() {
		var jtid = jthreadlist.find('input[name="tid"]');
		var tids = [];
		jtid.map(function() {if(this.checked) tids.push(this.value);});
		
		// ajax_dialog 发送参数
		var jdialog = $.ajax_dialog('mobile/mod-top-'+tids.join('_')+'.htm', false);
		
	});
	jmod_move.off('click').on('click', function() {
		var jtid = jthreadlist.find('input[name="tid"]');
		var tids = [];
		jtid.map(function() {if(this.checked) tids.push(this.value);});
		
		// ajax_dialog 发送参数
		var jdialog = $.ajax_dialog('mobile/mod-move-'+tids.join('_')+'.htm', false);
		
	});
	jmod_delete.off('click').on('click', function() {
		var jtid = jthreadlist.find('input[name="tid"]');
		var tids = [];
		jtid.map(function() {if(this.checked) tids.push(this.value);});
		
		// ajax_dialog 发送参数
		var jdialog = $.ajax_dialog('mobile/mod-delete-'+tids.join('_')+'.htm', false);
		
	});	
	jthreadlist.find('input[type="checkbox"]').on('click', function(e) {
		e.stopPropagation();
	});
	jcheckall.off('click').on('click', function() {
		jthreadlist.find('input[type="checkbox"]').prop('checked', this.checked);
	});
	
	jthreadlist.find('div.thread').on('click', function() {window.location=$(this).attr('href');});
}