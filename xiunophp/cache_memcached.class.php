<?php

class cache_memcached {
	
        public $conf = array();
        public $link = NULL;
        public $errno = 0;
        public $errstr = '';
        public function __construct($conf = array()) {
                if(!extension_loaded('Memcache') && !extension_loaded('Memcached') ) {
                        $this->error(1, ' Memcached 扩展没有加载，请检查您的 PHP 版本');
                        return FALSE;
                }
                $this->conf = $conf;
        }
        public function connect() {
                $conf = $this->conf;
                if($this->link) return $this->link;
                if(extension_loaded('Memcache')) {
                        $memcache = new Memcache;
                } elseif(extension_loaded('Memcached')) {
                        $memcache = new Memcached;
                } else {
                        $this->error(2, 'Memcache 扩展不存在。');
                        return FALSE;
                }
                $r = $memcache->connect($conf['host'], $conf['port']);
                if(!$r) {
                        $this->error(3, '连接 Memcached 服务器失败。');
                        return FALSE;
                }
                $this->link = $memcache;
                return $this->link;
        }
        public function set($k, $v, $life = 0) {
                if(!$this->link && !$this->connect()) return FALSE;
                $k = APP_CACHE_PRE.$k;
                $r = $this->link->set($k, $v, 0, $life);
                return $r;
        }
        public function get($k) {
                if(!$this->link && !$this->connect()) return FALSE;
                $k = APP_CACHE_PRE.$k;
                $r = $this->link->get($k);
                return $r === FALSE ? NULL : $r;
        }
        public function delete($k) {
                if(!$this->link && !$this->connect()) return FALSE;
                $k = APP_CACHE_PRE.$k;
                return $this->link->delete($k); // TRUE|FALSE
        }
        public function truncate() {
                if(!$this->link && !$this->connect()) return FALSE;
                return $this->link->flush();
        }
        public function error($errno = 0, $errstr = '') {
                $this->errno = $errno;
                $this->errstr = $errstr;
        }
        public function __destruct() {

        }
}

?>