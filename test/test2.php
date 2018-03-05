<?php
/**
 * 对接应用入口示例(调用钉钉jsapi)
 * 此示例完美配合此SDK使用，无需单独实现会话机制
 * 使用此示例，钉钉后台微应用首页地址应设置为：http://hostname:port/example2.php
 *
 */
require_once '../build/config.php';
require_once '../build/Core/biz.php';

//身份校验
../build_check_auth();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8"/>
        <title>示例2 - 钉钉登录</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style type="text/css">
            .main,button{text-align: center; font-family: Helvetica, Tahoma, Arial, "Microsoft YaHei";}
            .main p{line-height: 100px;}
            .main button{font-size: 16px; width: 180px; line-height: 32px; color: #fff; background-color: #38acff; border:1px solid #38acff; border-radius: 3px; cursor: pointer; display: none;}
            .main button:hover{background-color: #46b2fe; border:1px solid #46b2fe;}
        </style>
        <?php include_once '/../build/Core/includes/../buildHeaderScript.php'; ?>
    </head>
    <body>
        <div class="main">
            <p>登录成功！</p>
            <button id="open-link" onclick="openLink()">在外部浏览器打开</button>
        </div>
        <?php include_once '/../build/Core/includes/../buildFooterScript.php'; ?>
        <script type="text/javascript">
            function openLink() {
                $.ajax({
                    type: "POST",
                    url: "/../build/Core/api.php",
                    data: {_act: 3, agentId: Ding.config.agentId, gotoUrl: window.location.href},
                    dataType: "json",
                    timeout: 5000,
                    success: function (data) {
                        ../build.openLink(window.location.origin + "/../build/link.php?token=" + data.token);
                    },
                    error: function (xhr) {
                        ../build.alert(../build.util.defaultValue(xhr.statusText, "网络连接超时，请稍后重试~"));
                    }
                });
            }
            //免登授权完成回调
            function onAuthCompleted(config, isAuthCode, gotoUrl, data) {
                $("#open-link").show();
                ../build.alert("登录成功！");
            }
        </script>
    </body>
</html>