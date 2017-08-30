<?php exit;

function array_to_tree ($items, $id = 'id', $pid = 'pid', $son = 'son',$key=true)
{
	$tree = array(); //格式化的树
	$tmpMap = array();  //临时扁平数据
	if ( !is_array($items) )
		return;
	foreach ( $items as $item ) {
		$tmpMap[$item[$id]] = $item;
	}
	if($key==false){
		foreach ( $items as $item ) {
			if ( isset( $tmpMap[$item[$pid]] ) ) {
				
				$tmpMap[$item[$pid]][$son][] = &$tmpMap[$item[$id]];
			} else {
				$tree[] = &$tmpMap[$item[$id]];
			}
		}
	}else{
		foreach ( $items as $item ) {
			if ( isset( $tmpMap[$item[$pid]] ) ) {
				
				$tmpMap[$item[$pid]][$son][$item[$id]] = &$tmpMap[$item[$id]];
			} else {
				$tree[$item[$id]] = &$tmpMap[$item[$id]];
			}
		}
	}
	unset( $tmpMap, $items );
	return $tree;
}

?>