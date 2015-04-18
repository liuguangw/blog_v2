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
		if ("pushState" in history) {
			evt.preventDefault();
			history.pushState({
				"state_url" : this.href
			}, "", this.href);
			loadUrl(this.href);
		}
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
		// 浏览器前进、后退
		window.addEventListener('popstate', function(e) {
			if (history.state) {
				loadUrl(e.state.state_url);
			}
		}, false);
	}
	$("#main_navbar a").bindPushState().click(function() {
		updateNav(this);
	});
	// 管理连接生成
	var adminUrlarr = [ {
		"name" : "博客设置",
		"action" : "sets",
		"type" : 1
	}, {
		"name" : "分类管理",
		"action" : "types",
		"type" : 1
	}, {
		"name" : "标签管理 ",
		"action" : "tags",
		"type" : 1
	}, {
		"name" : "文件管理",
		"action" : "files",
		"type" : 1
	}, {
		"name" : "发表文章",
		"action" : "postTopic",
		"type" : 1
	}, {
		"type" : 0
	}, {
		"name" : "博客环境信息",
		"action" : "env",
		"type" : 1
	}, {
		"name" : "注销登录",
		"action" : "javascript:logoutConfirm()",
		"type" : 2
	}, ], adminListHtml = "", i;
	for (i = 0; i < adminUrlarr.length; i++) {
		if (adminUrlarr[i].type == 0)
			adminListHtml += "<li role=\"presentation\" class=\"divider\"></li>";
		else if (adminUrlarr[i].type == 1) {
			adminListHtml += "<li role=\"presentation\">";
			adminListHtml += ("<a role=\"menuitem\" tabindex=\"-1\" href=\""
					+ blogInfo.adminUrlTpl.replace(/\[action\]/,
							adminUrlarr[i].action) + "\">"
					+ adminUrlarr[i].name + "</a>");
			adminListHtml += "</li>";
		} else {
			adminListHtml += "<li role=\"presentation\">";
			adminListHtml += ("<a role=\"menuitem\" tabindex=\"-1\" href=\""
					+ adminUrlarr[i].action + "\">" + adminUrlarr[i].name + "</a>");
			adminListHtml += "</li>";
		}
	}
	$("#admin_list").html(adminListHtml);
	$("#admin_list a:lt(6)").bindPushState();
	/* 其他内容替换 */
	$("#touxiang").html(
			"<img class=\"img-thumbnail\" src=\"" + blogInfo.touxiang_img
					+ "\" width=\"150\" height=\"150\" title=\""
					+ blogInfo.nickname + "\" alt=\"头像\"/>");
	$("#blogname").html(blogInfo.blogname);
	$("#description").html(blogInfo.description);
	/* 更新登录状态 */
	updateUserArea(blogInfo.is_login);
	/* 登录界面代码生成 */
	var loginDivBody = "<form class=\"form-horizontal\">", loginDivFooter = "<button type=\"button\" class=\"btn btn-default\"\
								data-dismiss=\"modal\">取消</button>\
							<button id=\"login_btn\" type=\"button\" class=\"btn btn-primary\">登录博客</button>";
	loginDivBody += "<div class=\"row\">\
			<div class=\"col-md-12\">\
				<div id=\"login_err_tip\" class=\"alert alert-danger\"\
					role=\"alert\">登录错误提示</div>\
			</div>\
	</div>\
	<div class=\"form-group\">\
		<label for=\"username\" class=\"col-sm-2 control-label\">用户名</label>\
		<div class=\"col-md-9\">\
			<input type=\"text\" class=\"form-control\" id=\"username\"\
				placeholder=\"用户名\">\
		</div>\
	</div>\
	<div class=\"form-group\">\
		<label for=\"pass\" class=\"col-md-2 control-label\">密码</label>\
		<div class=\"col-md-9\">\
			<input type=\"password\" class=\"form-control\" id=\"pass\"\
				placeholder=\"密码\">\
		</div>\
	</div>\
	<div class=\"form-group\">\
		<label for=\"rcode\" class=\"col-md-2 control-label\">验证码</label>\
		<div class=\"col-md-6\">\
			<input type=\"text\" class=\"form-control\" id=\"rcode\"\
				placeholder=\"验证码\">\
		</div>\
		<div class=\"col-md-3\">\
			<p class=\"form-control-static\">\
				<a id=\"rcode_new\" href=\"javascript:void(0)\" title=\"换一张\">换一张？</a>\
			</p>\
		</div>\
	</div>\
	<div class=\"form-group\">\
		<div class=\"col-md-offset-2 col-md-9\">\
			<img id=\"rcode_img\" src=\"\" draggable=\"false\" alt=\"验证码\"\
				title=\"点击刷新\" />\
		</div>\
	</div>";
	loginDivBody += "</form>";
	$("#login_div").html(
			createModalDiv(blogInfo.blogname + "-登录", loginDivBody,
					loginDivFooter));
	/* 消息提示模态框 */
	$("#msg_div").html(
			createModalDiv("Modal title",
					"<div class=\"alert alert-danger\" role=\"alert\"></div>",
					""));
	/* 登录界面验证码刷新 */
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
/**
 * 模态框代码生成
 * 
 * @param modal_title
 *            标题
 * @param modal_body
 *            内容部分
 * @param modal_footer
 *            底部
 * @returns String
 */
function createModalDiv(modal_title, modal_body, modal_footer) {
	var divHtml = "<div class=\"modal-dialog\">\
					<div class=\"modal-content\">\
						<div class=\"modal-header\">\
							<button type=\"button\" class=\"close\" data-dismiss=\"modal\"\
								aria-label=\"Close\">\
								<span aria-hidden=\"true\">&times;</span>\
							</button>\
							<h4 class=\"modal-title\">"
			+ modal_title + "</h4>\
						</div>";
	divHtml += ("<div class=\"modal-body\">" + modal_body + "</div>");
	if (modal_footer != "")
		divHtml += ("<div class=\"modal-footer\">" + modal_footer + "</div>");
	divHtml += "</div></div>";
	return divHtml;
}
/**
 * 刷新右侧的区域
 */
function refreshRight() {
	$.ajax({
		"url" : blogInfo.refreshRUrl,
		"method" : "POST",
		"cache" : false,
		"dataType" : "json",
		"success" : function(data) {
			$("#blog_right").animate({
				"opacity" : 0
			}, "fast", "swing", function() {
				$(this).html(data.html);
				$("#blog_right").animate({
					"opacity" : 100
				}, "fast", "swing");
			});
		},
		"error" : function(jqXHR, textStatus, errorThrown) {
		}
	});
}