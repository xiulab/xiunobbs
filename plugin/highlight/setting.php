<?php 
/**
*标题高亮风格
*/
!defined('DEBUG') AND exit('Access Denied.');
$action = param(3);

if(empty($action)){
	if($method == 'GET'){//设置页面
		$data = db_find('subject_style');
		$input = array();
		//风格表单
		$input['style1_name'] = form_text('style1_name', $data?$data[0]['name']:'风格1'); 
		$input['style1_value'] = form_text('style1_value', $data?$data[0]['style']:''); 
		$input['style2_name'] = form_text('style2_name', $data?$data[1]['name']:'风格2');  
		$input['style2_value'] = form_text('style2_value', $data?$data[1]['style']:'');  
		$input['style3_name'] = form_text('style3_name', $data?$data[2]['name']:'风格3'); 
		$input['style3_value'] = form_text('style3_value', $data?$data[2]['style']:'');  
		$input['style4_name'] = form_text('style4_name', $data?$data[3]['name']:'风格4'); 
		$input['style4_value'] = form_text('style4_value', $data?$data[3]['style']:''); 
		$input['style5_name'] = form_text('style5_name', $data?$data[4]['name']:'风格5');
		$input['style5_value'] = form_text('style5_value', $data?$data[4]['style']:'');
		include _include(APP_PATH.'plugin/highlight/setting.htm');
	}else{//提交页面
		$array = array(
			0 => array('name' => param('style1_name'), 'style' => param('style1_value')),
			1 => array('name' => param('style2_name'), 'style' => param('style2_value')),
			2 => array('name' => param('style3_name'), 'style' => param('style3_value')),
			3 => array('name' => param('style4_name'), 'style' => param('style4_value')),
			4 => array('name' => param('style5_name'), 'style' => param('style5_value'))
		);

		db_truncate('subject_style');

		foreach ($array as $v) {
			db_insert('subject_style',$v);
		}
		message(0, lang('confirm_success'));
	}
}
?>