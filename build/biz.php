<?php


require_once 'config.php';
require_once 'requires.php';
require_once 'Core/DingTalk.php';

/**
 * 获取部门列表
 * @return stdClass
 */
function dingtalk_get_dept_list() {
    $params = array();
    return DingTalk::get_dingtalk_api_response('/department/list', $params);
}

/**
 * 获取部门详情
 * @param int $deptId 部门ID
 * @return stdClass
 */
function dingtalk_get_dept_info($deptId) {
    $params = array(
        'id' => $deptId
    );
    return DingTalk::get_dingtalk_api_response('/department/get', $params);
}

/**
 * 获取成员详情
 * @param string $userid 钉钉用户ID
 * @return stdClass
 */
function dingtalk_get_user_info($userid) {
    $params = array(
        'userid' => $userid
    );
    return DingTalk::get_dingtalk_api_response('/user/get', $params);
}

/**
 * 获取部门成员列表
 * @param int $deptId 部门ID
 * @return stdClass
 */
function dingtalk_get_user_list($deptId) {
    $params = array(
        'department_id' => $deptId
    );
    return DingTalk::get_dingtalk_api_response('/user/simplelist', $params);
}

/**
 * 获取部门成员列表(详情)
 * @param int $deptId 部门ID
 * @return stdClass
 */
function dingtalk_get_user_list_detail($deptId) {
    $params = array(
        'department_id' => $deptId
    );
    return DingTalk::get_dingtalk_api_response('/user/list', $params);
}
