<?php

/**
 * 配置项
 */
error_reporting(E_ERROR | E_COMPILE_ERROR | E_CORE_ERROR | E_RECOVERABLE_ERROR);
date_default_timezone_set('PRC');
mb_internal_encoding(DEFAULT_ENC);
mb_regex_encoding(DEFAULT_ENC);

/* * =====BEGIN全局配置=====* */
//加密使用私钥
define('PRIVATE_KEY', 'EC9F1B98D6A0737736E818C2DE723324');
/* * =====END全局配置=====* */

/* * =====BEGIN Redis配置=====* */
define('REDIS_HOST', '127.0.0.1'); //redis主机IP
define('REDIS_PORT_DEFAULT', 6379); //redis端口(默认)
define('REDIS_PORT_KVS', 6380); //redis端口(键值对)
/* * =====END Redis配置=====* */

/* * =====BEGIN钉钉API配置=====* */
//钉钉API根URL
define('DINGTALK_API_URL', 'https://oapi.dingtalk.com');
//企业ID 
define('DINGTALK_CORP_ID', 'CorpID');
//API Corp密钥 
define('DINGTALK_CORP_SECRET', 'CorpSecret');
//钉钉自定义随机字符串
define('DINGTALK_NONCE_STR', '0123456789');
/* * =====END钉钉API配置=====* */

/* * =====BEGIN子应用配置=====* */
//钉钉微应用AgentID
define('DINGTALK_APP_AGENT_ID', 12345678);
//使用钉钉免登授权的应用首页URL
define('LOCAL_APP_INDEX_URL', 'http://hostname:port/example1.php');
//使用钉钉免登授权的应用cookie键名称
define('LOCAL_APP_COOKIE_KEY', 'user');
//全局资源文件版本号
define('GLOBAL_RESOURCE_VERSION', '2016090900000');
/**=====END子应用配置=====**/
