<?php

class cache_redis {
	
        public $conf = array();
        public $link = NULL;
        public $errno = 0;
        public $errstr = '';
        public function __construct($conf = array()) {
                if(!extension_loaded('Redis')) {
                        $this->error(1, ' Redis 扩展没有加载');
                        return FALSE;
                }
                $this->conf = $conf;
        }
        public function connect() {
                if($this->link) return $this->link;
                $redis = new Redis;
                $r = $redis->connect('localhost', '6379');
                if(!$r) {
                        $this->error(2, '连接 Redis 服务器失败。');
                        return FALSE;
                }
                //$redis->select('xn');
                $this->link = $redis;
                return $this->link;
        }
        public function set($k, $v, $life = 0) {
                if(!$this->link && !$this->connect()) return FALSE;
                $k = APP_CACHE_PRE.$k;
                $v = xn_json_encode($v);
                $r = $this->link->set($k, $v);
                $life AND $r AND $this->link->expire($k, $life);
                return $r;
        }
        public function get($k) {
                if(!$this->link && !$this->connect()) return FALSE;
                $k = APP_CACHE_PRE.$k;
                $r = $this->link->get($k);
                return $r === FALSE ? NULL : xn_json_decode($r);
        }
        public function delete($k) {
                if(!$this->link && !$this->connect()) return FALSE;
                $k = APP_CACHE_PRE.$k;
                return $this->link->del($k) ? TRUE : FALSE;
        }
        public function truncate() {
                if(!$this->link && !$this->connect()) return FALSE;
                return $this->link->flushdb(); // flushall
        }
        public function error($errno, $errstr) {
                $this->errno = $errno;
                $this->errstr = $errstr;
        }
        public function __destruct() {

        }
}

?>