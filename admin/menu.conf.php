<?php

return array(
	'setting' => array(
		'url'=>'admin/'.url('setting'), 
		'text'=>lang('setting'), 
		'icon'=>'icon-cog', 
		'tab'=> array (
			'base'=>array('url'=>'admin/'.url('setting-base'), 'text'=>lang('admin_setting_base')),
			'smtp'=>array('url'=>'admin/'.url('setting-smtp'), 'text'=>lang('admin_setting_smtp')),
		)
	),
	'user' => array(
		'url'=>'admin/'.url('user'), 
		'text'=>lang('user_admin'), 
		'icon'=>'icon-user',
		'tab'=> array (
			'list'=>array('url'=>'admin/'.url('user-list'), 'text'=>lang('admin_user_list')),
			'group'=>array('url'=>'admin/'.url('user-group'), 'text'=>lang('admin_user_group')),
			'create'=>array('url'=>'admin/'.url('user-create'), 'text'=>lang('admin_user_create')),
		)
	),
	'post' => array(
		'url'=>'admin/'.url('post'), 
		'text'=>lang('post_admin'), 
		'icon'=>'icon-comment',
		'tab'=> array (
		)
	),
	'other' => array(
		'url'=>'admin/'.url('post'), 
		'text'=>lang('other_admin'), 
		'icon'=>'icon-circle-blank',
		'tab'=> array (
		)
	),
	'plugin' => array(
		'url'=>'admin/'.url('plugin'), 
		'text'=>lang('plugin_admin'), 
		'icon'=>'icon-cogs',
		'tab'=> array (
		)
	)
);

?>