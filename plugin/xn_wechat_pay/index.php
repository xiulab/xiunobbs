<?php
define('DEBUG', 0);
define('BASE_HREF', '../../');
define('SKIP_ROUTE', true); // 跳过路由处理，否则 index.php 中会中断流程
chdir('../../');
include './index.php';
empty( $user ) AND http_location(url('user-login'));
include './plugin/xn_wechat_pay/model/wechatpay.class.php';
include './plugin/xn_wechat_pay/route/index.php';
?>