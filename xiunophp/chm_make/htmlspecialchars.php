<?php

$s = <<<EOT


// 将当前节点设置为 class="active"
$.fn.class_active = function() {
	var jnode = this.eq(0);
	jnode.siblings().removeClass('active');
	jnode.addClass('active');
	return this;
}



EOT;
echo htmlspecialchars($s);