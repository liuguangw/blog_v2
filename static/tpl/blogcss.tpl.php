<?php
if (! defined ( 'APP_PATH' ))
	exit ( 'Access denied!' );
//CSS文件
?>@CHARSET "UTF-8";
body{
	background-image: url(<?php echo $tplData->get('bg_img'); ?>);
	background-repeat: repeat repeat;
}
#main_div {
	min-height: 700px;
	padding-top: 150px;
	background-image: url(<?php echo $tplData->get('top_img'); ?>);
	background-repeat: no-repeat no-repeat;
}
#blog_center,#blog_right{
	margin-top:60px;
}
#blog_center img{
	max-width: 100%;
}
#blog_center a {
	text-decoration:none;
}
.touming {
	opacity: .8;
	filter: alpha(opacity = 80);
}
#blogname{
	color:<?php echo $tplData->get('blogname_color'); ?>;
}
#description{
	margin-top: 0;
	color:<?php echo $tplData->get('descr_color'); ?>;
}
#main_navbar{
	height:30px;
	margin-top: 20px;
}
#main_navbar a {
	text-decoration:none;
	font-weight: bold;
	font-size: 14px;
	margin-right: 1em;
	color: <?php echo $tplData->get('nav_color'); ?>;
	display: inline-block;
	height: 25px;
	vertical-align: top;
	padding-bottom: 6px;
}
#main_navbar a:hover {
	color: <?php echo $tplData->get('nav_active_color'); ?>;
	border-bottom: 4px solid <?php echo $tplData->get('nav_active_color'); ?>;
}
#main_navbar a.active {
	color: <?php echo $tplData->get('nav_active_color'); ?>;
	border-bottom: 4px solid <?php echo $tplData->get('nav_active_color'); ?>;
}
/*登录界面*/
#user_area{
	margin-top: 6px;
}
#login_err_tip{
	display:none;
}
#rcode_img {
	cursor: pointer;
}
#admin_list{
	margin-top: 0;
}