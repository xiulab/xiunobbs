<?php
replace_path('../css/bootstrap.css.map');
replace_path('../css/bootstrap.min.css.map');
function replace_path($file) {
	$s = file_get_contents($file);
	$s = str_replace('../../scss/', '../bootstrap/scss/', $s);
	file_put_contents($file, $s);
	clearstatcache();
}
echo 'fix scss path ... [done]';
?>