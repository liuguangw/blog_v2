<?php

namespace liuguang\blog\view;

use liuguang\mvc\Application;
use liuguang\mvc\UrlHandler;
use liuguang\blog\model\User;

/**
 * 留言页面
 *
 * @author liuguang
 *        
 */
class Liuyan {
	private $db;
	private $tablePre;
	public function __construct(\PDO $db, $tablePre) {
		$this->db = $db;
		$this->tablePre = $tablePre;
	}
	public function getHtml($page) {
		$db = $this->db;
		$tablePre = $this->tablePre;
		$app = Application::getApp ();
		$appConfig = $app->getAppConfig ();
		$urlHandler = $app->getUrlHandler ();
		date_default_timezone_set ( $appConfig->get ( 'timeZone' ) );
		$html = '<div class="panel panel-default" id="reply_div">
          <div class="panel-heading">留言列表第' . $page . '页</div>';
		$html .= $this->getList ( $urlHandler, $page );
		$html .= '</div>';
		// 判断博客是否允许留言
		$stm = $db->query ( 'SELECT * FROM ' . $tablePre . 'config WHERE t_key=\'allow_liuyan\'' );
		$rst = $stm->fetch ();
		if ($rst ['t_value'] == 1)
			$html .= $this->getEditor ( $urlHandler, $page );
		else
			$html .= '<div class="panel panel-default">
  <div class="panel-body">博主已关闭留言功能</div>
</div>';
		return $html;
	}
	public function getTitle($page) {
		$stm = $this->db->query ( 'SELECT t_value FROM ' . $this->tablePre . 'config WHERE t_key=\'blogname\'' );
		$rst = $stm->fetch ();
		return $rst ['t_value'] . '-留言-第' . $page . '页';
	}
	/**
	 * 获取留言处的编辑器
	 */
	private function getEditor(UrlHandler $urlHandler, $page) {
		$app = Application::getApp ();
		$appConfig = $app->getAppConfig ();
		$ue_path = $appConfig->get ( 'app_pub_context' ) . '/ueditor/';
		$configUrl = $urlHandler->createUrl ( 'ajax/Ueditor', 'config', array (), false );
		$uploadimageUrl = $urlHandler->createUrl ( 'ajax/Ueditor', 'uploadimage', array (), false );
		$uploadscrawlUrl = $urlHandler->createUrl ( 'ajax/Ueditor', 'uploadscrawl', array (), false );
		$uploadfileUrl = $urlHandler->createUrl ( 'ajax/Ueditor', 'uploadfile', array (), false );
		$doLiuyanUrl = $urlHandler->createUrl ( 'ajax/Liuyan', 'doReply', array (), false );
		$loadLiuyanUrl = $urlHandler->createUrl ( 'ajax/Liuyan', 'loadReply', array (), false );
		$html = '<div class="panel panel-default">
  <div class="panel-body">
    <form class="form-horizontal" id="post_topic_form">';
		$html .= '<div class="row">
<div class="col-sm-12">
<input type="text" class="form-control" id="t_user" placeholder="请输入您的名字或者昵称">
</div>
</div>';
		$html .= '<div class="row">
<div class="col-sm-12">
    <!-- 加载编辑器的容器 -->
    <script class="form-control-static" id="t_contents" type="text/plain">留言内容</script>
    <script type="text/javascript">
    var ue;
    if(!blogInfo.load_js.ueditor){
        loadJsFile("' . $ue_path . 'ueditor.config.js",function(){
            loadJsFile("' . $ue_path . 'ueditor.all.min.js",function(){
                blogInfo.load_js.ueditor=true;
            	UE.Editor.prototype._bkGetActionUrl = UE.Editor.prototype.getActionUrl;
				UE.Editor.prototype.getActionUrl = function(action) {
					if (action == "config") {
				        return "' . $configUrl . '";
				    }
				    else if (action == "uploadimage") {
				        return "' . $uploadimageUrl . '";
				    } else if (action == "uploadscrawl") {
				        return "' . $uploadscrawlUrl . '";
				    } else if (action == "uploadfile") {
				        return "' . $uploadfileUrl . '";
				    } else {
				        return this._bkGetActionUrl.call(this, action);
				    }
				}
            	/**/
                ue = UE.getEditor("t_contents",
                    {"UEDITOR_HOME_URL":"' . $ue_path . '",
                    "initialFrameHeight":280});
            });
        });
    }
    else{
        ue.destroy();
        ue = UE.getEditor("t_contents",
                    {"UEDITOR_HOME_URL":"' . $ue_path . '",
                    "initialFrameHeight":280});
    }
    </script>
</div></div>';
		$html .= ('<div class="row">
<div class="col-sm-2 col-sm-offset-10">
    <button id="post_topic_btn" type="button" class="btn btn-primary">发表留言</button>
</div>
</div>');
		/* 发表留言的事件绑定 */
		$html .= ('<script type="text/javascript">
    $("#post_topic_btn").click(function(){
        var r=confirm("确定要发表吗?");
        if(!r)
            return;
        $.ajax({
            "url" : "' . $doLiuyanUrl . '",
            "method" : "POST",
            "cache" : false,
            "dataType" : "json",
            "data" : {
                "t_user":$("#t_user").val(),
                "t_contents":ue.getContent()
            },
            "success" : function(data) {
                if(data.success){
                /*刷新底部留言列表*/
                $.ajax({
                            "url" : "' . $loadLiuyanUrl . '",
                            "method" : "POST",
                            "cache" : false,
                            "dataType" : "json",
                            "data" : {
                                "page":' . $page . '
                            },
                            "success" : function(data) {
							ue.setContent("");
                            $("#reply_div").html("<div class=\"panel-heading\">留言列表第' . $page . '页</div>"+data.msg);
							SyntaxHighlighter.highlight();
                            },
                            "error" : function(jqXHR, textStatus, errorThrown) {
                                alertModal("danger","异步失败",errorThrown);/*异步失败*/
                            }
                        });/*end ajax*/
                }
                else
                    alertModal("danger","留言失败",data.msg);
            },
            "error" : function(jqXHR, textStatus, errorThrown) {
                alertModal("danger","异步失败",errorThrown);/*异步失败*/
            }
        });/*end ajax*/
    });
    </script>');
		$html .= ('</form></div></div>');
		return $html;
	}
	public function getList(UrlHandler $urlHandler, $page) {
		$db = $this->db;
		$tablePre = $this->tablePre;
		$stm = $db->query ( 'SELECT COUNT(*) as t_num FROM ' . $tablePre . 'liuyan' );
		$rst = $stm->fetch ();
		if ($rst ['t_num'] == 0) {
			return '<div class="panel-body">目前还没有留言</div>';
		}
		$limit = 12; // 每页最多显示12条
		$t_num = $rst ['t_num'];
		$page_num = ceil ( $t_num / $limit ); // 总页码数
		if (($page < 1) || ($page > $page_num))
			return '<div class="alert alert-danger" role="alert">当前页面不存在</div>';
		$limit0 = ($page - 1) * $limit;
		// 获取博主昵称
		$stm = $db->query ( 'SELECT t_value FROM ' . $tablePre . 'config WHERE t_key=\'nickname\'' );
		$rst = $stm->fetch ();
		$nickname = $rst ['t_value'];
		$html = '<ul class="list-group" id="reply_list">';
		$stm = $db->query ( 'SELECT * FROM ' . $tablePre . 'liuyan ORDER BY t_id DESC LIMIT ' . $limit0 . ', ' . $limit );
		$user = new User ();
		$isAdmin = $user->checkAdmin ( $db, $tablePre );
		$offset_num = $t_num % $limit;
		$i = ($page_num + 1 - $page) * $limit;
		if ($offset_num != 0)
			$i -= ($limit - $offset_num);
		while ( $tmp = $stm->fetch () ) {
			$reply_nick = $tmp ['t_user'];
			if ($tmp ['is_admin_post'] == 1)
				$reply_nick = $nickname;
			$html .= ('<li class="list-group-item">
                    <h4 class="list-group-item-heading">[' . $i . '楼]
                    <span class="glyphicon glyphicon-user" aria-hidden="true"></span> ' . htmlspecialchars ( $reply_nick ) . '&nbsp;&nbsp;&nbsp;[' . date ( 'Y-m-d H:i:s', $tmp ['post_time'] ) . ']');
			if ($isAdmin) {
				$html .= ('  <a href="javascript:void(0)" data-t_id="' . $tmp ['t_id'] . '">[删除留言]</a>');
			}
			$html .= ('</h4><p class="list-group-item-heading">' . $tmp ['t_content'] . '</p></li>');
			$i --;
		}
		// 分页
		$fenyeV = new Fenye ();
		$urlTpl = $urlHandler->createUrl ( 'web/BlogLiuyan', 'index', array (
				'page' => '%d' 
		) );
		$html .= $fenyeV->getNav ( $urlTpl, $page, $page_num );
		$html .= '<script type="text/javascript">
            $("#f_fenye a").each(function(){
                if($(this).parent().is("li[class]"))
                    $(this).click(function(evt){
                        evt.preventDefault();
                    });
                else
                    $(this).bindPushState();
            });';
		$blogContext = substr ( $_SERVER ['SCRIPT_NAME'], 0, - 1 - strlen ( MVC_ENTRY_NAME ) );
		$ue_path = $blogContext . '/static/ueditor/';
		$html .= ('/*高亮插件*/
			if(!blogInfo.load_js.shCore){
				loadJsFile("' . $ue_path . 'third-party/SyntaxHighlighter/shCore.js",function(){
					var oHead = document.getElementsByTagName("head")[0];
					var cssObject = document.createElement("link");
					cssObject.rel="stylesheet";
					cssObject.type="text/css";
					cssObject.href="' . $ue_path . 'third-party/SyntaxHighlighter/shCoreDefault.css";
					cssObject.onload=function(){
						SyntaxHighlighter.highlight();
					};
					oHead.appendChild(cssObject);
					blogInfo.load_js.shCore=true;
				});
			}
			else
				SyntaxHighlighter.highlight();');
		$del_liuyan_url = $urlHandler->createUrl ( 'ajax/AdminLiuyan', 'delete', array (), false );
		$loadLiuyanUrl = $urlHandler->createUrl ( 'ajax/Liuyan', 'loadReply', array (), false );
		if ($isAdmin)
			$html .= ('/*删除留言 */
				$("#reply_list>li").each(function(){
				$(this).find("h4:first>a").click(function(){
					var r=confirm("是否删除这条留言?"),aNode=$(this);
			        if(!r)
			            return;
			        $.ajax({
			            "url" : "' . $del_liuyan_url . '",
			            "method" : "POST",
			            "cache" : false,
			            "dataType" : "json",
			            "data" : {
			                "reply_id":aNode.attr("data-t_id")
			            },
			            "success" : function(data) {
			                if(data.success){
			                /*刷新底部评论列表*/
			                $.ajax({
			                            "url" : "' . $loadLiuyanUrl . '",
			                            "method" : "POST",
			                            "cache" : false,
			                            "dataType" : "json",
			                            "data" : {
			                                "page":' . $page . '
			                            },
			                            "success" : function(data) {
			                                alertModal("success","执行成功","你已成功删除本条留言");
                            			$("#reply_div").html("<div class=\"panel-heading\">留言列表</div>"+data.msg);
			                            },
			                            "error" : function(jqXHR, textStatus, errorThrown) {
			                                alertModal("danger","异步失败",errorThrown);/*异步失败*/
			                            }
			                        });/*end ajax*/
			                }
			                else
			                    alertModal("danger","删除留言失败",data.msg);
			            },
			            "error" : function(jqXHR, textStatus, errorThrown) {
			                alertModal("danger","异步失败",errorThrown);/*异步失败*/
			            }
			        });/*end ajax*/
					});/*end click*/
				});
		</script>');
		else
			$html .= '</script>';
		return $html;
	}
}