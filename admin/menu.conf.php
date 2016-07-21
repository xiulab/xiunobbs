<?php

return array(
	'setting' => array(
		'url'=>url('admin/setting-base'), 
		'text'=>lang('setting'), 
		'icon'=>'icon-cog', 
		'tab'=> array (
			'base'=>array('url'=>url('admin/setting-base'), 'text'=>lang('admin_setting_base')),
			'smtp'=>array('url'=>url('admin/setting-smtp'), 'text'=>lang('admin_setting_smtp')),
		)
	),
	'user' => array(
		'url'=>url('admin/user-list'), 
		'text'=>lang('user_admin'), 
		'icon'=>'icon-user',
		'tab'=> array (
			'list'=>array('url'=>url('admin/user-list'), 'text'=>lang('admin_user_list')),
			'group'=>array('url'=>url('admin/group-list'), 'text'=>lang('admin_user_group')),
			'create'=>array('url'=>url('admin/user-create'), 'text'=>lang('admin_user_create')),
		)
	),
	'post' => array(
		'url'=>url('admin/post-list'), 
		'text'=>lang('post_admin'), 
		'icon'=>'icon-comment',
		'tab'=> array (
		)
	),
	'other' => array(
		'url'=>url('admin/post'), 
		'text'=>lang('other_admin'), 
		'icon'=>'icon-circle-blank',
		'tab'=> array (
		)
	),
	'plugin' => array(
		'url'=>url('admin/plugin'), 
		'text'=>lang('plugin_admin'), 
		'icon'=>'icon-cogs',
		'tab'=> array (
		)
	)
);

?>