<?php
    function wechat_login_link ( $type = 0 ) {
        global $conf;
        if( $type ) {
            //授权登录 可获取头像 用户名等
            $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $conf['wx_appkey'] . '&redirect_uri=' . urlencode( 'http://' . $_SERVER['HTTP_HOST'] . url('wxlogin') ) . '&response_type=code&scope=snsapi_userinfo&state=snsapi_userinfo#wechat_redirect';
        } else {
            //不授权只取 openid
            $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $conf['wx_appkey'] . '&redirect_uri=' . urlencode( 'http://' . $_SERVER['HTTP_HOST'] . url('wxlogin')  ) . '&response_type=code&scope=snsapi_base&state=snsapi_base#wechat_redirect';
        }

        http_location( $url );
    }

    function wechat_get_token ( $code , $state = '' ) {
        global $conf, $user, $uid, $longip, $time;
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $conf['wx_appkey'] . '&secret=' . $conf['wx_appsecret'] . '&code=' . $code . '&grant_type=authorization_code';
        $data = https_curl_get( $url );
        $data = xn_json_decode( $data );
        $openid = '';
        if( !empty( $data['openid'] ) ) {
            $openid = $data['openid'];
            $arr = db_find_one( 'user_open_wechat' , array('openid' => $openid) );

            if( empty( $arr) ) {
                $r = db_create( 'user_open_wechat' , array('uid' => 0 , 'openid' => $openid) );

                message(-1,'注册失败');
            }

            if( $arr['uid'] ) {
                $user = user_read( $arr['uid'] );
                if(empty($user)) {
                    db_update('user_open_wechat' , array('openid' => $openid),array('uid'=>0));
                    //db_delete( 'user_open_wechat' , array('openid' => $openid) );
                }else {
                    user_update( $user['uid'] , array('login_ip' => $longip , 'login_date' => $time , 'logins+' => 1) );
                    $uid = $user['uid'];
                    $_SESSION['uid'] = $uid;
                    user_token_set( $user['uid'] );
                    $user ? message( 0 , jump( '登录成功' , 'http://' . _SERVER( 'HTTP_HOST' ) ) ,1) : message( 1 , '登录失败' );
                }
            } elseif( $uid ) {
                $user = user_read( $uid );
                if(empty($user)) {
                    db_update('user_open_wechat' , array('openid' => $openid),array('uid'=>0));

                    //db_delete( 'user_open_wechat' , array('openid' => $openid) );
                }else {
                    user_update( $user['uid'] , array('login_ip' => $longip , 'login_date' => $time , 'logins+' => 1) );
                    $uid = $user['uid'];
                    $_SESSION['uid'] = $uid;
                    user_token_set( $user['uid'] );
                    db_update( 'user_open_wechat' , array('openid' => $openid) , array('uid' => $uid) );
                    $user ? message( 0 , jump( '登录成功' , 'http://' . _SERVER( 'HTTP_HOST' ) ) ,1) : message( 1 , '登录失败' );
                }
            }
                if( $state == 'snsapi_userinfo' ) {
                    $url = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $data['access_token'] . '&openid=' . $openid . '&lang=zh_CN';
                    $data = https_curl_get( $url );
                    $data = xn_json_decode( $data );
                    $user = wechat_create_user( $data , $openid );
                } else {
                    $openid = xn_encrypt( $openid );
                    include './plugin/xn_wechat_public/view/htm/user_login.htm';
                    exit;
                }

        } else {
            message( 1 , '参数失效,请重试' );
        }

        return false;
    }

    function wechat_create_user ( $data , $openid ) {
        global $conf , $time , $longip;
        $username = $data['nickname'];
        // 自动产生一个用户名
        $r = user_read_by_username( $username );
        if( $r ) {
            // 特殊字符过滤
            $username = xn_substr( $username . '_' . $time , 0 , 31 );
            $r = user_read_by_username( $username );
            $r AND message( -1 , '用户名被占用。' );
        }
        // 自动产生一个 Email
        $email = "qq_$time@qq.com";
        $r = user_read_by_email( $email );
        $r AND message( -1 , 'Email 被占用' );
        // 随机密码
        $password = md5( rand( 1000000000 , 9999999999 ) . $time );
        $user = array('username' => $username , 'email' => $email , 'password' => $password , 'gid' => 101 , 'salt' => rand( 100000 , 999999 ) , 'create_date' => $time , 'create_ip' => $longip , 'avatar' => 0 , 'logins' => 1 , 'login_date' => $time , 'login_ip' => $longip ,);
        $uid = user_create( $user );
        empty( $uid ) AND message( -1 , '注册失败' );
        //$user = user_read($uid);
        $r = db_update( 'user_open_wechat' , array('openid' => $openid) , array('uid' => $uid) );
        empty( $r ) AND message( -1 , '注册失败' );
        runtime_set( 'users+' , '1' );
        runtime_set( 'todayusers+' , '1' );
        // 头像不重要，忽略错误。
        if( $data['headimgurl'] ) {
            $filename = "$uid.png";
            $dir = substr( sprintf( "%09d" , $uid ) , 0 , 3 ) . '/';
            $path = $conf['upload_path'] . 'avatar/' . $dir;
            !is_dir( $path ) AND mkdir( $path , 0777 , true );
            $data = http_get( $data['headimgurl'] );
            file_put_contents( $path . $filename , $data );
            user_update( $uid , array('avatar' => $time) );
        }
        $user['uid']=$uid;
        $_SESSION['uid'] = $uid;
        user_token_set($user['uid']);
        $user ? message( 0 , jump( '登录成功' , 'http://' . _SERVER( 'HTTP_HOST' ) ) ,1) : message( 1 , '登录失败' );

        return $user;
    }



   function https_curl_get ($url)
    {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
    //curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
    //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    $temp = curl_exec($ch);
    curl_close($ch);

    return $temp;
    }
?>