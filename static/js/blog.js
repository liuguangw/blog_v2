/**
 * pushState加载url对应的页面主体内容
 * 
 * @param url
 */
function loadUrl(url) {
	$.ajax({
		"url" : blogInfo.pushUrl,
		"method" : "POST",
		"cache" : false,
		"dataType" : "json",
		"data" : {
			"url" : url
		},
		"success" : function(data) {
			document.title = data.title;
			$("#blog_center").animate({
				"opacity" : 0
			}, "fast", "swing", function() {
				$(this).html(data.blog_center);
				$("#blog_center").animate({
					"opacity" : 100
				}, "fast", "swing");
			});
		},
		"error" : function(jqXHR, textStatus, errorThrown) {
			$("#blog_center").html(
					"<div class=\"alert alert-danger\" role=\"alert\"><strong>ajax加载失败</strong> "
							+ errorThrown + "</div>")
		}
	});
}
/**
 * 为链接绑定pushState方法
 */
$.fn.bindPushState = function() {
	$(this).click(function(evt) {
		evt.preventDefault();
		history.pushState({
			"state_url" : this.href
		}, "", this.href);
		loadUrl(this.href);
	});
	return $(this);
};
/**
 * 显示消息提示
 * 
 * @param msgType
 * @param msg
 */
function alertMsg(msgType, msg) {
	var msgNode = $("<div class=\"alert alert-" + msgType
			+ "\" style=\"display:none\" role=\"alert\">" + msg + "</div>");
	$("#main_div>div").prepend(msgNode);
	msgNode.fadeIn("fast", "linear", function() {
		$(this).delay(3500).fadeToggle(1500, "linear", function() {
			$(this).remove();
		});
	});
}
/**
 * 模态框消息
 * 
 * @param msgType
 * @param title
 * @param msg
 */
function alertModal(msgType, title, msg) {
	var msgNode = $("<div class=\"alert alert-" + msgType
			+ "\" role=\"alert\">" + msg + "</div>");
	$("#msg_div .modal-title").html(title);
	$("#msg_div .modal-body").html(msgNode);
	$("#msg_div").modal("show");
}
/**
 * 获取验证码图片的URL
 * 
 * @return string
 */
function getRcodeUrl() {
	return blogInfo.rcodeUrlTpl.replace(/\[rand\]/, Math.random());
}
/**
 * 用户登录和管理博客按钮的切换
 * 
 * @param isLogin
 *            是否登录的状态int
 */
function updateUserArea(isLogin) {
	if (isLogin == 1)
		$("#user_area")
				.html(
						"<span class=\"glyphicon glyphicon-cog\" aria-hidden=\"true\"></span> 管理博客  <span class=\"caret\"></span>");
	else
		$("#user_area")
				.html(
						"<span class=\"glyphicon glyphicon-user\" aria-hidden=\"true\"></span> 博主登录");
}
/**
 * 导航条激活位置切换
 * 
 * @param selector
 */
function updateNav(selector) {
	$("#main_navbar a.active").removeAttr("class");
	$(selector).addClass("active");
}
/**
 * 登录博客页面，提交表单时触发
 */
function postLoginForm() {
	$
			.ajax({
				"url" : blogInfo.doLoginUrl,
				"method" : "POST",
				"cache" : false,
				"dataType" : "json",
				"data" : {
					"username" : $("#username").val(),
					"pass" : $("#pass").val(),
					"rcode" : $("#rcode").val()
				},
				"success" : function(data) {
					if (!data.success) {
						$("#login_err_tip").html(
								"<strong>出错了</strong> " + data.msg).show();
						$("#rcode_img").attr("src", getRcodeUrl());
					} else {
						// 设置cookie
						var Days = 30, exp = new Date();
						exp.setTime(exp.getTime() + Days * 24 * 60 * 60 * 1000);
						document.cookie = "osid=" + data.osid + "; expires="
								+ exp.toGMTString() + "; path="
								+ blogInfo.blog_context;
						blogInfo.is_login = 1;
						updateUserArea(1);
						$('#login_div').modal('hide');
					}
				},
				"error" : function(jqXHR, textStatus, errorThrown) {
					$("#login_err_tip").html(
							"<strong>ajax加载失败</strong> " + errorThrown).show();
				}
			});
}
/**
 * 准备就绪后博客执行的函数
 * 
 * @param int
 *            nIndex 导航条当前的位置(从0开始)
 * @param function
 *            successFn 加载主体内容的函数
 * 
 */
