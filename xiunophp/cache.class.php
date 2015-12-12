<?php

/*
* Copyright (C) 2015 xiuno.com
*/

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
        public function error($errno, $errstr) {
                $this->errno = $errno;
                $this->errstr = $errstr;
        }
        public function __destruct() {

        }
}

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
                return xcache_set($k, $v, $life);
        }
        // 取不到数据的时候返回 NULL，不是 FALSE
        public function get($k) {
                return xcache_get($k);
        }
        public function delete($k) {
                return xcache_unset($k);
        }
        public function truncate() {
                xcache_unset_by_prefix('');
                return TRUE;
        }
        public function error($errno, $errstr) {
                $this->errno = $errno;
                $this->errstr = $errstr;
        }
        public function __destruct() {

        }
}

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
                $v = xn_json_encode($v);
                $r = $this->link->set($k, $v);
                $life AND $r AND $this->link->expire($k, $life);
                return $r;
        }
        public function get($k) {
                if(!$this->link && !$this->connect()) return FALSE;
                $r = $this->link->get($k);
                return $r === FALSE ? NULL : xn_json_decode($r);
        }
        public function delete($k) {
                if(!$this->link && !$this->connect()) return FALSE;
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
        public function error($errno = 0, $errstr = '') {
                $this->errno = $errno;
                $this->errstr = $errstr;
        }
        public function __destruct() {

        }
}

// mysql 的实现封装耦合性有点高，要求有 bbs_cache 表存在。参看 install/install.sql 中的结构。
class cache_mysql {
        public $conf = array();
        public $db = NULL;
        public $link = NULL;
        public $errno = 0;
        public $errstr = '';
        public $table = 'bbs_cache';
        public function __construct($conf = array()) {
                if(!is_array($conf)) {
                        $this->db = $conf;
                } else {
                        $this->conf = $conf;
                        if(function_exists('mysql_connect')) {
                                $db = new db_mysql($conf);
                                $db->errstr AND $this->error($db->errno, $db->errstr);
                        } elseif(class_exists('PDO')) {
                                $db = new db_pdo_mysql($conf);
                                $db->errstr AND $this->error($db->errno, $db->errstr);
                        } else {
                                $this->error(1, 'PHP 的 mysqllib, pdo_mysql 扩展没有加载');
                        }
                        $this->db = $db;
                }
        }
        public function connect() {
                if($this->link) return $this->link;
                $db = $this->db;
                $this->link = $db->connect();
                $db->errstr AND $this->error($db->errno, $db->errstr);
                return $this->link;
        }
        public function set($k, $v, $life = 0) {
                if(!$this->link && !$this->connect()) return FALSE;
                $time = time();
                $expiry = $life ? $time + $life : 0;
                $v = addslashes(xn_json_encode($v));
                $r = $this->db->exec("REPLACE INTO `{$this->table}` SET k='$k',v='$v',expiry='$expiry'");
                if($this->db->errno) $this->error($this->db->errno, $this->db->errstr);
                return $r !== FALSE;
        }
        public function get($k) {
                if(!$this->link && !$this->connect()) return FALSE;
                $time = time();
                $arr = $this->db->find_one("SELECT * FROM `{$this->table}` WHERE k='$k'");
                if(!$arr) return NULL;
                if($arr['expiry'] && $time > $arr['expiry']) {
                        $this->db->exec("DELETE FROM `{$this->table}` WHERE k='$k'", $this->link);
                        return NULL;
                }
                return xn_json_decode($arr['v'], 1);
        }
        public function delete($k) {
                if(!$this->link && !$this->connect()) return FALSE;
                $r = $this->db->exec("DELETE FROM `{$this->table}` WHERE k='$k'", $this->link);
                return empty($r) ? FALSE : TRUE;
        }
        public function truncate() {
                if(!$this->link && !$this->connect()) return FALSE;
                $r = $this->db->exec("TRUNCATE `{$this->table}`", $this->link);
                return TRUE;
        }
        public function error($errno = 0, $errstr = '') {
                $this->errno = $errno ? $errno : ($this->link ? mysql_errno($this->link) : mysql_errno());
                $this->errstr = $errstr ? $errstr : ($this->link ? mysql_error($this->link) : mysql_error());
        }
        public function __destruct() {

        }
}

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
                $r = $this->link->set($k, $v, 0, $life);
                return $r;
        }
        public function get($k) {
                if(!$this->link && !$this->connect()) return FALSE;
                return $this->link->get($k);
        }
        public function delete($k) {
                if(!$this->link && !$this->connect()) return FALSE;
                return $this->link->delete($k);
        }
        public function truncate() {
                if(!$this->link && !$this->connect()) return FALSE;
                $keys = $kv->pkrget('', 100); // 获取 100 条
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

/*
$n = new cache_mysql(array('host'=>'localhost', 'user'=>'root', 'password'=>'root', 'name'=>'test'));
if($n->errno) exit($n->errstr);
$n->set('a', array(1,2,3));
$k = $n->get('a');
var_dump($k);
if($n->errno) exit($n->errstr);
*/

?>