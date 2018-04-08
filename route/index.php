<?php

// hook index_start.php

if(empty($conf['nav_2_on'])) {
	include _include(APP_PATH.'route/bbs.php');
} else {
	include _include(APP_PATH.'view/htm/index.htm');
}

// hook index_end.php

?>