<?php

/**
 * 对接应用入口示例
 * 此示例是独立于SDK的子应用，通过/dingdingauto/bridge.php进行中转授权登录。
 * 此示例仅适用于调用钉钉免登授权登录的场景，具体的身份验证和会话机制取决于子应用本身。
 * 使用此示例，钉钉后台微应用首页地址应设置为：http://hostname:port/dingdingauto/bridge.php，配置项 LOCAL_APP_INDEX_URL 应设置为：http://hostname:port/example1.php
 *
 */
require_once 'dingdingauto/config.php';
require_once 'dingdingauto/Common/request.php';
require_once 'dingdingauto/Core/biz.php';

if (request_int('referer_from_dingdingauto') == 1) {
    //当存在传入参数 referer_from_dingdingauto=1 时，说明子应用处于未登录状态
    //此时需要校验钉钉免登授权，同时获取钉钉用户信息，实现快捷登录子应用逻辑
    dingdingauto_check_auth($token, null, false, true);

    //TODO：获取钉钉用户信息，快捷登录子应用
    print_r(get_dingdingauto_cookie());
    //登录成功，写入cookie
    setcookie('user', get_dingdingauto_userid(), 0, '/');
}

//以下是子应用自身逻辑，此处以"Hello world"为例
echo '<br><br>';
echo 'Hello, world!';

header('Content-Type: text/html; charset=utf-8');
exit(0);
