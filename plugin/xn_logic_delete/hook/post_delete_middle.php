<?php exit;

	// 是否为逻辑删除
	$ishard = param(3, 0);

	if($isfirst) {
		if($ishard) {
			$group['allowharddelete'] AND thread_delete($tid);
		} else {
			thread_logic_delete($tid);
		}
	} else {
		if($ishard) {
			$group['allowharddelete'] AND post_delete($pid);
		} else {
			post_logic_delete($pid);
		}
	}

	// hook post_delete_end.php
	
	message(0, lang('delete_successfully'));
	
	exit;

?>