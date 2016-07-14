<?php

// 经过测试 xcache3.1 xcache_set() life 参数不管用
class cache_xcache {
        public $conf = array();
        public $link = NULL;
        public $errno = 0;
        public $errstr = '';
        public function __construct($conf = array()) {
                if(!function_exists('xcache_set')) {
                        $this->error(1, 'Xcache 扩展没有加载，请检查您的 PHP 版本');
                        return FALSE;
                }
                $this->conf = $conf;
        }
        public function connect() {
        }
        public function set($k, $v, $life) {
        	$k = APP_CACHE_PRE.$k;
                return xcache_set($k, $v, $life);
        }
        // 取不到数据的时候返回 NULL，不是 FALSE
        public function get($k) {
        	$k = APP_CACHE_PRE.$k;
                return xcache_get($k);
        }
        public function delete($k) {
        	$k = APP_CACHE_PRE.$k;
                return xcache_unset($k);
        }
        public function truncate() {
                xcache_unset_by_prefix(APP_CACHE_PRE);
                return TRUE;
        }
        public function error($errno, $errstr) {
                $this->errno = $errno;
                $this->errstr = $errstr;
        }
        public function __destruct() {

        }
}

?>