function blogInit(nIndex) {
	// 数据库连接提示
	if (blogInfo.conn_ok == 0)
		alertMsg("danger", "数据库连接失败");
	// 导航条
	$("#main_navbar a:eq(" + nIndex + ")").addClass("active");
	// 判断是否支持 pushState
	if ("pushState" in history) {
		$("#main_navbar a").bindPushState().click(function() {
			updateNav(this);
		});
		$("#admin_list a:lt(6)").bindPushState();
		// 浏览器前进、后退
		window.addEventListener('popstate', function(e) {
			if (history.state) {
				loadUrl(e.state.state_url);
			}
		}, false);
	}
	// 其他内容替换
	$("#touxiang").html(
			"<img class=\"img-thumbnail\" src=\"" + blogInfo.touxiang_img
					+ "\" width=\"150\" height=\"150\" title=\""
					+ blogInfo.nickname + "\" alt=\"头像\"/>");
	$("#blogname").html(blogInfo.blogname);
	$("#description").html(blogInfo.description);
	// 更新登录状态
	updateUserArea(blogInfo.is_login);
	// 登录界面
	var reloadRcode = function() {
		$("#rcode_img").attr("src", getRcodeUrl());
	}
	reloadRcode();
	$("#rcode_img").click(reloadRcode);
	$("#rcode_new").click(reloadRcode);
	$("#login_div input").focus(function() {
		$("#login_err_tip").hide();
	}).keydown(function(event) {
		if (event.which == 13) {
			event.preventDefault();
			postLoginForm();
		}
	});
	$("#login_div h4").html(blogInfo.blogname + "- 登录");
	$("#login_btn").click(postLoginForm);

	$("#user_area").click(function() {
		if (blogInfo.is_login == 1)
			$(this).parent().toggleClass("open");
		else
			$("#login_div").modal("show");// 弹窗要求登录
	}).parent().mouseenter(function() {
		if (blogInfo.is_login == 1)
			$(this).addClass("open");
	}).mouseleave(function() {
		if (blogInfo.is_login == 1)
			$(this).removeClass("open");
	});
}
function logoutConfirm() {
	var r;
	if (arguments.length > 0)
		r = arguments[0];
	else
		r = confirm("你确定要退出吗?");
	if (r == true) {
		// 删除cookie
		var Days = 5, exp = new Date();
		exp.setTime(exp.getTime() - Days * 24 * 60 * 60 * 1000);
		document.cookie = "osid=123; expires=" + exp.toGMTString() + "; path="
				+ blogInfo.blog_context;
		location.href = blogInfo.logoutUrl;
	}
}
function getCookie(c_name) {
	if (document.cookie.length > 0) {
		c_start = document.cookie.indexOf(c_name + "=")
		if (c_start != -1) {
			c_start = c_start + c_name.length + 1
			c_end = document.cookie.indexOf(";", c_start)
			if (c_end == -1)
				c_end = document.cookie.length
			return unescape(document.cookie.substring(c_start, c_end))
		}
	}
	return ""
}
function loadJsFile(jsFile, fn) {
	var oHead = document.getElementsByTagName("head")[0];
	var jsObject = document.createElement("SCRIPT");
	jsObject.type = "text/javascript";
	jsObject.src = jsFile;
	jsObject.defer = "defer";
	jsObject.onload = fn;
	oHead.appendChild(jsObject);
}