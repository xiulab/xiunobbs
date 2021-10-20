<?php
$latestThread = mythread_find(array('uid' => $uid), array('tid' => -1), 1, 1);
if (!empty($latestThread)) {
    $thread = thread_read($latestThread[0]['tid']);
    $cur_time = $_SERVER['time'];
    $lan = $_SERVER['lang'];
    if (date('Y-n-j', $thread['create_date']) == date('Y-n-j', $cur_time)) {
        message(-1, 'come back tomorrow');
    }
}
?>