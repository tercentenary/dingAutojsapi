<?php
/**
 * 钉钉jsapi需要引用的js
 *
 */
require_once dirname(__DIR__) . '/DingTalk.php';
?>
<?php if (is_mobile_client()) { ?>
    <script type="text/javascript" src="http://g.alicdn.com/ilw/ding/0.7.0/scripts/dingtalk.js"></script>
<?php } else { ?>
    <script type="text/javascript" src="http://g.alicdn.com/dingding/dingtalk-pc-api/2.3.1/index.js"></script>
<?php } ?>
<script type="text/javascript" src="/dingtalk/Core/js/dingtalk.js?v=<?php echo GLOBAL_RESOURCE_VERSION; ?>"></script>
<script type="text/javascript">
    var _config = <?php echo DingTalk::get_configs(DINGTALK_APP_AGENT_ID); ?>;
    DingTalk.auth(_config, false);
</script>