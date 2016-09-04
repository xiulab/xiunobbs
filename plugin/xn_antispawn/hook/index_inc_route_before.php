<?php exit;

if($method == 'POST') {
	if(param('anti_spam_key') != setting_get('anti_spam_key')) {
		message(-1, '...');
	}
}
?>