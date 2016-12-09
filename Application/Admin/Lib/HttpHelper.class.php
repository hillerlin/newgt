<?php
namespace Admin\Lib;
/**
 * HttpHelper for post & get
 *
 * @author sam
 */
class HttpHelper {
    
    /**
     * post
     * $data  =>   array('arg1'=> 'value1', 'arg2'=>'value2')
     * @return type
     */
    public static function post($url, $data) {
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_TIMEOUT,30);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,30);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        
        $response = curl_exec($ch);
//        $info = curl_getinfo($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ( $httpCode != 200 ){
            $message = "Return code is {$httpCode} \n".$url."\n".curl_error ( $ch );
            self::log($message);
            curl_close($ch);
            return false;
        } else {
            curl_close($ch);
            self::log($response);
            return $response;
        }
    }

    public static function get($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

    public static function getGet($arg, $defaultValue = '') {
        return isset($_GET[$arg]) ? $_GET[$arg] : $defaultValue;
    }

    public static function getPost($arg, $defaultValue = '') {
        return isset($_POST[$arg]) ? $_POST[$arg] : $defaultValue;
    }
    
    public static function log($message) {
        $destination = C('LOG_PATH') . '/http_' . date('y_m_d') . '.log';
        \Think\Log::write($message, 'INFO ', 'File', $destination);
    }

}
