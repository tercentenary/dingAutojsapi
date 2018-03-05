<?php

/**
 * 钉钉API(系统级，仅供http调用)
 */
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/requires.php';
require_once __DIR__ . '/DingTalk.php';

/*
 * 获取用户信息(免登)
 */
execute_action(HttpRequestMethod::Post, 1, function() {
    $dcsrf = request_string('dcsrf');
    if (strlen($dcsrf) <= 0 || !DingTalk::validate_dcsrf($dcsrf)) die_error(USER_ERROR, '您操作过快，请稍后重试~');
    list($agentId, $code) = filter_request(array(
        request_int('agentId'),
        request_string('code')
    ));
    $params = array(
        'code' => $code
    );
    $response = DingTalk::get_dingtalk_api_response('/user/getuserinfo', $params);
    //获取详细信息
    $params = array(
        'userid' => $response->userid
    );
    $response1 = DingTalk::get_dingtalk_api_response('/user/get', $params);

    $response->jobnumber = $response1->jobnumber;
    $response->name = $response1->name;
    $response->_uda = md5($agentId . $response->userid . $response->deviceId . PRIVATE_KEY);

    echo_result((array) $response);
});

/*
 * 刷新jsapi_ticket
 */
execute_action(HttpRequestMethod::Post, 2, function() {
    $dcsrf = request_string('dcsrf');
    if (strlen($dcsrf) <= 0 || !DingTalk::validate_dcsrf($dcsrf)) die_error(USER_ERROR, '非法请求！');
    DingTalk::refresh_jsapi_ticket();
    echo_code(0);
});

/*
 * 生成一次性票据(跳转浏览器使用)
 */
execute_action(HttpRequestMethod::Post, 3, function() use($agentId) {
    list($agentId, $gotoUrl) = filter_request(array(
        request_int('agentId'),
        request_string('gotoUrl')
    ));
    $cookieKey = 'd_' . $agentId;
    $cookieValue = $_COOKIE[$cookieKey];
    $token = json_encode(array('cookieValue' => $cookieValue, 'agentId' => $agentId, 'gotoUrl' => $gotoUrl));
    $token = urlencode(authcode_encode($token));
    echo_result(array('token' => $token));
});
