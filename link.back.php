<?php

require_once 'config.php';
require_once 'requires.php';

$token = request_string('token');
$token = json_decode(authcode_decode($token));
if (!isset($token) || strlen($token->agentId) <= 0 || strlen($token->cookieValue) <= 0 || strlen($token->gotoUrl) <= 0) document_write('非法来源，拒绝访问！');

$cookie = json_decode(base64_decode(urldecode($token->cookieValue)));
$is_auth = isset($cookie) && strlen($cookie->_uda) > 0 && strcmp($cookie->_uda, md5($token->agentId . $cookie->userid . $cookie->deviceId . PRIVATE_KEY)) === 0;
if (!$is_auth) document_write('非法来源，拒绝访问！');

setcookie('d_' . $token->agentId, $token->cookieValue, 0, '/');

redirect_302($token->gotoUrl);
