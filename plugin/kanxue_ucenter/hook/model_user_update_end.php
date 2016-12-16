<?php exit;


/*
array(
	"uid": 123,
	"articles": 123,	// 文章数
	"threads": 123,		// 主题数
	"posts": 123456,	// 回帖数
	"digests": 12,		// 精华数
	"avatar_url": "http://passport.kanxue.com/upload/avatar/000/123.png",
	"create_date": "2016-1-2",
	"credits": 123,		// 积分 
	"golds": 123,		// 看雪币

	// 需要权限的字段，根据 token 判断，自己或者管理员可以获取以下信息
	"email": "abc@gmail.com",
	"brief": "个人描述",
	"pms": 123,	// 短消息数
	"rmbs": 123,	// 人民币余额 
	
	"sign": 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',	// 签名算法： unset($_POST['sign']); ksort($_POST); md5(http_build_query($_POST).$conf['auth_key'])
)
*/

$keyarr = array('uid'=>0, 'threads'=>0, 'posts'=>0, 'digests'=>0, 'avatar_url'=>0, 'credits'=>0, 'golds'=>0, 'pms'=>0, 'rmbs'=>0);
$update = array_intersect_key($arr, $keyarr);
if(!empty($update)) {
	kanxue_ucenter_user_update($uid, $update);
}