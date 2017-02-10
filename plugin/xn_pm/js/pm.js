/*
$('#exampleModal').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget) // Button that triggered the modal
  var recipient = button.data('whatever') // Extract info from data-* attributes
  // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
  // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
  var modal = $(this)
  modal.find('.modal-title').text('New message to ' + recipient)
  modal.find('.modal-body input').val(recipient)
});
*/



// 组件
Vue.component('comp-pm', {
	props: ['pm', 'is_last'],
	template: '#tpl_pm',
	methods: {
	  	delete_pm: function(pm) {
	  		if(!window.confirm('确定删除？')) return;
                        $.xpost(xn.url('pm-delete'), {pmid: pm.pmid}, function(code, message) {
                                if(code == 0) {
                                        var index = app.pmlist.indexOf(pm); 
			                app.pmlist.splice(index, 1);
                                } else {
                                        alert(message);
                                }
                        });
			//app.pmlist = app.pmlist.filter(t => t.pmid != 5)
			//app.pmlist.$remove(pm); // vue 2.0 废弃
	  	}
  	}
});

app = new Vue({
	el: '#pm_dialog',
	data: {
                uid: uid,
                cuid: cuid,
                cusername: cusername,
                recentlist: g_recentlist,
                pmlist: [],
                textarea: '',
	},
	/*computed: {
                recentlist: function() {
                        return recentlist;
                },
		pmlist: function() {
                        return g_pmlist["uid_"+uid];
		}
	},*/
	methods: {
                change_user: function(uid) {
                        this.cuid = uid;
                        this.cusername = '';
                        this.pmlist = g_pmlist["uid_"+uid];
                },
		create_pm: function(pmid) {
			/*
                        var newpm = {"pmid": 6, "uid": 1, "username": "Jack", "user_avatar_url": "view/img/1.png", "create_date": 1200000000, "message": this.textarea};
			this.pmlist.push(newpm);
                        this.textarea = '';
                        this.$refs.textarea.focus();
                        */
                        var postdata = {
                                touid: 123,
                                message: this.textarea,
                        };
                        var that = this;
                        $.xpost(xn.url('pm-create'), postdata, function(code, message) {
                                var pmid = message.pmid;
                                var newpm = {
                                        pmid: message.pmid,
                                        uid: user.uid,
                                        username: user.username,
                                        user_avatar_url: user.avatar_url,
                                        create_date: xn.time(),
                                        message: message.message
                                }
                                that.pmlist.push(newpm);
                                that.textarea = '';
                        });
		}
	},
        updated: function() {

	},
        mounted: function() {
                this.change_user(1);
        }
});
//app.change_user(1);



// 定时请求数据
$.xn_loop_get = function(url, callback) {
        var t = null;
        var default_delay = 4;
        var delay = default_delay;
        var request = function() {
                if(t) {
                        clearTimeout(t);
                        t = null;
                }
                t = setTimeout(function() {
                        $.xget(url, function(code, message) {
                                // 表示没有消息，延长请求的时间
                                if(code == 0) {
                                        delay *= 2;
                                } else if(code == 1) {
                                        delay = default_delay;
                                        callback(message);
                                } else {
                                        delay *= 4;
                                }
                                console.log(message);
                                request();
                        });
                }, delay * 1000);
        }
        request();
}

// 定时请求是否有新消息。更新图标
$.xn_loop_get(xn.url('pm-new'), function(message) {
        // 更新状态

        // 如果发现对话框弹出了，则更新最近列表
        if(message > 0 && !$('#pm_dialog').is(':hidden')) {
                $.xget(xn.url('pm-recent_list'), function(code, message) {
                        g_recentlist = message;
                });
        }
});

$('#pm_dialog').modal('show');