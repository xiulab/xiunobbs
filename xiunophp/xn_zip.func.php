<?php 

function xn_zip($zipfile, $extdir) { 
	$pathinfo = pathinfo($extdir); 
	$parentpath = $pathinfo['dirname']; 
	$dirname = $pathinfo['basename']; 

	$z = new ZipArchive(); 
	$z->open($zipfile, ZIPARCHIVE::CREATE); 
	$z->addEmptyDir($dirname); 
	xn_dir_to_zip($z, $extdir, strlen("$parentpath/")); 
	$z->close(); 
}

function xn_unzip($zipfile, $extdir){ 
	$z = new ZipArchive;
	if($z->open($zipfile) === TRUE) {
		$z->extractTo($extdir);
		$z->close();
	}
}

function xn_dir_to_zip(&$z, $zippath, $prelen = 0) {
		
	// (PHP 5 >= 5.3.0, PHP 7, PECL zip >= 1.9.0)
	/*
	$zip = new ZipArchive();
	$ret = $zip->open($zipfile, ZipArchive::OVERWRITE);
	if ($ret !== TRUE) {
		printf('Failed with code %d', $ret);
	}else {
		//$options = array('add_path' => 'sources/', 'remove_all_path' => TRUE);
		$options = array('remove_all_path' => TRUE);
		$zip->addGlob($extdir.'/*', GLOB_BRACE, $options);
		$zip->close();
	}
	*/
	substr($zippath, -1) != '/' AND $zippath .= '/';
	$filelist = glob($zippath."*");
	foreach($filelist as $filepath) {
		$localpath = substr($filepath, $prelen); 
		if(is_file($filepath)) { 
			$z->addFile($filepath, $localpath); 
		} elseif(is_dir($filepath)) { 
			$z->addEmptyDir($localpath); 
			xn_dir_to_zip($z, $filepath, $prelen); 
		}
	}
}

// 第一层的目录名称，用来兼容多层打包
function xn_zip_unwrap_path($zippath, $dirname = '') {
	substr($zippath, -1) != '/' AND $zippath .= '/';
	$arr = glob("$zippath*", GLOB_ONLYDIR);
	if(empty($arr)) return $zippath;
	$dirname2 = end(explode(DIRECTORY_SEPARATOR, $arr[0]));
	if(!$dirname && count($arr) == 1) return $arr[0];
	if($dirname && $dirname == $dirname2) {
		return $arr[0];
	} else {
		return $arr[0];
	}
}


//xn_unzip('d:/test/yyy.zip', 'd:/test/yyy/');
//xn_zip('d:/test/yyy.zip', 'd:/test/xxx/xxx');

?>