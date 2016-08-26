		$hide1 = param('hide1', 0);
		$hide2 = param('hide2', 0);
		$r = thread_update($tid, array('hide1'=>$hide1, 'hide2'=>$hide2));