<?php

class cache_yac {
	public $yac = NULL;
	public $cachepre = '';
        public function __construct() {
                if(!class_exists('Yac')) {
                        return xn_error(1, 'yac 扩展没有加载，请检查您的 PHP 版本');
                }
		$this->cachepre = isset($conf['cachepre']) ? $conf['cachepre'] : 'pre_';
		$this->yac = new Yac($this->cachepre);
        }
        public function connect() {
        }
        public function set($k, $v, $life) {
                return $this->yac->set($k, $v, $life);
        }
        // 取不到数据的时候返回 NULL，不是 FALSE
        public function get($k) {
                return $this->yac->get($k);
        }
        public function delete($k) {
                return $this->yac->delete($k);
        }
        public function truncate() {
                $this->yac->flush();
                return TRUE;
        }
        public function __destruct() {

        }
}

?>