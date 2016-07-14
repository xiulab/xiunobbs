<?php

class cache_saekv {
        public $conf = array();
        public $link = NULL;
        public $errno = 0;
        public $errstr = '';
        public function __construct($conf = array()) {
                if(!extension_loaded('SaeKV')) {
                        $this->error(1, ' SaeKV 扩展没有加载，请检查您的 PHP 版本');
                        return FALSE;
                }
                $kv = new SaeKV();
                $this->link = $kv;
                $this->conf = $conf;
        }
        public function connect() {
                if($this->link) return $this->link;
                $this->link->init();
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
                return $this->link->get($k);
        }
        public function delete($k) {
                if(!$this->link && !$this->connect()) return FALSE;
                $k = APP_CACHE_PRE.$k;
                return $this->link->delete($k);
        }
        public function truncate() {
                if(!$this->link && !$this->connect()) return FALSE;
                $keys = $kv->pkrget(APP_CACHE_PRE, 100); // 获取 100 条
                foreach($keys as $k) {
                        $this->delete($k);
                }
                return TRUE;// $this->link->flush(); // 很不幸 sae 并未提供 flush all 接口
        }
        public function error($errno = 0, $errstr = '') {
                $this->errno = $errno;
                $this->errstr = $errstr;
        }
        public function __destruct() {

        }
}

?>