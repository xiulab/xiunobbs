<?php exit;
foreach ($threadlist as $k=>$v){
	$color = db_find_one('subject_style',array('id'=>$v['style_id']));
	$threadlist[$k]['subject']="<span style='color:".$color['style']."'>".$v['subject']."</span>";
}
?>