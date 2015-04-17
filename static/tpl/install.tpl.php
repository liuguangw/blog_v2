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
<title>流光博客安装程序</title>
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
<style type="text/css">
body{
	background-image: url(<?php echo $tplData->get('blog_context'); ?>/static/img/blog_bg.jpg);
	background-repeat: repeat repeat;
}
#main_div {
	min-height: 700px;
	padding-top: 150px;
	background-image: url(<?php echo $tplData->get('blog_context'); ?>/static/img/blog_top.jpg);
	background-repeat: no-repeat no-repeat;
}
#install_form{
	margin-top:16px;
}
</style>
<script type="text/javascript">
var forms=[
    ["username","text","用户名","请设置博客管理员用户名"],
    ["nickname","text","博主昵称","输入博主昵称"],
    ["blogname","text","博客名称","输入博客名称"],  
    ["pass1","password","密码","输入密码"],
    ["pass2","password","确认密码","确认密码"] 	
		];
$(document).ready(function(){
	var htmlStr="<div class=\"form-group\">\
					<div id=\"err_tip\" class=\"alert alert-danger\" role=\"alert\" style=\"display: none;\"></div>\
				</div>",i;
	for(i=0;i<forms.length;i++){
		htmlStr+=("<div class=\"form-group form-group-lg\">\
			    <label for=\""+forms[i][0]+"\" class=\"col-md-2 control-label\">"+forms[i][2]+"</label>\
			    <div class=\"col-md-10\">\
			      <input type=\""+forms[i][1]+"\" class=\"form-control\" id=\""+forms[i][0]+"\" placeholder=\""+forms[i][3]+"\">\
			    </div>\
			  </div>");
	}
	$("#install_form").prepend(htmlStr);
	/*判断是否已经安装*/
	var blogInit=<?php echo $tplData->get('blogInit'); ?>;
	if(blogInit){
		$("#installed").on("hidden.bs.modal", function () {
			location.href="<?php echo $tplData->get('blog_context'); ?>/";
		});
		$("#installed").modal("show");
	}
	/*表单验证事件*/
	$("#install_form input").each(function(){
		var inputEle=$(this);
		inputEle.blur(function(){
			if((this.id=="pass2")&&(inputEle.val()!=$("#pass1").val())){
				  $("#err_tip").html("两次输入的密码不一致").show();
				  inputEle.parent().attr("class","col-md-10 has-error");
				  return;
			}
			$.ajax({
				  url: "<?php echo $tplData->get('checkInputUrl'); ?>",
				  type: "POST",
				  data: {
					  input_id:this.id,
					  input_val:inputEle.val()
				},
				  dataType:"json",
				  success: function(data){
					  if(!data.success){
						  $("#err_tip").html(data.msg).show();
						  inputEle.parent().attr("class","col-md-10 has-error");
					  }else
						  inputEle.parent().attr("class","col-md-10 has-success");
				},
				error:function(jqXHR,textStatus,errorThrown ){
					$("#err_tip").html('<strong>异步加载失败 !</strong>'+errorThrown);
				}
			});
		});/*end blur*/
		inputEle.focus(function(){
			var inputParent=inputEle.parent();
			if(inputParent.hasClass("has-error")){
				  $("#err_tip").hide();
			}
			inputParent.attr("class","col-sm-10");
		});
	});/*end each*/
	var submitFn=function(){
		var inputIds=["username","nickname","blogname","pass1","pass2"],i,checkDiv;
		for(i=0;i<inputIds.length;i++){
			checkDiv=$("#"+inputIds[i]).parent();
			if(checkDiv.hasClass("has-error")){
				$("#form_error").modal("show");
				break;
			}
		}
		var modalDiv=$("#on_install");
		modalDiv.find("h4").html("博客正在安装中");
		modalDiv.find("div.modal-body").html("<div class=\"progress\">\
	      	<div class=\"progress-bar progress-bar-striped active\" role=\"progressbar\" aria-valuenow=\"100\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width: 100%\">\
			      <span class=\"sr-only\">正在安装中...</span>\
			</div>\
	      </div>");
		modalDiv.find("button").attr("disabled","disabled");
	    modalDiv.modal("show");
		$.ajax({
			  url: "<?php echo $tplData->get('doInstallUrl'); ?>",
			  type: "POST",
			  data: {
				  "username":$("#username").val(),
				  "nickname":$("#nickname").val(),
				  "blogname":$("#blogname").val(),
				  "pass1":$("#pass1").val(),
				  "pass2":$("#pass2").val()
			},
			  dataType:"json",
			  success: function(data){
				  if(!data.success){
						modalDiv.find("h4").html("出错了");
						modalDiv.find("div.modal-body").html("<div class=\"alert alert-danger\" role=\"alert\">"+data.msg+"</div>");
				  }else{
						modalDiv.find("h4").html("博客安装成功");
						modalDiv.find("div.modal-body").html("<p>\
								博客安装成功，请把配置文件\
								<code>config.inc.php</code>\
								中的\
								<code>blogInit</code>\
								项的值修改为\
								<code>true</code>,完成安装.\
							</p>");
				  }
					modalDiv.find("button").removeAttr("disabled");
			},
			error:function(jqXHR,textStatus,errorThrown ){
				modalDiv.find("h4").html("异步加载失败");
				modalDiv.find("div.modal-body").html("<div class=\"alert alert-danger\" role=\"alert\"><strong>异步加载失败 !</strong>"+errorThrown+"</div>");
				modalDiv.find("button").removeAttr("disabled");
		  }
		});
	};/*end function*/
	$("#install_form button").click(submitFn);/*end click*/
	$("#install_form input").keydown(function(event) {
		if (event.which == 13) {
			event.preventDefault();
			submitFn();
		}
	});
});
</script>
</head>
<body>
<div class="container-fluid" id="main_div">
	<div class="container">
	<div class="panel panel-default">
		<div class="panel-heading">流光博客安装程序</div>
		<div class="panel-body">
				<form class="form-horizontal" id="install_form">
				  <div class="form-group form-group-lg">
				    <div class="col-md-offset-2 col-md-10">
				      <button type="button" class="btn btn-primary btn-lg">立即安装</button>
				    </div>
				  </div>
				</form>
		</div>
	</div>
	<!-- 已安装提示框 -->
			<div id="installed" class="modal fade">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal"
								aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
							<h4 class="modal-title">博客已安装完毕</h4>
						</div>
						<div class="modal-body">
							<p>
								博客已经安装过了，如需重装，请把配置文件
								<code>config.inc.php</code>
								中的
								<code>init</code>
								项的值修改为
								<code>false</code>
							</p>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-primary"
								data-dismiss="modal">返回首页</button>
						</div>
					</div>
					<!-- /.modal-content -->
				</div>
				<!-- /.modal-dialog -->
			</div>
			<!-- /.modal -->
			<!-- 安装过程框 -->
			<div id="on_install" class="modal fade">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal"
								aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
							<h4 class="modal-title">博客安装中</h4>
						</div>
						<div class="modal-body">
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-primary"
								data-dismiss="modal">确定</button>
						</div>
					</div>
					<!-- /.modal-content -->
				</div>
				<!-- /.modal-dialog -->
			</div>
			<!-- 表单填写有错误项 -->
			<div id="form_error" class="modal fade">
				<div class="modal-dialog">
					<div class="modal-content">
						<div class="modal-header">
							<button type="button" class="close" data-dismiss="modal"
								aria-label="Close">
								<span aria-hidden="true">&times;</span>
							</button>
							<h4 class="modal-title">无法安装</h4>
						</div>
						<div class="modal-body">
							<p>您的输入中含有错误项，必需填写正确,才能进行安装.
							</p>
						</div>
						<div class="modal-footer">
							<button type="button" class="btn btn-primary"
								data-dismiss="modal">确定</button>
						</div>
					</div>
					<!-- /.modal-content -->
				</div>
				<!-- /.modal-dialog -->
			</div>
	</div>
</div>
</body>
</html>