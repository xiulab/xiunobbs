<?php

/*
* Copyright (C) 2015 xiuno.com
*/

function form_radio_yes_no($name, $checked = 0) {
	$checked = intval($checked);
	return form_radio($name, array(1=>'是', 0=>'否'), $checked);
}

function form_radio($name, $arr, $checked = 0) {
	empty($arr) && $arr = array('否', '是');
	$s = '';

	foreach((array)$arr as $k=>$v) {
		$add = $k == $checked ? ' checked="checked"' : '';
		$s .= "<label><input type=\"radio\" name=\"$name\" value=\"$k\" class=\"noborder\"$add />$v</label> &nbsp; \r\n";
	}
	return $s;
}

function form_select($name, $arr, $checked = 0, $id = TRUE) {
	if(empty($arr)) return '';
	$idadd = $id === TRUE ? "id=\"$name\"" : ($id ? "id=\"$id\"" : '');
	$s = "<select name=\"$name\" $idadd> \r\n";
	$s .= form_options($arr, $checked);
	$s .= "</select> \r\n";
	return $s;
}

function form_options($arr, $checked = 0) {
	$s = '';
	foreach((array)$arr as $k=>$v) {
		$add = $k == $checked ? ' selected="selected"' : '';
		$s .= "<option value=\"$k\"$add>$v</option> \r\n";
	}
	return $s;
}

function form_text($name, $value, $width = 150) {
	is_numeric($width) AND $width .= 'px';
	$s = "<input type=\"text\" name=\"$name\" id=\"$name\" value=\"$value\" style=\"width: {$width}\" />";
	return $s;
}

function form_hidden($name, $value) {
	$s = "<input type=\"hidden\" name=\"$name\" id=\"$name\" value=\"$value\" />";
	return $s;
}

function form_textarea($name, $value, $width = 600,  $height = 300) {
	is_numeric($width) AND $width .= 'px';
	is_numeric($height) AND $height .= 'px';
	$s = "<textarea name=\"$name\" id=\"$name\" style=\"width: {$width}; height: {$height};\">$value</textarea>";
	return $s;
}

function form_password($name, $value, $width = 150) {
	is_numeric($width) AND $width .= 'px';
	$s = "<input type=\"password\" name=\"$name\" id=\"$name\" value=\"$value\" style=\"width: {$width}\" />";
	return $s;
}

function form_time($name, $value, $width = 150) {
	is_numeric($width) AND $width .= 'px';
	$s = "<input type=\"text\" name=\"$name\" id=\"$name\" value=\"$value\" style=\"width: {$width}\" />";
	return $s;
}


/**用法

echo form_radio_yes_no('radio1', 0);
echo form_checkbox('aaa', array('无', '有'), 0);

echo form_radio_yes_no('aaa', 0);
echo form_radio('aaa', array('无', '有'), 0);
echo form_radio('aaa', array('a'=>'aaa', 'b'=>'bbb', 'c'=>'ccc', ), 'b');

echo form_select('aaa', array('a'=>'aaa', 'b'=>'bbb', 'c'=>'ccc', ), 'a');

*/

?>
