<?php

class cache_apc {
	
	public $conf = array();
	public $link = NULL;
	public $cachepre = '';

        public function __construct($conf = array()) {
                if(!function_exists('apc_get')) {
			return xn_error(-1, 'APC 扩展没有加载，请检查您的 PHP 版本');
                }
                $this->conf = $conf;
		$this->cachepre = isset($conf['cachepre']) ? $conf['cachepre'] : 'pre_';
        }
        public function connect() {
        }
        public function set($k, $v, $life) {
                return apc_store($k, $v, $life);
        }
        public function get($k) {
                return apc_get($k);
        }
        public function delete($k) {
                return apc_delete($k);
        }
        public function truncate() {
                return apc_clear_cache('user');
        }
        public function __destruct() {

        }
}

?>