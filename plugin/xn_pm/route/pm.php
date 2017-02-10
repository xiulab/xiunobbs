<?php

/*
header("Access-Control-Allow-Origin: http://www.domain.com/"); 
*/

$route = param(1);

user_login_check();

$user = user_read($uid);

// 【新短消息状态】 返回最近联系人列表
// GET /pm-new-{uid}.htm
if($route == 'new') {
        
        // 管理员可能需要此参数，普通用户从 session 中获取 uid
        // $uid = param(2);

        // 获取最新短消息
        if($user['newpms'] > 0) {
                // $recentlist = pm_recent_list($uid);
                message(1, $user['newpms']);
        } else {
                message(0, 0);
        }

// 【最近联系人列表】
// GET /pm-recent_list-{uid}.htm
} elseif($route == 'recent_list') {

        // 管理员可能需要此参数，普通用户从 session 中获取 uid
        // $uid = param(2);

        $recentlist = pm_recent_list($uid);
        
        // 只要点开短消息窗口，就设置新短消息为 0
        if($user['newpms'] > 0) {
                user_update($uid, array('newpms'=>0));
        }

        message(0, $recentlist);

// 【两人的聊天记录】
// GET /pm-list-{recvuid}-{senduid}-{startpmid}.htm
} elseif($route == 'list') {
        
        $uid1 = $recvuid = $uid; // param(2, 0);
        $uid2 = $senduid = param(3, 0);
        $startpmid = param(4, 0);
        
        if($uid1 != $uid && $uid2 != $uid) {
                message(-1, 'user_group_insufficient_privilege');
        }

        $pmlist = pm_list($uid1, $uid2, $startpmid);

        // 获取过就等于已阅读
        if($startpmid == 0) {
                pm_recent_update($recvuid, $senduid, array('count'=>0));
                user_update($recvuid, array('newpms'=>0));
        }

        message(0, $pmlist);

// 【创建新短消息】
// POST /pm-create.htm
// touid={123}&message={mesaage}
} elseif($route == 'create') {

        $touid = param('touid', 0);
        $message = param('message');
        $touser = user_read($touid);
        empty($touser) AND message(-1, lang('user_not_exists'));
        $message_search = pm_cn_encode($message);
        $arr = array(
                'uid1'=>($uid > $touid ? $touid : $uid),
                'uid2'=>($uid > $touid ? $uid : $touid),
                'username1'=>($uid > $touid ? $touser['username'] : $user['username']),
                'username2'=>($uid > $touid ? $user['username'] : $touser['username']),
                'senduid'=>$uid,
                'create_date'=>$time,
                'message'=>$message,
                'message_search'=>$message_search
        );
        $pmid = pm_create($arr);
        $pmid === FALSE AND message(-1, lang('pm_create_failed'));

        //pm_recent_replace($touid, $uid);

        message(0, array('pmid'=>$pmid, 'create_date'=>$time, 'message'=>$message));

// 【删除新短消息】
// POST /pm-delete.htm
// pmid={123}
} elseif($route == 'delete') {

        $pmid = param('pmid');
        $pm = pm_read($pmid);
        empty($pm) AND message(-1, '短消息不存在');
        $pm['uid1'] != $uid && $pm['uid2'] != $uid && message(-1, '没有权限删除');
        pm_delete($pmid);

        message(0, '删除成功');
}

?>