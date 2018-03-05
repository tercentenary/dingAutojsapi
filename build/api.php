<?php


require_once 'config.php';
require_once 'requires.php';
require_once 'Core/DingTalk.php';
require_once 'Core/biz.php';
require_once 'biz.php';

//身份校验
dingtalk_check_auth($token, null, false, true);

/*
 * 获取部门列表
 */
execute_action(HttpRequestMethod::Get, 1, function() {
    echo_result(dingtalk_get_dept_list());
});

/*
 * 获取部门详情
 */
execute_action(HttpRequestMethod::Get, 2, function() {
    list($deptId) = filter_request(array(
        request_numeric('deptId')
    ));
    echo_result(dingtalk_get_dept_info($deptId));
});

/*
 * 获取成员详情
 */
execute_action(HttpRequestMethod::Get, 3, function() {
    list($userid) = filter_request(array(
        request_string('userid')
    ));
    echo_result(dingtalk_get_user_info($userid));
});

/*
 * 获取部门成员列表
 */
execute_action(HttpRequestMethod::Get, 4, function() {
    list($deptId) = filter_request(array(
        request_numeric('deptId')
    ));
    echo_result(dingtalk_get_user_list($deptId));
});

/*
 * 获取部门成员列表(详情)
 */
execute_action(HttpRequestMethod::Get, 5, function() {
    list($deptId) = filter_request(array(
        request_numeric('deptId')
    ));
    echo_result(dingtalk_get_user_list_detail($deptId));
});
