// 处理移动后导致的，一个 tid 在不同的 fid
var pure_view_tids = function() {
	var tids = $.pdata('view_tids');
	var arr = {}; // {tid: [fid, time], tid: [fid, time]}
	var changed = 0;
	for(var fid in tids) {
		for(var tid in tids[fid]) {
			var time = tids[fid][tid];
			if(arr[tid]) {
				if(arr[tid][1] > time) {
					delete tids[fid][tid];
					arr[tid] = [arr[tid][0], time];
				} else {
					delete tids[arr[tid][0]][tid];
					arr[tid] = [fid, time];
				}
			} else {
				arr[tid] = [fid, time];
			}
		}
	}
	$.pdata('view_tids', tids);
}

// 查找几天内的查看过的 tid，返回格式： {"123": 1234567890, "124": 1234567809}
var get_view_tids = function(fid, tids) {
	var fid = fid || 0;
	var tids = tids || $.pdata('view_tids');
	if(!tids) return {};
	if(fid) {
		return tids[fid] ? tids[fid] : {};
	} else {
		var r = {};
		for(var _fid in tids) r = array_merge(r, tids[_fid]);
		return r;
	}
}

// 获取最新的 tids ，返回格式： {"123": 1234567890, "124": 1234567809}
var get_new_tids = function(fid) {
	if(fid) {
		if(!forumlist[fid]) return {};
		return forumlist[fid]['newtids'];	
	} else {
		var r = {};
		for(var k in forumlist) {
			r = array_merge(r, forumlist[k]['newtids']);
		}
		return r;
	}
}

// 比较差集
var diff_new_tids = function(new_tids, view_tids) {
	var r = {};
	for(var tid in new_tids) {
		if(!view_tids[tid] || (view_tids[tid] < new_tids[tid])) {
			r[tid] = new_tids[tid];
		}
	}
	return r;
}

// 保存 view_tid
function save_view_tid(fid, tid, last_date) {

	// 去除异常的 tid
	pure_view_tids();
	
	if(!new_thread_days) new_thread_days = 3; // 默认3天

	var now = time();
	
	var view_tids = $.pdata('view_tids');
	if(!view_tids) view_tids = {};
	if(!view_tids[fid]) view_tids[fid] = {};
	view_tids[fid][tid] = last_date;
	$.pdata('view_tids', view_tids);
	
	// 干掉其他日期的记录，只保留 N 天内的帖子，去除重复（移动导致的）
	for(_fid in view_tids) {
		for(_tid in view_tids[_fid]) {
			if(now - view_tids[_fid][_tid] >  new_thread_days * 86400) {
				delete view_tids[_fid][_tid];
			}
		}
	}
	
}