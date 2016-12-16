
// 禁止上传可疑文件
if(empty($filetype)) {
	$filetype = attach_type($name, $filetypes);
}

if($filetype == 'image' && strpos($data, '<'.'?'.'php') !== FALSE) {
	message(-1, 'Forbidden');
}