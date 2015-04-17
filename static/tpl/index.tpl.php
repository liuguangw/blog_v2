<?php
if (! defined ( 'APP_PATH' ))
	exit ( 'Access denied!' );
?>
<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo $tplData->get('title'); ?></title>
<!-- Bootstrap -->
<link href="<?php echo $tplData->get('blog_context'); ?>/static/css/bootstrap.min.css"
	rel="stylesheet" />
<script type="text/javascript"
	src="<?php echo $tplData->get('blog_context'); ?>/static/js/jquery1.11.2.min.js"></script>
<script type="text/javascript"
	src="<?php echo $tplData->get('blog_context'); ?>/static/js/bootstrap.min.js"></script>
<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn"t work if you view the page via file:// -->
<!--[if lt IE 9]>
      <script src="<?php echo $tplData->get('blog_context'); ?>/static/js/html5shiv3.7.2.min.js"></script>
      <script src="<?php echo $tplData->get('blog_context'); ?>/static/js/respond1.4.2.min.js"></script>
<![endif]-->
<link href="<?php echo $tplData->get('blogCsspath'); ?>" rel="stylesheet" />
<script type="text/javascript"
	src="<?php echo $tplData->get('blog_context'); ?>/static/js/blog.js"></script>
<script type="text/javascript">
var blogInfo=<?php echo $tplData->get('blogInfo'); ?>;// 存储博客的基本信息和登录状态
$(document).ready(function(){
	/*设置导航条初始位置*/
	blogInit(<?php echo $tplData->get('nIndex'); ?>);
})
</script>
</head>
<body>
	<div id="main_div" class="container-fluid">
		<div class="container">
			<div id="blog_header" class="row">
				<div id="touxiang" class="col-md-2"></div>
				<div class="col-md-10">
					<div class="row">
						<div class="col-md-5">
							<h2 id="blogname">博客名称</h2>
						</div>
					</div>
					<!-- /.row -->
					<div class="row">
						<div class="col-md-7">
							<h3 id="description">博客说明文本</h3>
						</div>
					</div>
					<!-- /.row -->
					<div class="row">
						<div class="col-md-10">
							<!-- 导航条 -->
							<div id="main_navbar">
							<a href="<?php echo $tplData->get('blogIndexUrl'); ?>">博客首页</a>
							<a href="<?php echo $tplData->get('blogListUrl'); ?>">文章一览</a>
							<a href="<?php echo $tplData->get('blogTypesUrl'); ?>">文章类别</a>
							<a href="<?php echo $tplData->get('blogArchsUrl'); ?>">文章归档</a>
							<a href="<?php echo $tplData->get('blogLiuyanUrl'); ?>">在线留言</a>
							<a href="<?php echo $tplData->get('blogAboutUrl'); ?>">关于本站</a>
							</div>
						</div>
						<div class="col-md-2 dropdown">
							<button id="user_area" type="button" class="btn btn-default">
								<span class="glyphicon glyphicon-user" aria-hidden="true"></span>
								博主登录
							</button>
							<ul id="admin_list" class="dropdown-menu" role="menu">
								<li role="presentation">
									<a role="menuitem" tabindex="-1"
										href="<?php echo $tplData->get('adminSetsUrl'); ?>">博客设置</a>
								</li>
								<li role="presentation">
									<a role="menuitem" tabindex="-1"
										href="<?php echo $tplData->get('adminTypesUrl'); ?>">分类管理</a>
								</li>
								<li role="presentation">
									<a role="menuitem" tabindex="-1"
										href="<?php echo $tplData->get('adminTagsUrl'); ?>">标签管理</a>
								</li>
								<li role="presentation">
									<a role="menuitem" tabindex="-1"
										href="<?php echo $tplData->get('adminFilesUrl'); ?>">文件管理</a>
								</li>
								<li role="presentation">
									<a role="menuitem" tabindex="-1"
										href="<?php echo $tplData->get('postTopicUrl'); ?>">发表文章</a>
								</li>
								<li role="presentation" class="divider"></li>
								<li role="presentation">
									<a role="menuitem" tabindex="-1"
										href="<?php echo $tplData->get('adminEnvUrl'); ?>">博客环境信息</a>
								</li>
								<li role="presentation">
									<a role="menuitem" tabindex="-1"
										href="javascript:logoutConfirm()">注销登录</a>
								</li>
							</ul>
						</div>
					</div>
					<!-- /.row -->
				</div>
			</div>
			<!-- /#blog_header -->
			<!-- 博客登录界面 -->
			<div id="login_div" class="modal fade" role="dialog"
				aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal"
								aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
							<h4 class="modal-title">博客名称</h4>
						</div>
						<div class="modal-body">
							<form class="form-horizontal">
								<div class="row">
									<div class="col-md-12">
										<div id="login_err_tip" class="alert alert-danger"
											role="alert">登录错误提示</div>
									</div>
								</div>
								<div class="form-group">
									<label for="username" class="col-sm-2 control-label">用户名</label>
									<div class="col-md-9">
										<input type="text" class="form-control" id="username"
											placeholder="用户名">
									</div>
								</div>
								<div class="form-group">
									<label for="pass" class="col-md-2 control-label">密码</label>
									<div class="col-md-9">
										<input type="password" class="form-control" id="pass"
											placeholder="密码">
									</div>
								</div>
								<div class="form-group">
									<label for="rcode" class="col-md-2 control-label">验证码</label>
									<div class="col-md-6">
										<input type="text" class="form-control" id="rcode"
											placeholder="验证码">
									</div>
									<div class="col-md-3">
										<p class="form-control-static">
											<a id="rcode_new" href="javascript:void(0)" title="换一张">换一张？</a>
										</p>
									</div>
								</div>

								<div class="form-group">
									<div class="col-md-offset-2 col-md-9">
										<img id="rcode_img" src="" draggable="false" alt="验证码"
											title="点击刷新" />
									</div>
								</div>
							</form>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-default"
								data-dismiss="modal">取消</button>
							<button id="login_btn" type="button" class="btn btn-primary">登录博客</button>
						</div>
					</div>
					<!-- /.modal-content -->
				</div>
				<!-- /.modal-dialog -->
			</div>
			<!-- /.modal -->
			<!-- end 登录界面 -->
			<!-- 消息提示模态框 -->
			<div id="msg_div" class="modal fade" role="dialog" aria-hidden="true">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal"
								aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
							<h4 class="modal-title" id="gridSystemModalLabel">Modal title</h4>
						</div>
						<div class="modal-body">
							<div class="alert alert-danger" role="alert"></div>
						</div>
					</div>
				</div>
			</div>
			<!-- /.modal -->
			<div class="row">
				<div id="blog_center" class="col-md-10"><?php echo $tplData->get('blog_center'); ?></div>
				<!-- 右侧  -->
				<div id="blog_right" class="col-md-2"><?php echo $tplData->get('blog_right'); ?></div>
			</div>
		</div>
	</div>
</body>
</html>