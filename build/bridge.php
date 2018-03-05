<?php


require_once 'config.php';
require_once 'Common/response.php';
require_once 'Core/biz.php';

//身份校验
dingtalk_check_auth();

//此处非固定逻辑，应根据具体子应用场景来判断是否登录状态
if (strlen($_COOKIE[LOCAL_APP_COOKIE_KEY]) > 0) {
    //获取到子应用cookie，说明子应用已经是登录状态，直接跳转至子应用首页即可
    redirect_302(LOCAL_APP_INDEX_URL);
} else {
    //未获取到子应用cookie，说明子应用尚未登录，跳转至子应用首页，同时传入参数 referer_from_dingtalk=1
    redirect_302(LOCAL_APP_INDEX_URL . '?referer_from_dingtalk=1');
    //TODO：在子应用中实现以下逻辑
    //当子应用接收到此参数，根据当前钉钉cookie来判断是否登录成功，从cookie中获取当前钉钉用户ID，进而获取钉钉用户信息，在子应用中实现快捷登录
}
