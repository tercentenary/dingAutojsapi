<?php

/**
 * 响应通用函数库
 */
function document_write($msg = '抱歉，您无权访问！') {
    header('Content-Type: text/html; charset=utf-8');
    die('<p style="font-family: Helvetica, Tahoma, Arial, Microsoft YaHei;">' . $msg . '</p>');
}

function js_alert($msg, $close_window = true) {
    header('Content-Type: text/html; charset=utf-8');
    die("<script>alert('$msg');" . ($close_window ? 'window.close();' : '') . "</script>");
}

function redirect_302($url) {
    header("Location: $url");
    exit(0);
}

function die_error($error_code, $msg) {
    $result = array('code' => $error_code, 'msg' => $msg);
    die(get_response($result));
}

function die_error_with_log($error_code, $msg, $log_catagory, $log_text) {
    write_log($log_catagory, $log_text, 'error');
    die_error($error_code, $msg);
}

function echo_result($result) {
    if (!isset($result['code'])) $result['code'] = 0;
    exit(get_response($result));
}

function echo_jsonp_result($result, $jsonp) {
    exit($jsonp . '(' . json_encode($result) . ')');
}

function echo_code($code) {
    exit(get_response(array('code' => $code)));
}

function echo_msg($msg) {
    exit(get_response(array('code' => 0, 'msg' => $msg)));
}

function get_response($data) {
    set_content_type('json', 'utf-8');
    return json_encode($data);
}

function set_content_type($type = 'json', $charset = 'utf-8') {
    if (headers_sent()) return;
    $content_types = array(
        'xml' => 'application/xml',
        'json' => 'application/json',
        'html' => 'text/html',
    );
    header('Content-Type: ' . $content_types[strtolower($type)] . '; charset=' . $charset);
}
