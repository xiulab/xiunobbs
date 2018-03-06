<?php
//0:全部 1:通知 2:评论 3:主题  4:私信 
return array(
	0 => array(
		'url'=>url('my-notice'), 
		'name'=>lang('notice_lang_all'), 
		'class'=>'info',
		'icon'=>''
	),
	1 => array(
		'url'=>url('my-notice-1'), 
		'name'=>lang('notice_lang_notice'),
		'class'=>'info',
		'icon'=>''
	),
	2 => array(
		'url'=>url('my-notice-2'), 
		'name'=>lang('notice_lang_comment'),
		'class'=>'primary',
		'icon'=>''
	),
	3 => array(
		'url'=>url('my-notice-3'), 
		'name'=>lang('notice_lang_system'),
		'class'=>'danger',
		'icon'=>''
	),
	// hook notice_route_menu_array_end.php
	99 => array(
		'url'=>url('my-notice-99'), 
		'name'=>lang('notice_lang_other'),
		'class'=>'success',
		'icon'=>'bell'
	),
);?>