<?php exit;

// 获取看雪的星星数

// 将秒格式化为天，小时
function kanxue_time_fmt($t) {
	if($t > 86400) {
		return round($t / 86400).'天';
	} elseif($t > 3600) {
		return round($t / 3600).'小时';
	} elseif($t > 60) {
		return round($t / 60).'分钟';
	} else {
		return $t.'秒';
	}
}

function kanxue_stars($x, &$title) {

        static $arr = array();

        // 曲线公式：$i * sqrt($i)

        // $text_star_0 = '☆';
        // $text_star = '★';
        // $text_moon = '☽';
        // $text_sun = '☀';
        
        $text_star_0 = '<img src="plugin/kanxue/img/star_0.gif" width="13" height="13" style="opacity: 0.65" />';
        $text_star = '<img src="plugin/kanxue/img/star.gif" width="13" height="13" style="opacity: 0.65" />';
        $text_moon = '<img src="plugin/kanxue/img/moon.gif" width="13" height="13" style="opacity: 0.65" />';
        $text_sun = '<img src="plugin/kanxue/img/sun.gif" width="13" height="13" style="opacity: 0.65" />';
        
//        $text_star_0 = '<img src="plugin/kanxue/img/star_0.gif" width="16" height="16" />';
//        $text_star = '<img src="plugin/kanxue/img/star.gif" width="16" height="16" />';
//        $text_moon = '<img src="plugin/kanxue/img/moon.gif" width="16" height="16" />';
//        $text_sun = '<img src="plugin/kanxue/img/sun.gif" width="16" height="16" />';
        
        $notation = 4; // 进制
        $bit = 3;      // 位数
        $sum = pow($notation, $bit); // 总的状态位

        $limit = 31536000; // 365 天，满贯

        $coefficient = round( $limit / ($sum * sqrt($sum)) );

         // 如果为 0 级显示为空星星。
        if($x < 61594) {   // $arr[1] 硬编码，加速
        	$title = '级别：0 | 在线时长：'.kanxue_time_fmt($x).' | 升级还需要：'.kanxue_time_fmt(61594 - $x);
        	return $text_star_0;
        }
        
        // 满级
        if($x >= $limit) {
        	$title = "级别：$sum | 在线时长：".kanxue_time_fmt($x).' | 满级';
        	return str_repeat($text_sun, $notation);
        }
       
        // 生成一个数组
        if(empty($arr)) {
                for($i=0; $i<$sum; $i++) {
                        $arr[$i] = round( ($i * sqrt($i)) * $coefficient ); // 递增
                }
        }

        $r = '';
        for($i = $sum - 1; $i > -1; $i--) {
                if($x > $arr[$i]) {
                        $r = base_convert($i.'', 10, $notation);
                        break;
                }
        }
        $r = sprintf("%03d", $r);
        $s = '';
	$s = str_repeat($text_sun, $r[0]);
	$s .= str_repeat($text_moon, $r[1]);
	$s .= str_repeat($text_star, $r[2]);
        
	$title = "级别：$i | 在线时长：".kanxue_time_fmt($x).' | 升级还需要：'.kanxue_time_fmt($arr[$i + 1] - $x);
        return $s;
}

?>