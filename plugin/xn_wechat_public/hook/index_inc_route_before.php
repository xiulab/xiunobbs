<?php exit;
    if(!defined('SKIP_ROUTE')) {
        if( empty( $user ) && empty( $code ) && empty( $openid ) && !empty( $conf['wx_appkey'] ) ) {
            $agent = strtolower( _SERVER( 'HTTP_USER_AGENT' ) );
            if( strpos( $agent , 'micromessenger' ) !== false  && $route!='wxlogin') {
                http_location( url('wxlogin') );
            }
        }
    }

?>