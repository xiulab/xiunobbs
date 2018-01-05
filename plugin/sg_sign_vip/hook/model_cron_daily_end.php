$sg_sign_set = db_find_one('sg_sign_set', array('id'=>1));
if($sg_sign_set['time'] < strtotime('yesterday')){
db_update('sg_sign_set', array('id'=>1), array('sg_sign_top'=>'','sg_sign_one'=>'','sg_sign'=>0));
}