<?php
/**
 * 通用钉钉免登授权
 *
 */
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/requires.php';
require_once __DIR__ . '/DingTalk.php';

$token = request_string('token');
$token = json_decode(authcode_decode($token));
if (!isset($token) || strlen($token->agentId) <= 0) document_write('非法来源，拒绝访问！');
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8"/>
        <title>授权</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style type="text/css">
            .main{text-align: center; font-family: Helvetica, Tahoma, Arial, "Microsoft YaHei";}
            .main p{line-height: 100px;}
        </style>
        <?php if (is_mobile_client()) { ?>
            <script type="text/javascript" src="https://g.alicdn.com/ilw/ding/0.7.0/scripts/dingtalk.js"></script>
        <?php } else { ?>
            <script type="text/javascript" src="https://g.alicdn.com/dingding/dingtalk-pc-api/2.3.1/index.js"></script>
        <?php } ?>
        <script type="text/javascript" src="js/dingtalk.js?v=<?php echo GLOBAL_RESOURCE_VERSION; ?>"></script>
        <script type="text/javascript">
            var _config = <?php echo DingTalk::get_configs($token->agentId); ?>;
            DingTalk.auth(_config, true, <?php echo '"' . $token->backUrl . '"'; ?>);
        </script>
    </head>
    <body>
        <div class="main">
            <p>登录中，请稍候...</p>
        </div>
        <script src="http://libs.baidu.com/jquery/1.8.3/jquery.min.js"></script>
        <script src="http://cdn.bootcss.com/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
        <script src="http://yx-cdn.oss-cn-hangzhou.aliyuncs.com/public/utility/base64.js"></script>
    </body>
</html>