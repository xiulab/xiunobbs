<?php exit;

// 判断是否有权限查看
$fid = $thread['fid'];
$tid = $thread['tid'];
if($thread['deleted']) {
        if(!forum_access_mod($fid, $gid, 'allowdelete') || !$group['allowharddelete']) {
                message(-1, '主题不存在');
        }

}
?>