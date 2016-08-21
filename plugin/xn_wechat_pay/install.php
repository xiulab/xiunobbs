<?php
!defined('DEBUG') AND exit( 'Forbidden' );
$tablepre = $db->tablepre;
$sql = "CREATE TABLE IF NOT EXISTS {$tablepre}user_paylog (
  rid int(10) unsigned NOT NULL AUTO_INCREMENT,
  uid int(10) unsigned NOT NULL DEFAULT '0' COMMENT '用户id',
  type tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '微信支付1',
  rmbs int(10) unsigned DEFAULT '0' COMMENT '金额',
  regdate int(10) unsigned DEFAULT '0' COMMENT '创建时间',
  paydate int(10) unsigned DEFAULT '0' COMMENT '确认时间',
  pay char(32) DEFAULT NULL COMMENT '订单号',
  transaction_id char(32) DEFAULT NULL COMMENT '流水号',
  PRIMARY KEY (rid),
  KEY pay (pay),
  KEY uid (uid,type)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='会员余额明细'";
$r = db_exec($sql);
$r === false AND message(-1, '创建表结构失败'); // 中断，安装失败。
?>