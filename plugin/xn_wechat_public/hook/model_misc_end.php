if ( !function_exists('array_to_tree') ) {
	function array_to_tree ($items, $id = 'id', $pid = 'pid', $son = 'son')
	{
		$tree = array(); //格式化的树
		$tmpMap = array();  //临时扁平数据
		if ( !is_array($items) )
			return;
		foreach ( $items as $item ) {
			$tmpMap[$item[$id]] = $item;
		}
		foreach ( $items as $item ) {
			if ( isset( $tmpMap[$item[$pid]] ) ) {
				$tmpMap[$item[$pid]][$son][$item[$id]] = &$tmpMap[$item[$id]];
			} else {
				$tree[$item[$id]] = &$tmpMap[$item[$id]];
			}
		}
		unset( $tmpMap, $items );
		
		return $tree;
	}
}