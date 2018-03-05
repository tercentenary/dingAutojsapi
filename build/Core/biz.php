<?php

/**
 * 钉钉公共方法库(系统级)
 *
 */
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/requires.php';
require_once __DIR__ . '/DingTalk.php';

/**
 * 校验钉钉授权
 * @param string $token 跳转授权token，输出参数
 * @param string $backUrl 授权通过后跳转的URL，默认null(可选)
 * @param boolean $autoRedirect 是否自动跳转，默认true(可选)
 * @param boolean $outputError 是否输出错误消息，默认true(可选)
 * @return boolean 是否校验通过
 */
function dingtalk_check_auth(&$token = null, $backUrl = null, $autoRedirect = true, $outputError = true) {
    $cookie = get_dingtalk_cookie();
    $is_auth = isset($cookie) && strlen($cookie->_uda) > 0 && strcmp($cookie->_uda, md5(DINGTALK_APP_AGENT_ID . $cookie->userid . $cookie->deviceId . PRIVATE_KEY)) === 0;
    if (!$is_auth) {
        if (strlen($backUrl) <= 0) $backUrl = get_current_page_url();
        $params = array(
            'agentId' => DINGTALK_APP_AGENT_ID,
            'backUrl' => $backUrl
        );
        $token = urlencode(authcode_encode(json_encode($params)));
        if ($autoRedirect) redirect_302('/dingtalk/Core/auth.php?token=' . $token);
        if ($outputError) die_error(USER_LOGIN_EXPIRED, 'Authorization failed, access denied.');
        return false;
    }
    return true;
}

/**
 * 根据cookie名称获取cookie值
 * @return string cookie值
 */
function get_dingtalk_cookie() {
    $cookieKey = 'd_' . DINGTALK_APP_AGENT_ID;
    $cookieValue = $_COOKIE[$cookieKey];
    $cookie = json_decode(base64_decode(urldecode($cookieValue)));
    return $cookie;
}

/**
 * 获取钉钉用户ID
 * @return string 钉钉用户ID
 */
function get_dingtalk_userid() {
    return get_dingtalk_cookie()->userid;
}

/**
 * 获取钉钉用户设备ID
 * @return string 钉钉用户设备ID
 */
function get_dingtalk_deviceId() {
    return get_dingtalk_cookie()->deviceId;
}

/**
 * 获取钉钉用户工号
 * @return string 钉钉用户工号
 */
function get_dingtalk_jobnumber() {
    return get_dingtalk_cookie()->jobnumber;
}

/**
 * 获取钉钉用户名
 * @return string 钉钉用户名
 */
function get_dingtalk_username() {
    return get_dingtalk_cookie()->name;
}
