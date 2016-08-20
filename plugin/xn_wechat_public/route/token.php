<?php
define('DEBUG', 0);
define('BASE_HREF', '../../../');
define('SKIP_ROUTE', true); // 跳过路由处理，否则 index.php 中会中断流程
chdir('../../../');
$openid = true;
include './index.php';
include './plugin/xn_wechat_public/model/wechat.class.php';

$wechat_class = new wechat($conf);
$echostr = param('echostr');
if ( !empty( $echostr ) ) {
	echo $wechat_class->valid();
} else {
	echo $wechat_class->responseMsg();
}
?>