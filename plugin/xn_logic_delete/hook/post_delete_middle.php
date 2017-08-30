<?php exit;

	if($isfirst) {
		thread_logic_delete($tid);
	} else {
		post_logic_delete($pid);
	}

	// hook post_delete_end.php
	
	message(0, lang('delete_successfully'));
	
	exit;

?>