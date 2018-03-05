<?php

/**
 * 阿里钉钉API帮助类
 *
 */
class DingTalk {

    const DINGTALK_API_URL = DINGTALK_API_URL;
    const DINGTALK_CORP_ID = DINGTALK_CORP_ID; //企业ID 
    const DINGTALK_CORP_SECRET = DINGTALK_CORP_SECRET; //API Corp密钥 
    const DINGTALK_NONCE_STR = DINGTALK_NONCE_STR; //自定义随机字符串

    /**
     * 获取前端配置信息
     * @param string $agentId 微应用AgentID
     * @return type 配置数组
     */

    public static function get_configs($agentId) {
        $corpId = self::DINGTALK_CORP_ID;
        $nonceStr = self::DINGTALK_NONCE_STR;
        $timeStamp = time();
        $url = self::get_current_page_url();
        $ticket = self::get_jsapi_ticket();
        $signature = self::get_front_sign($ticket, $nonceStr, $timeStamp, $url);

        $config = array(
            'url' => $url,
            'nonceStr' => $nonceStr,
            'agentId' => $agentId,
            'timeStamp' => $timeStamp,
            'corpId' => $corpId,
            'signature' => $signature,
            'dcsrf' => authcode_encode(md5(self::DINGTALK_NONCE_STR . date('YmdHi', time()) . PRIVATE_KEY))
        );
        return json_encode($config, JSON_UNESCAPED_SLASHES);
    }

    public static function validate_dcsrf($dcsrf) {
        return strcmp(md5(self::DINGTALK_NONCE_STR . date('YmdHi', time()) . PRIVATE_KEY), authcode_decode($dcsrf)) === 0;
    }

    /**
     * 获取前端签名
     * @param string $ticket jsapi_ticket
     * @param string $nonceStr 随机字符串 DINGTALK_NONCE_STR
     * @param string $timeStamp 当前时间戳
     * @param string $url 当前页面URL
     * @return string 签名字符串
     */
    private static function get_front_sign($ticket, $nonceStr, $timeStamp, $url) {
        $to_sign = 'jsapi_ticket=' . $ticket .
                '&noncestr=' . $nonceStr .
                '&timestamp=' . $timeStamp .
                '&url=' . $url;
        return sha1($to_sign);
    }

    private static function internal_get_access_token($retry_times = 0) {
        $url = self::DINGTALK_API_URL . '/gettoken?corpid=' . self::DINGTALK_CORP_ID . '&corpsecret=' . self::DINGTALK_CORP_SECRET;
        $http_result = curl_http_request($url);
        $result = json_decode($http_result, false, 512, JSON_BIGINT_AS_STRING);
        if ((!isset($result) || $result->errcode != 0) && $retry_times < 3) {
            $retry_times++;
            return self::internal_get_access_token($retry_times);
        }
        return $result;
    }

    public static function get_access_token($refresh = false) {
        $redis = new \Redis();
        $redis_connected = $redis->connect(REDIS_HOST, REDIS_PORT_KVS);
        $use_redis = $redis_connected;
        if ($refresh) $use_redis = false;
        if ($use_redis) {
            $access_token = $redis->get(self::DINGTALK_CORP_ID . '_access_token');
            $use_redis = $access_token;
        }
        if (!$use_redis) {
            $access_token = self::internal_get_access_token()->access_token;
            if ($redis_connected) {
                //钉钉access_token有效期默认2小时，即7200秒，这里设置成1小时59分有效期，即7140秒
                $redis->setex(self::DINGTALK_CORP_ID . '_access_token', 7140, (string) $access_token);
            }
        }
        return $access_token;
    }

    private static function internal_get_jsapi_ticket($retry_times = 0) {
        $url = self::DINGTALK_API_URL . '/get_jsapi_ticket?type=jsapi&access_token=' . self::get_access_token();
        $http_result = curl_http_request($url);
        $result = json_decode($http_result, false, 512, JSON_BIGINT_AS_STRING);
        if ((!isset($result) || $result->errcode != 0) && $retry_times < 3) {
            $retry_times++;
            return self::internal_get_jsapi_ticket($retry_times);
        }
        return $result;
    }

    public static function get_jsapi_ticket($refresh = false) {
        $redis = new \Redis();
        $redis_connected = $redis->connect(REDIS_HOST, REDIS_PORT_KVS);
        $use_redis = $redis_connected;
        if ($refresh) $use_redis = false;
        if ($use_redis) {
            $jsapi_ticket = $redis->get(self::DINGTALK_CORP_ID . '_jsapi_ticket');
            $use_redis = $jsapi_ticket;
        }
        if (!$use_redis) {
            $jsapi_ticket_result = self::internal_get_jsapi_ticket();
            $jsapi_ticket = $jsapi_ticket_result->ticket;
            if ($redis_connected) {
                $expires_in = (int) $jsapi_ticket_result->expires_in - 60;
                if ($expires_in < 0) $expires_in = 7140;
                $redis->setex(self::DINGTALK_CORP_ID . '_jsapi_ticket', $expires_in, (string) $jsapi_ticket);
            }
        }
        return $jsapi_ticket;
    }

