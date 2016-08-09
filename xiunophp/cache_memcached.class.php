<?php

class cache_memcached {
	
	public $conf = array();
	public $link = NULL;
	public $cachepre = '';

        public function __construct($conf = array()) {
                if(!extension_loaded('Memcache') && !extension_loaded('Memcached') ) {
                        return xn_error(1, ' Memcached 扩展没有加载，请检查您的 PHP 版本');
                }
                $this->conf = $conf;
		$this->cachepre = isset($conf['cachepre']) ? $conf['cachepre'] : 'pre_';
        }
        public function connect() {
                $conf = $this->conf;
                if($this->link) return $this->link;
                if(extension_loaded('Memcache')) {
                        $memcache = new Memcache;
                } elseif(extension_loaded('Memcached')) {
                        $memcache = new Memcached;
                } else {
			return xn_error(-1, 'Memcache 扩展不存在。');
                }
                $r = $memcache->connect($conf['host'], $conf['port']);
                if(!$r) {
			return xn_error(-1, '连接 Memcached 服务器失败。');
                }
                $this->link = $memcache;
                return $this->link;
        }
        public function set($k, $v, $life = 0) {
                if(!$this->link && !$this->connect()) return FALSE;
                $r = $this->link->set($k, $v, 0, $life);
                return $r;
        }
        public function get($k) {
                if(!$this->link && !$this->connect()) return FALSE;
                $r = $this->link->get($k);
                return $r === FALSE ? NULL : $r;
        }
        public function delete($k) {
                if(!$this->link && !$this->connect()) return FALSE;
                return $this->link->delete($k); // TRUE|FALSE
        }
        public function truncate() {
                if(!$this->link && !$this->connect()) return FALSE;
                return $this->link->flush();
        }
        public function __destruct() {

        }
}

?>