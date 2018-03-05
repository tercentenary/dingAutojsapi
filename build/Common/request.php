<?php

/**
 * 请求通用函数库

 */
function execute_action($http_request_method, $action, callable $func) {
    if (strcasecmp($_SERVER['REQUEST_METHOD'], $http_request_method) === 0 && strcasecmp(request_action(), $action) === 0) {
        $func();
        exit(0);
    }
}

//输入参数验证
function filter_request(array $args) {
    if (in_array(null, $args, true)) die_error(PARAM_MISSING_ERROR_CODE, PARAM_MISSING_ERROR_MSG);
    if (in_array(false, $args, true)) die_error(PARAM_INVALID_ERROR_CODE, PARAM_INVALID_ERROR_MSG);
    return $args;
}

function request_string($name, $decode = true, $sanitize_special_chars = false) {
    $value = $_REQUEST[$name];
    if (!isset($value)) return null;
    if ($decode) $value = rawurldecode($value);
    if ($sanitize_special_chars) $value = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
    $value = trim($value);
    if (strlen($value) <= 0) return null;
    return $value;
}

function request_email($name) {
    $request_string = request_string($name, true);
    return !isset($request_string) ? null : filter_var($request_string, FILTER_VALIDATE_EMAIL);
}

function request_url($name) {
    $request_string = request_string($name);
    return !isset($request_string) ? null : filter_var($request_string, FILTER_VALIDATE_URL);
}

function request_ip($name) {
    $request_string = request_string($name);
    return !isset($request_string) ? null : filter_var($request_string, FILTER_VALIDATE_IP);
}

function request_datetime($name) {
    $request_string = request_string($name);
    return !isset($request_string) ? null : filter_var($request_string, FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => '/^[0-9]{4}(\-|\/)[0-9]{1,2}(\\1)[0-9]{1,2}(|\s+[0-9]{1,2}(|:[0-9]{1,2}(|:[0-9]{1,2})))$/')));
}

function request_date($name) {
    $request_string = request_string($name);
    return !isset($request_string) ? null : filter_var($request_string, FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => '/^[0-9]{4}-[1-12]{1,2}-[1-31]{1,2}$/')));
}

function request_boolean($name) {
    $request_string = request_string($name);
    return !isset($request_string) ? null : filter_var($request_string, FILTER_VALIDATE_BOOLEAN);
}

function request_md5_32($name) {
    $request_string = request_string($name);
    return !isset($request_string) ? null : filter_var($request_string, FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => '/^[0-9a-fA-F]{32,32}$/')));
}

function request_md5_16($name) {
    $request_string = request_string($name);
    return !isset($request_string) ? null : filter_var($request_string, FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => '/^[0-9a-fA-F]{16,16}$/')));
}

function request_int($name, $min = null, $max = null) {
    $request_string = request_string($name);
    if (!isset($request_string)) return null;
    if (isset($min) || isset($max)) {
        $options = array();
        if (isset($min)) $options['min_range'] = $min;
        if (isset($max)) $options['max_range'] = $max;
        return filter_var($request_string, FILTER_VALIDATE_INT, array('options' => $options));
    }

    return filter_var($request_string, FILTER_VALIDATE_INT);
}

function request_float($name) {
    $request_string = request_string($name);
    return !isset($request_string) ? null : filter_var($request_string, FILTER_VALIDATE_FLOAT);
}

function request_regexp($name, $regexp) {
    $request_string = request_string($name);
    return !isset($request_string) ? null : filter_var($request_string, FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => $regexp)));
}

function request_money($name) {
    return request_regexp($name, '/^(([1-9]{1}\d*)|([0]{1}))(\.(\d){1,2})?$/');
}

function request_mobilephone($name) {
    return request_regexp($name, '/^1[34578][0-9]{9,9}$/');
}

function request_telephone($name) {
    return request_regexp($name, '/^\d{3,4}-\d{7,8}$/');
}

function request_zip($name) {
    return request_regexp($name, '/^[1-9][0-9]{5}$/');
}

function request_idcard($name) {
    return request_regexp($name, '/^(\d{15}$|^\d{18}$|^\d{17}(\d|X|x))$/');
}

function request_qq($name) {
    return request_regexp($name, '/^\d{5,12}$/');
}

function request_ids($name) {
    return request_regexp($name, '/^\d{1,}(,\d{1,})+$|^\d{1,}$/');
}

function request_numeric($name, $min_length = 1, $max_length = null) {
    return request_regexp($name, '/^\d{' . $min_length . ',' . $max_length . '}$/');
}

function request_string_range($name, array $effective_values) {
    $request_string = request_string($name);
    return !in_array($request_string, $effective_values) ? null : $request_string;
}

function default_value($name, $value) {
    $request_string = request_string($name);
    return $request_string ? $request_string : $value;
}

function request_action() {
    $request_string = request_string('_act');
    if (!isset($request_string)) return 0;
    return filter_var($request_string, FILTER_VALIDATE_INT);
}

function get_request_ip() {
    $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    if (empty($ip)) $ip = $_SERVER["REMOTE_ADDR"];
    return $ip;
}

function get_request_method() {
    return strtoupper($_SERVER['REQUEST_METHOD']);
}

function set_request_params($name, $value) {
    $_REQUEST[$name] = $value;
}

/**
 * 判断是否是通过PC访问
 */
function is_pc_client() {
    $uAgent = $_SERVER['HTTP_USER_AGENT'];
    $osPat = "mozilla|m3gate|winwap|openwave|Windows NT|Windows 3.1|95|Blackcomb|98|ME|XWindow|ubuntu|Longhorn|AIX|Linux|AmigaOS|BEOS|HP-UX|OpenBSD|FreeBSD|NetBSD|OS\/2|OSF1|SUN";
    if (preg_match("/($osPat)/i", $uAgent)) {
        return true;
    } else {
        return false;
    }
}

/**
 * 判断是否是通过手机访问
 */
function is_mobile_client() {
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return true;
    }
    //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset($_SERVER['HTTP_VIA'])) {
        //找不到为flase,否则为true
        return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
    }
    //判断手机发送的客户端标志,兼容性有待提高
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array('nokia', 'sony', 'ericsson', 'mot', 'samsung', 'htc', 'sgh', 'lg', 'sharp',
            'sie-', 'philips', 'panasonic', 'alcatel', 'lenovo', 'iphone', 'ipod', 'blackberry', 'meizu',
            'android', 'netfront', 'symbian', 'ucweb', 'windowsce', 'palm', 'operamini', 'operamobi',
            'openwave', 'nexusone', 'cldc', 'midp', 'wap', 'mobile');
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    //协议法，因为有可能不准确，放到最后判断
    if (isset($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}

/**
 * 获取当前页面URL，摘自钉钉SDK
 * @return string
 */
function get_current_page_url() {
    $pageURL = 'http';

    if (array_key_exists('HTTPS', $_SERVER) && $_SERVER["HTTPS"] == "on") {
        $pageURL .= "s";
    }
    $pageURL .= "://";

    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . urldecode($_SERVER["REQUEST_URI"]);
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"] . urldecode($_SERVER["REQUEST_URI"]);
    }
    return $pageURL;
}

class HttpRequestMethod {

    const __default = self::Get;
    const Get = 'GET';
    const Post = 'POST';
    const Put = 'PUT';
    const Delete = 'DELETE';

}