    public static function refresh_jsapi_ticket() {
        self::get_access_token(true);
        self::get_jsapi_ticket(true);
    }

    /**
     * 调用钉钉业务API
     * @param string $url API路径，无需域名前缀，如：/user/get
     * @param array $params 附加参数，无需access_token
     * @param int $post 是否以post方式提交，0:否(默认) 1:是
     * @param int $retry_times 调用API重试次数
     * @return boolean 是否调用成功
     */
    public static function curl_dingtalk_api($url, array $params, $post = 0, $retry_times = 0) {
        $url = self::DINGTALK_API_URL . $url;
        $post_data = array(
            'access_token' => self::get_access_token()
        );
        $post_data = array_merge($post_data, $params);
        $post_fields = http_build_query($post_data, null, '&', PHP_QUERY_RFC3986);
        if ($post == 0) $url.= '?' . $post_fields;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POST, $post);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        if ($post == 1) curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        $result = curl_exec($ch);
        $curl_info = curl_getinfo($ch);
        $info = $curl_info['url'] . '|' . $curl_info['http_code'] . '|' . $curl_info['total_time'];
        $header_size = $curl_info['header_size'];
        $header = substr($result, 0, $header_size);
        $body = substr($result, $header_size);
        $uri = $_SERVER["REQUEST_URI"];
        curl_close($ch);
        if ($curl_info['http_code'] == 0 || $curl_info['http_code'] >= 400) {
            write_log("get-dingtalk-api", "[req_url=>$uri]\n[url=>$url]\n[info:$info]\n[header=>$header]\n[body=>$body]\n\n", "error");
        } else {
            $result = json_decode($body, false, 512, JSON_BIGINT_AS_STRING);
            $result->raw = $body;
            if ((!isset($result)) && $retry_times < 3) {
                $retry_times++;
                return self::curl_dingtalk_api($url, $params, $post, $retry_times);
            }
            if (!isset($result) || $result->errcode != 0) {
                write_log("get-dingtalk-api", "[req_url=>$uri]\n[url=>$url]\n[body=>$body]\n\n", "DINGTALKERRRSP");
            }
            return $result;
        }
        return false;
    }

    /**
     * 解析钉钉API返回结果
     * @param array $result 钉钉API返回原始结果数据
     * @param object $response 解析后的对象(输出参数)
     * @return boolean 是否解析成功
     */
    public static function get_dingtalk_response($result, &$response) {
        if (!isset($result) || !isset($result->errcode)) {
            $response = new \stdClass();
            $response->code = -1;
            $response->msg = "unknown error";
            return false;
        }

        $response = $result;
        $response->code = $result->errcode;
        $response->msg = $result->errmsg;
        unset($response->errcode);
        unset($response->errmsg);

        if ($response->code != 0) {
            return false;
        }

        $response->code = 0;
        unset($response->msg);
        unset($response->raw);

        return true;
    }

    /**
     * 调用钉钉业务API并返回正确结果
     * @param type $url API路径，无需域名前缀，如：/user/get
     * @param array $params 附加参数，无需access_token
     * @param type $post 是否以post方式提交，0:否(默认) 1:是
     * @return object 解析后的对象
     */
    public static function get_dingtalk_api_response($url, array $params, $post = 0) {
        $dingtalk_result = self::curl_dingtalk_api($url, $params, $post);
        if (!self::get_dingtalk_response($dingtalk_result, $response)) {
            if ($response->code == 40014) {
                //不合法的access_token
                self::get_access_token(true);
                return self::get_dingtalk_api_response($url, $params, $post);
            } else {
                die_error(DINGTALK_API_ERROR, $response->msg);
            }
        }
        return $response;
    }

    /**
     * 获取当前页面URL，摘自钉钉SDK
     * @return string
     */
    public static function get_current_page_url() {
        $pageURL = 'http';

        if (array_key_exists('HTTPS', $_SERVER) && $_SERVER["HTTPS"] == "on") {
            $pageURL .= "s";
        }
        $pageURL .= "://";

        if ($_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . urldecode($_SERVER["REQUEST_URI"]);
        } else {
            $pageURL .= $_SERVER["SERVER_NAME"] . urldecode($_SERVER["REQUEST_URI"]);
        }
        return $pageURL;
    }

}
