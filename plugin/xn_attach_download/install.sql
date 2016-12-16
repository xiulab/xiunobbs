
#论坛附件表  只能按照从上往下的方式查找和删除！ 此表如果大，可以考虑通过 aid 分区。
DROP TABLE IF EXISTS bbs_attach_down;
CREATE TABLE bbs_attach_down (
  aid int(11) unsigned NOT NULL default '0',		# 附件id
  uid int(11) NOT NULL default '0',			# 用户id
  create_date int(11) unsigned NOT NULL default '0',	# 文件上传时间 UNIX 时间戳
  PRIMARY KEY (aid, uid)				# aid
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

