<?php

class cache_apc {
	
        public $conf = array();
        public $link = NULL;
        public $errno = 0;
        public $errstr = '';
        public function __construct($conf = array()) {
                if(!function_exists('apc_get')) {
                        $this->error(1, 'APC 扩展没有加载，请检查您的 PHP 版本');
                        return FALSE;
                }
                $this->conf = $conf;
        }
        public function connect() {
        }
        public function set($k, $v, $life) {
        	$k = APP_CACHE_PRE.$k;
                return apc_store($k, $v, $life);
        }
        public function get($k) {
        	$k = APP_CACHE_PRE.$k;
                return apc_get($k);
        }
        public function delete($k) {
        	$k = APP_CACHE_PRE.$k;
                return apc_delete($k);
        }
        public function truncate() {
                return apc_clear_cache('user');
        }
        public function error($errno, $errstr) {
                $this->errno = $errno;
                $this->errstr = $errstr;
        }
        public function __destruct() {

        }
}

?>