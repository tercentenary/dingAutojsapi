<?php

/**
 * 错误码定义
 */

define('PARAM_MISSING_ERROR_CODE', '0x100');
define('PARAM_MISSING_ERROR_MSG', 'Missing necessary parameters');

define('PARAM_INVALID_ERROR_CODE', '0x101');
define('PARAM_INVALID_ERROR_MSG', 'Invalid parameters');

define('PARAM_ILLEGAL_ERROR_CODE', '0x102');
define('PARAM_ILLEGAL_ERROR_MSG', 'Illegal input');

define('UNSUPPORTED_REQUEST_METHOD_ERROR', '0x103');

define('USER_ERROR', '0x300');
define('USER_LOGIN_EXPIRED', '0x301');
define('USER_BAN_ERROR', '0x302');
define('REJECT_REQUEST_ERROR_CODE', '0x303');
define('USER_DENY_ERROR_MSG', 'Access denied');

define('NETWORK_ERROR', '0x400');

define('INTERNAL_ERROR', '0x500');

define('DINGTALK_AUTH_EXPIRED', '0x600');
define('DINGTALK_API_ERROR', '0x601');

define('RUNTIME_ERROR', '0x10000');
