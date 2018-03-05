/**
 * 钉钉jsapi
 * 
 * vervison v1.0
 * Depends on jquery.cookie.js, base64.js
 */
var DingTalk = DingTalk || {};
(function (dt) {
    var self = dt;
    var Ding;

    dt.init = function () {
        dt.isMobileClient = self.util.isMobileClient();
        Ding = self.isMobileClient ? dd : DingTalkPC;
        window.Ding = Ding;
    };

    dt.auth = function (config, isAuthCode, gotoUrl) {
        //jsapi列表，详见官方文档，根据自己需要增删
        var jsApiList = [
            'runtime.info',
            'runtime.permission.requestAuthCode',
            'device.notification.prompt',
            'device.notification.alert',
            'device.notification.confirm',
            'device.notification.prompt',
            'device.notification.toast',
            'biz.chat.open',
            'biz.chat.pickConversation',
            'biz.util.open',
            'biz.util.openLink',
            'biz.user.get',
            'biz.contact.choose',
            'biz.telephone.call',
            'biz.ding.post'];

        dingTalkAuth(Ding, config, jsApiList, isAuthCode, gotoUrl, function (conf, isAuth, goUrl, data) {
            var dingUser = {
                userid: data.userid,
                deviceId: data.deviceId,
                jobnumber: data.jobnumber,
                name: data.name
            };
            Ding.dingUser = dingUser;
            Ding.config = {
                agentId: conf.agentId,
                corpId: conf.corpId
            };
            window.Ding = Ding;
            if (isAuth) {
                dingUser._uda = data._uda;
                var cookieKey = "d_" + conf.agentId;
                var cookieValue = encodeURIComponent(Base64.encode(Base64.utf16to8(JSON.stringify(dingUser))));
                $.cookie(cookieKey, cookieValue, {path: '/'});
            }
            if (typeof (onAuthCompleted) === "function") {
                onAuthCompleted.apply(this, [conf, isAuth, goUrl, data]);
            }
            if (!self.util.isEmpty(goUrl)) {
                window.location.href = goUrl;
            }
        });
    };

    function dingTalkAuth(ddObj, config, jsApiList, isAuthCode, gotoUrl, onSuccess) {
        ddObj.config({
            agentId: config.agentId,
            corpId: config.corpId,
            timeStamp: config.timeStamp,
            nonceStr: config.nonceStr,
            signature: config.signature,
            jsApiList: jsApiList //必填，需要使用的jsapi列表
        });
        ddObj.dingUser = ddObj.dingUser || {};
        ddObj.dingUser.userid = 0;
        ddObj.ready(function (res) {
            ddObj.runtime.permission.requestAuthCode({
                corpId: config.corpId, //企业ID
                onSuccess: function (info) {
                    if (isAuthCode) {
                        $.ajax({
                            type: "POST",
                            url: "/dingtalk/Core/api.php",
                            data: {_act: 1, agentId: config.agentId, code: info.code, dcsrf: config.dcsrf},
                            dataType: "json",
                            timeout: 10000,
                            success: function (data) {
                                if (data.code === 0) {
                                    if (typeof (onSuccess) === "function") {
                                        onSuccess.apply(this, [config, isAuthCode, gotoUrl, data]);
                                    }
                                } else {
                                    document.body.innerHTML = "";
                                    self.alert("免登授权失败2:" + self.util.defaultValue(data.msg, "您操作过快，请稍后重试~"), "错误消息", "稍后重试");
                                }
                            },
                            error: function (xhr) {
                                document.body.innerHTML = "";
                                self.alert("免登授权失败1:" + self.util.defaultValue(xhr.statusText, "网络连接超时，请稍后重试~"), "错误消息", "稍后重试");
                            }
                        });
                    } else {
                        var cookieKey = "d_" + config.agentId;
                        var cookieValue = $.cookie(cookieKey);
                        var cookieExpired = self.util.isEmpty(cookieValue);
                        if (!cookieExpired) {
                            var data = self.util.jsonParse(Base64.utf8to16(Base64.decode(decodeURIComponent(cookieValue))));
                            cookieExpired = self.util.isEmpty(data) || self.util.isEmpty(data.userid) || self.util.isEmpty(data.deviceId) || data.userid === 0;
                        }
                        if (cookieExpired) {
                            dingTalkAuth(ddObj, config, jsApiList, true, gotoUrl, onSuccess);
                        } else {
                            if (typeof (onSuccess) === "function") {
                                onSuccess.apply(this, [config, isAuthCode, gotoUrl, data]);
                            }
                        }
                    }
                },
                onFail: function (err) {
                    document.body.innerHTML = "";
                    self.alert("免登授权失败0:" + JSON.stringify(err), "错误消息", "稍后重试");
                }
            });
        });
        ddObj.error(function (err) {
            if (!dt.isMobileClient && err.errorCode == "PC_1003" || !dt.isMobileClient && err.errorCode == 1003 || dt.isMobileClient && err.errorCode == 3) {
                $.ajax({
                    type: "POST",
                    url: "/dingtalk/Core/api.php",
                    data: {_act: 2, dcsrf: config.dcsrf},
                    dataType: "json",
                    timeout: 10000,
                    success: function (data) {
                        if (data.code === 0) {
                            window.location.reload();
                        } else {
                            document.body.innerHTML = self.util.defaultValue(data.msg, "未知错误，请稍候重试！");
                        }
                    },
                    error: function (xhr) {
                        document.body.innerHTML = self.util.defaultValue(xhr.statusText, "网络连接超时，请稍后重试~");
                    }
                });
            } else {
                document.body.innerHTML = JSON.stringify(err);
            }
        });
    };

    dt.alert = function (msg, title, btnText, ok) {
        Ding.device.notification.alert({
            message: self.util.defaultValue(msg, "休息会儿再来吧~"),
            title: self.util.defaultValue(title, ""), //可空
            buttonName: self.util.defaultValue(btnText, "好的"),
            onSuccess: function () {
                if (typeof (ok) === "function") {
                    ok.apply(this);
                }
            },
            onFail: function (err) {
            }
        });
    };

    dt.confirm = function (msg, title, btnTexts, ok, cancel) {
        Ding.device.notification.confirm({
            message: self.util.defaultValue(msg, "确定吗？"),
            title: self.util.defaultValue(title, "提示"),
            buttonLabels: self.util.defaultValue(btnTexts, ["确定", "取消"]),
            onSuccess: function (result) {
                // buttonIndex: 0 //被点击按钮的索引值，Number类型，从0开始
                if (result.buttonIndex === 0 && typeof (ok) === "function") {
                    ok.apply(this);
                } else if (result.buttonIndex === 1 && typeof (cancel) === "function") {
                    cancel.apply(this);
                }
            },
            onFail: function (err) {
            }
        });
    };

    dt.prompt = function (msg, title, btnTexts) {
        Ding.device.notification.prompt({
            message: self.util.defaultValue(msg, "再说一遍？"),
            title: self.util.defaultValue(title, "提示"),
            buttonLabels: self.util.defaultValue(btnTexts, ["继续", "不玩了"]),
            onSuccess: function (result) {
                /*
                 {
                 buttonIndex: 0, //被点击按钮的索引值，Number类型，从0开始
                 value: '' //输入的值
                 }
                 */
            },
            onFail: function (err) {
            }
        });
    };

    dt.toast = function (text, type, duration, delay) {
        Ding.device.notification.toast({
            type: self.util.defaultValue(type, "information"), //toast的类型 alert, success, error, warning, information, confirm
            text: self.util.defaultValue(text, "这里是个toast"), //提示信息
            duration: self.util.defaultValue(duration, 3), //显示持续时间，单位秒，最短2秒，最长5秒
            delay: self.util.defaultValue(delay, 0), //延迟显示，单位秒，默认0, 最大限制为10
            onSuccess: function (result) {
                /*{}*/
            },
            onFail: function (err) {
            }
        });
    };

    dt.actionsheet = function (title, options) {
        Ding.device.notification.actionSheet({
            title: self.util.defaultValue(title, "谁是最棒哒？"), //标题
            cancelButton: "取消", //取消按钮文本
            otherButtons: self.util.defaultValue(options, ["悟空", "八戒", "唐僧", "沙和尚"]),
            onSuccess: function (result) {
                /*{
                 buttonIndex: 0 //被点击按钮的索引值，Number，从0开始, 取消按钮为-1
                 }*/
            },
            onFail: function (err) {
            }
        });
    };

    /**
     * 打开钉钉内置页面
     * @param {string} name
     * @param {int} userid
     * @param {string} corpId
     * @returns {undefined}
     */
    dt.open = function (name, userid, corpId) {
        Ding.biz.util.open({
            name: self.util.defaultValue(name, "profile"), //页面名称，目前支持：1、profile(个人资料页)
            params: {
                id: userid, // String 必选 用户工号
                corpId: corpId //String 必选 企业id
            }, //传参
            onSuccess: function () {
                /**/
            },
            onFail: function (err) {
            }
        });
    };

    /**
     * 打开模态框
     * @param {string} size
     * @param {string} url
     * @param {string} title
     * @returns {undefined}
     */
    dt.modal = function (size, url, title) {
        Ding.biz.util.openModal({
            size: self.util.defaultValue(size, "middle"), // modal的尺寸，可选值："":大，"middle":中，"mini":小
            url: self.util.defaultValue(url, "/"), //打开modal的内容的url
            title: self.util.defaultValue(title, "modal title"), //顶部标题
            onSuccess: function (result) {
                /*
                 */
            },
            onFail: function () {
            }
        });
    };

    /**
     * 打开侧边栏
     * @param {string} title
     * @returns {undefined}
     */
    dt.slidePanel = function (title) {
        Ding.biz.util.openSlidePanel({
            url: "about:blank", //打开侧边栏的url
            title: self.util.defaultValue(title, "title"), //侧边栏顶部标题
            onSuccess: function (result) {
                /*
                 */
            },
            onFail: function () {
            }
        });
    };

    /**
     * 在浏览器上打开链接
     * @param {string} url
     * @returns {undefined}
     */
    dt.openLink = function (url) {
        Ding.biz.util.openLink({
            url: self.util.defaultValue(url, "http://www.dingtalk.com"), //要打开链接的地址
            onSuccess: function (result) {
                /**/
            },
            onFail: function () {
            }
        });
    };

    dt.util = {
        isMobileClient: function () {
            //return navigator.userAgent.match(/(iPhone|iPod|Android|ios|iOS|iPad|Backerry|WebOS|Symbian|Windows Phone|Phone)/i);
            return /(iPhone|iPod|Android|ios|iOS|iPad|Backerry|WebOS|Symbian|Windows Phone|Phone)/i.test(navigator.userAgent);
        },
        isEmpty: function (obj) {
            return obj === undefined || obj === null || obj === "";
        },
        defaultValue: function (obj, defaultValue) {
            return this.isEmpty(obj) ? defaultValue : obj;
        },
        jsonParse: function (data) {
            var obj;
            try {
                obj = JSON.parse(data);
            } catch (e) {
                obj = {};
            }
            return obj;
        },
        appendUrl: function (url, name, value) {
            if (url.indexOf(name) > 0) {
                return url;
            }
            if (url.indexOf('?') > 0) {
                return url + "&" + name + "=" + value;
            }
            return url + "?" + name + "=" + value;
        }
    };

    window.DingTalk = dt;
})(DingTalk);
DingTalk.init();