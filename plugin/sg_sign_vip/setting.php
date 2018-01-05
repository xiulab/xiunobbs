<?php

/*
	Xiuno BBS 4.0 每日签到设置
*/

!defined('DEBUG') AND exit('Access Denied.');
if($method == 'GET') {
	$kv = kv_get('sg_sign');
	$input = array();
	$input['sign1'] = form_text('sign1', $kv['sign1']);
	$input['sign2'] = form_text('sign2', $kv['sign2']);
	$input['sign3'] = form_text('sign3', $kv['sign3']);
	$input['sign4'] = form_text('sign4', $kv['sign4']);
	$input['sign5'] = form_text('sign5', $kv['sign5']);
	$input['sign6'] = form_text('sign6', $kv['sign6']);
	$input['sign7'] = form_text('sign7', $kv['sign7']);
	$input['sign8'] = form_text('sign8', $kv['sign8']);
	$input['sign9'] = form_select('sign9',array('credits'=>'credits '.lang('sg_sign1'), 'golds'=>'golds '.lang('sg_sign2'), 'rmbs'=>'rmbs '.lang('sg_sign3')), $kv['sign9']);
	$input['sign10'] = form_select('sign10',array('08599E'=>lang('sg_sign31'), 'c62f2f'=>lang('sg_sign32'), '009a61'=>lang('sg_sign33'), 'FA884F'=>lang('sg_sign34')), $kv['sign10']);
	$input['sign11'] = form_radio_yes_no('sign11', $kv['sign11']);
	include _include(APP_PATH.'plugin/sg_sign_vip/htm/setting.htm');
} else {
	$kv = array();
	$kv['sign1'] = param('sign1', 0);
	$kv['sign2'] = param('sign2', 0);
	$kv['sign3'] = param('sign3', 0);
	$kv['sign4'] = param('sign4', 0);
	$kv['sign5'] = param('sign5', 0);
	$kv['sign6'] = param('sign6', 0);
	$kv['sign7'] = param('sign7', 0);
	$kv['sign8'] = param('sign8', 0);
	$kv['sign9'] = param('sign9', '', FALSE);
	$kv['sign10'] = param('sign10', '', FALSE);
	$kv['sign11'] = param('sign11', 0);
	kv_set('sg_sign', $kv);
	message(0, lang('save_successfully'));
}
?>