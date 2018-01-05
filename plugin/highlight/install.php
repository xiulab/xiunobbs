<?php

/*
	Xiuno BBS 4.0 插件实例：高亮主题安装
	admin/plugin-install-highlight.htm
*/

!defined('DEBUG') AND exit('Forbidden');

# 高亮主题，高亮风格表
$tablepre = $db->tablepre;
$sql = "CREATE TABLE IF NOT EXISTS {$tablepre}subject_style (
	`id`  int(10) NOT NULL AUTO_INCREMENT , 
	`name`  varchar(50) NOT NULL , 
	`style`  varchar(50) NOT NULL , 
	PRIMARY KEY (`id`) 
)ENGINE=MyISAM DEFAULT CHARSET=utf8;
;";
db_exec($sql);

$sql = "INSERT INTO `bbs_subject_style` VALUES ('1', '红色', '#DC143C');
INSERT INTO `bbs_subject_style` VALUES ('2', '蓝色', '#4169E1');
INSERT INTO `bbs_subject_style` VALUES ('3', '绿色', '#3CB371');
INSERT INTO `bbs_subject_style` VALUES ('4', '金色', '#FFD700');
INSERT INTO `bbs_subject_style` VALUES ('5', '紫色', '#8A2BE2');";
db_exec($sql);

$sql = "ALTER TABLE {$tablepre}thread ADD COLUMN style_id int(2) NOT NULL default '0';";
db_exec($sql);
?>