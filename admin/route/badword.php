<?php

!defined('DEBUG') AND exit('Access Denied.');

include './xiunophp/form.func.php';

$action = param(1);

if($method == 'GET') {
		
	$badwords = kv_get('badwords');
	$input_badword_on = form_radio_yes_no('badword_on', $conf['badword_on']);

	$badwords = badword_implode(':', ' ', $badwords);
	
	include './admin/view/badword.htm';
	
} elseif($method == 'POST') {

	$badwords = param('badwords');
	$badword_on = param('badword_on', 0);
	$badwords = str_replace("　 ", ' ', $badwords);
	$badwords = str_replace("：", ':', $badwords);
	$badwords = str_replace(": ", ':', $badwords);
	$badwords = preg_replace('#\s+#is', ' ', $badwords);
	
	$badwordarr = badword_explode(':', ' ', $badwords);
	
	kv_set('badwords', $badwordarr);
	conf_set('badword_on', $badword_on);
	
	message(0, '保存成功');
}


?>