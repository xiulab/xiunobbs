<?php

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
                $k = APP_CACHE_PRE.$k;
                $time = time();
                $expiry = $life ? $time + $life : 0;
                $v = addslashes(xn_json_encode($v));
                $r = $this->db->exec("REPLACE INTO `{$this->table}` SET k='$k',v='$v',expiry='$expiry'");
                if($this->db->errno) $this->error($this->db->errno, $this->db->errstr);
                return $r !== FALSE;
        }
        public function get($k) {
                if(!$this->link && !$this->connect()) return FALSE;
                $k = APP_CACHE_PRE.$k;
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
                $k = APP_CACHE_PRE.$k;
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

/*
$n = new cache_mysql(array('host'=>'localhost', 'user'=>'root', 'password'=>'root', 'name'=>'test'));
if($n->errno) exit($n->errstr);
$n->set('a', array(1,2,3));
$k = $n->get('a');
var_dump($k);
if($n->errno) exit($n->errstr);
*/

?>