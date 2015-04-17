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
<title>出错啦 !</title>
<!-- Bootstrap -->
<link href="<?php echo $tplData->get('blog_context'); ?>/static/css/bootstrap.min.css"
	rel="stylesheet" />
<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn"t work if you view the page via file:// -->
<!--[if lt IE 9]>
      <script src="<?php echo $tplData->get('blog_context'); ?>/static/js/html5shiv3.7.2.min.js"></script>
      <script src="<?php echo $tplData->get('blog_context'); ?>/static/js/respond1.4.2.min.js"></script>
<![endif]-->
</head>
<body>
	<div class="container">
	<div class="panel panel-danger">
	<div class="panel-heading">404 错误</div>
	  <div class="panel-body">对不起，您访问的地址不存在。</div>
  <div class="panel-footer"><a href="<?php echo $tplData->get('blog_context'); ?>/">返回首页</a></a></div>
	</div>
	</div>
</body>
</html>