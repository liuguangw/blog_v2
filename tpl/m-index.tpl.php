<?php
if (! defined ( 'APP_PATH' ))
	exit ( 'Access denied!' );
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo $tplData->get('title'); ?></title>
<meta name="keywords"
	content="<?php echo $tplData->get('blog_keywords'); ?>">
<meta name="description"
	content="<?php echo $tplData->get('search_abouts'); ?>">

<!-- Sets initial viewport load and disables zooming  -->
<meta name="viewport" content="initial-scale=1, maximum-scale=1">

<!-- Makes your prototype chrome-less once bookmarked to your phone's home screen -->
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black">

<!-- Include the compiled Ratchet CSS -->
<link type="text/css"
	href="<?php echo $tplData->get('blog_context'); ?>/static/mobile/css/frozen.css"
	rel="stylesheet">
<style type="text/css">
#blog_header {
	padding-left: 15px;
	line-height: 45px;
	font-size: 16px;
	color: #00A5E3;
	position: fixed;
	top: 0;
	height: 45px;
	-webkit-box-sizing: border-box;
	width: 100%;
	z-index: 10;
	background-color: #f8f9fa;
}
#blog_header a{
	padding-right: 30px;
}
.blog_desc{
	padding:13px;
}
.ui-tab-nav {
	top: 45px;
}
body {
	padding-top: 45px;
}
</style>
<script type="text/javascript"
	src="<?php echo $tplData->get('blog_context'); ?>/static/mobile/js/zepto.min.js"></script>
<script type="text/javascript"
	src="<?php echo $tplData->get('blog_context'); ?>/static/mobile/js/frozen.js"></script>
<script type="text/javascript">
function replacePage(toIndex,pageTitle){
	var pageUrls=[];
	if ("pushState" in history) {
		var state = {
			"state_url" : url,
		};
		history.pushState(state, "", state.state_url);
		loadUrl(state, true);
	}
	document.title=pageTitle;
}
$(document).ready(function(){
	var tab = new fz.Scroll(".ui-tab", {
	    role: "tab",
	    interval: 3000
	    //autoplay: true
	}),
	centerList=document.getElementById("blog_contents").children,
	blog_data;
	var loading="<div class=\"ui-loading-wrap\">\
			    <p>加载中</p>\
			    <i class=\"ui-loading\"></i>\
			</div>";
	tab.currentPage = <?php echo $tplData->get('currentPage') ?>;
	$(tab.nav.children[0]).removeClass("current");
	$(tab.nav.children[tab.currentPage]).addClass("current");
	tab.scrollTo(-tab.itemWidth*tab.currentPage,0);
	tab.on("beforeScrollStart", function(fromIndex, toIndex) {
		$.each(centerList,function(index, item){
			item.innerHTML=loading;
		});
		$.ajax({
			"url" :"<?php echo $tplData->get ( 'pushurl');?>",
			"method" : "POST",
			"cache" : false,
			"dataType" : "json",
			"data" : {
				"toIndex" : toIndex
			},
			"success" : function(data) {
				blog_data=data;
			},
			"error" : function(jqXHR, textStatus, errorThrown) {
				blog_data={
						"success":false,
						"html":"<div class=\"ui-tooltips ui-tooltips-warn\">\
			    <div class=\"ui-tooltips-cnt ui-tooltips-cnt-link ui-border-b\">\
		        <i></i>加载失败，"+errorThrown+"，请检查你的网路设置。\
		    </div>\
		</div>"
				}
				$("#blog_center").html(
						"<div class=\"alert alert-danger\" role=\"alert\"><strong>ajax加载失败</strong> "
								+ errorThrown + "</div>")
			}
		});/*end ajax*/
	});
	tab.on('scrollEnd', function() {
		if(blog_data.success){
			replacePage(tab.currentPage,blog_data.title)
		}
	    $(centerList[tab.currentPage]).html(blog_data.html);
	});
});
</script>
</head>
<body>
	<h2 class="ui-border-b" id="blog_header">
		<a href="<?php echo $tplData->get('indexurl'); ?>"><?php echo $tplData->get('blogname'); ?></a>
	</h2>
	<div class="ui-nowrap blog_desc"><?php echo $tplData->get('description'); ?></div>
	<div class="content">
		<div class="ui-tab">
		    <ul class="ui-tab-nav ui-border-b">
		        <li class="current">文章一览</li>
		        <li>文章分类</li>
		        <li>文章归档</li>
		    </ul>
		    <ul id="blog_contents" class="ui-tab-content" style="width:300%">
		        <li class="current"></li>
		        <li></li>
		        <li></li>
		    </ul>
		</div>
	</div>
</body>
</html>