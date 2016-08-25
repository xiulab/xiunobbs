<?php

return array(
	'setting' => array(
		'url'=>url('setting-base'), 
		'text'=>lang('setting'), 
		'icon'=>'icon-cog', 
		'tab'=> array (
			'base'=>array('url'=>url('setting-base'), 'text'=>lang('admin_setting_base')),
			'smtp'=>array('url'=>url('setting-smtp'), 'text'=>lang('admin_setting_smtp')),
		)
	),
	'forum' => array(
		'url'=>url('forum-list'), 
		'text'=>lang('forum'), 
		'icon'=>'icon-comment',
		'tab'=> array (
		)
	),
	/*
	'post' => array(
		'url'=>url('post-list'), 
		'text'=>lang('post_admin'), 
		'icon'=>'icon-comment',
		'tab'=> array (
		)
	),*/
	'user' => array(
		'url'=>url('user-list'), 
		'text'=>lang('user'), 
		'icon'=>'icon-user',
		'tab'=> array (
			'list'=>array('url'=>url('user-list'), 'text'=>lang('admin_user_list')),
			'group'=>array('url'=>url('group-list'), 'text'=>lang('admin_user_group')),
			'create'=>array('url'=>url('user-create'), 'text'=>lang('admin_user_create')),
		)
	),
	'other' => array(
		'url'=>url('other'), 
		'text'=>lang('other'), 
		'icon'=>'icon-circle-blank',
		'tab'=> array (
			'cache'=>array('url'=>url('other-cache'), 'text'=>lang('admin_other_cache')),
		)
	),
	'plugin' => array(
		'url'=>url('plugin'), 
		'text'=>lang('plugin'), 
		'icon'=>'icon-cogs',
		'tab'=> array (
			'local'=>array('url'=>url('plugin-local'), 'text'=>lang('admin_plugin_local_list')),
			'official'=>array('url'=>url('plugin-official'), 'text'=>lang('admin_plugin_official_list')),
		)
	)
);

?>