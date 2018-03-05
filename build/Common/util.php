<?php

/**
 * 实用工具函数库
 */

/**
 * Discuz 中经典的加密解密函数
 * @param string $string 原文或者密文
 * @param string $operation 操作(ENCODE | DECODE), 默认为 DECODE
 * @param string $key 密钥
 * @param int $expiry 密文有效期, 加密时候有效， 单位 秒，0 为永久有效
 * @return string 处理后的 原文或者 经过 base64_encode 处理后的密文
 *
 * @example
 *
 * $a = authcode('abc', 'ENCODE', 'key');
 * $b = authcode($a, 'DECODE', 'key');  // $b(abc)
 *
 * $a = authcode('abc', 'ENCODE', 'key', 3600);
 * $b = authcode('abc', 'DECODE', 'key'); // 在一个小时内，$b(abc)，否则 $b 为空
 */
function authcode($string, $operation = 'DECODE', $key = 'EC9F1B98D6A0737736E818C2DE723324', $expiry = 3600) {

    $ckey_length = 4;
    // 随机密钥长度 取值 0-32;
    // 加入随机密钥，可以令密文无任何规律，即便是原文和密钥完全相同，加密结果也会每次不同，增大破解难度。
    // 取值越大，密文变动规律越大，密文变化 = 16 的 $ckey_length 次方
    // 当此值为 0 时，则不产生随机密钥

    $key = md5($key ? $key : 'key' ); //这里可以填写默认key值
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), - $ckey_length)) : '';

    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);

    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0 ) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);

    $result = '';
    $box = range(0, 255);

    $rndkey = array();
    for ($i = 0; $i <= 255; $i ++) {
        $rndkey [$i] = ord($cryptkey [$i % $key_length]);
    }

    for ($j = $i = 0; $i < 256; $i ++) {
        $j = ($j + $box [$i] + $rndkey [$i]) % 256;
        $tmp = $box [$i];
        $box [$i] = $box [$j];
        $box [$j] = $tmp;
    }

    for ($a = $j = $i = 0; $i < $string_length; $i ++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box [$a]) % 256;
        $tmp = $box [$a];
        $box [$a] = $box [$j];
        $box [$j] = $tmp;
        $result .= chr(ord($string [$i]) ^ ($box [($box [$a] + $box [$j]) % 256]));
    }

    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}

function authcode_encode($string, $expiry = 60) {
    return authcode($string, 'ENCODE', 'EC9F1B98D6A0737736E818C2DE723324', $expiry);
}

function authcode_decode($string) {
    return authcode($string, 'DECODE');
}

/**
 * 取毫秒数
 * @return string 当前时间毫秒部分
 */
function milliseconds() {
    list($usec, $sec) = explode(' ', microtime());
    $msec = round($usec * 1000);
    return $msec;
}

/**
 * 获取常量定义的数组
 * @param string $define_name 常量名称
 * @param boolean $assoc 是否关联数组
 * @return array
 */
function get_defined_array($define_name, $assoc = true) {
    return json_decode($define_name, $assoc);
}

/**
 * 记录常规日志
 * @param string $catagory 日志类别
 * @param string $text 日志文本内容
 * @param string $level 日志级别
 * @return 是否记录成功
 */
function write_log($catagory, $text, $level = 'info') {
    $folder_path = $_SERVER["DOCUMENT_ROOT"] . "/log/$catagory";
    if (!is_dir($folder_path)) mkdir($folder_path, 0777, true);
    $date = date('Y-m-d');
    $file_path = "$folder_path/$date.log";
    $log_text = sprintf("[%s.%s][$level]%s\n", date('Y-m-d H:i:s'), milliseconds(), $text);
    return file_put_contents($file_path, $log_text, FILE_APPEND);
}

/**
 * 记录错误日志
 * @param string $catagory 日志类别
 * @param string $title 标题
 * @param string $ex 异常对象
 * @return 是否记录成功
 */
function exception_log($catagory, $title, \Exception $ex) {
    $folder_path = $_SERVER["DOCUMENT_ROOT"] . "/exception-log/$catagory";
    if (!is_dir($folder_path)) mkdir($folder_path, 0777, true);
    $date = date('Y-m-d');
    $file_path = "$folder_path/$date.log";
    $messages = array($ex->getMessage());
    while (null !== ($inner_exception = $ex->getPrevious())) {
        $messages[] = $inner_exception->getMessage();
    }
    $log_text = implode('\n', $messages);
    $log_text = sprintf("[%s.%s]$title\n%s", date('Y-m-d H:i:s'), milliseconds(), $log_text);
    return file_put_contents($file_path, $log_text, FILE_APPEND);
}

/**
 * HTTP请求通用方法(curl)
 * @param string $url 请求的URL
 * @param int $method 请求方式，0:GET，1:POST，默认0:GET
 * @param array $post_data 请求参数
 * @param string $cookie cookie
 * @return boolean 是否请求成功
 */
function curl_http_request($url, $method = 0, $post_data = null, $cookie = null) {
    if (isset($post_data) && is_string($post_data) && strlen($post_data) > 0) $post_fields = $post_data;
    else if (isset($post_data) && is_array($post_data) && count($post_data) > 0) $post_fields = http_build_query($post_data, null, '&', PHP_QUERY_RFC3986);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POST, $method);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    if (isset($post_fields)) curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    if (isset($cookie)) curl_setopt($ch, CURLOPT_COOKIE, $cookie);
    $result = curl_exec($ch);
    $curl_info = curl_getinfo($ch);
    $info = $curl_info['url'] . '|' . $curl_info['http_code'] . '|' . $curl_info['total_time'];
    $header_size = $curl_info['header_size'];
    $header = substr($result, 0, $header_size);
    $body = substr($result, $header_size);
    $success = true;
    $uri = $_SERVER["REQUEST_URI"];
    if ($curl_info['http_code'] == 0 || $curl_info['http_code'] >= 400) {
        $log_text = "[req_url=>$uri]\n[url=>$url]\n[post=>$post_fields]\n[info:$info]\n[header=>$header]\n[body=>$body]\n\n";
        //记录错误日志
        write_log("curl", $log_text, "error");
        $success = false;
    }
    curl_close($ch);
    if ($success) {
        return $body;
    }
    return false;
}
