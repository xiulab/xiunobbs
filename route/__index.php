<?php

// hook index_start.php

if(BBS_ROUTE == 'index') {
	include _include(APP_PATH.'view/htm/index.htm');
} elseif(BBS_ROUTE == 'bbs') {
	include _include(APP_PATH.'route/bbs.php');
}

// hook index_end.php

?>