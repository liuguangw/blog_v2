<?php

namespace liuguang\blog\view;

use liuguang\mvc\Application;
use liuguang\blog\model\User;

/**
 * 文章页面
 *
 * @author liuguang
 *        
 */
class TopicPage {
	private $db;
	private $tablePre;
	private $t_id;
	private $title;
	public function __construct(\PDO $db, $tablePre, $t_id) {
		$this->db = $db;
		$this->tablePre = $tablePre;
		$this->t_id = $t_id;
	}
	public function getHtml() {
		$db = $this->db;
		$tablePre = $this->tablePre;
		$t_id = $this->t_id;
		// 判断文章id是否存在
		$sql = 'SELECT COUNT(*) AS t_num FROM ' . $tablePre . 'topic WHERE t_id=' . $t_id;
		$stm = $db->query ( $sql );
		$rst = $stm->fetch ();
		if ($rst ['t_num'] == 0) {
			$this->title = '文章不存在';
			return '<div class="alert alert-danger" role="alert">文章不存在</div>';
		}
		$app = Application::getApp ();
		$appConfig = $app->getAppConfig ();
		$urlHandler = $app->getUrlHandler ();
		date_default_timezone_set ( $appConfig->get ( 'timeZone' ) );
		// 获取帖子的标签数量
		$sql = 'SELECT COUNT(*) AS tag_count FROM ' . $tablePre . 'topic_tag WHERE topic_id=' . $t_id;
		$stm = $db->query ( $sql );
		$rst = $stm->fetch ();
		$taglist = '';
		if ($rst ['tag_count'] != 0) {
			// 获取标签列表
			$sql = 'SELECT t_id,t_name FROM ' . $tablePre . 'tag WHERE t_id IN (SELECT tag_id FROM ' . $tablePre . 'topic_tag WHERE topic_id=' . $t_id . ')';
			$stm = $db->query ( $sql );
			while ( $tmp = $stm->fetch () ) {
				$tagUrl = $urlHandler->createUrl ( 'web/Tag', 'index', array (
						't_id' => $tmp ['t_id'],
						'page' => 1 
				) );
				$taglist .= ('&nbsp;&nbsp;<a href="' . $tagUrl . '"><span class="label label-info">' . $tmp ['t_name'] . '</span></a>');
			}
		}
		$nodeTpl = '<div class="panel panel-default">
  <div class="panel-heading">%s</div>
  <div class="panel-body">
    <p id="topic_head_info">发表于:%s&nbsp;%s 浏览数:%s&nbsp; 所属分类:&nbsp; <a href="%s">%s</a></p>
    <hr/>
    %s
	<hr/>
	%s
  </div>
  <div id="tag_info" class="panel-footer">
    文章标签:%s
  </div>
</div>';
		$sql = 'SELECT ' . $tablePre . 'topic.*,' . $tablePre . 'leibie.t_name FROM ' . $tablePre . 'topic INNER JOIN ' . $tablePre . 'leibie on ' . $tablePre . 'topic.t_id=' . $t_id . ' AND ' . $tablePre . 'topic.leibie_id=' . $tablePre . 'leibie.t_id';
		$stm = $db->query ( $sql );
		$tmp = $stm->fetch ();
		$stm = null;
		$db->exec ( 'UPDATE ' . $tablePre . 'topic SET view_num=view_num+1 WHERE t_id=' . $t_id );
		$this->title = $tmp ['t_title'];
		$last_update_info = '';
		if ($tmp ['last_update'] != 0) {
			$last_update_info = '&nbsp;&nbsp;最后修改于:' . date ( 'Y-m-d H:i:s', $tmp ['last_update'] );
		}
		$typeUrl = $urlHandler->createUrl ( 'web/TocType', 'index', array (
				't_id' => $tmp ['leibie_id'] 
		) );
		$shareCode='<div class="bdsharebuttonbox">
   <a href="javascript:void(0)" class="bds_more" data-cmd="more">分享到：</a>
   <a href="javascript:void(0)" class="bds_qzone" data-cmd="qzone" title="分享到QQ空间">QQ空间</a>
   <a href="javascript:void(0)" class="bds_tsina" data-cmd="tsina" title="分享到新浪微博">新浪微博</a>
   <a href="javascript:void(0)" class="bds_tqq" data-cmd="tqq" title="分享到腾讯微博">腾讯微博</a>
   <a href="javascript:void(0)" class="bds_weixin" data-cmd="weixin" title="分享到微信">微信</a>
   <a href="javascript:void(0)" class="bds_tieba" data-cmd="tieba" title="分享到百度贴吧">百度贴吧</a>
   <a href="javascript:void(0)" class="bds_renren" data-cmd="renren" title="分享到人人网">人人网</a>
  </div> 
<script type="text/javascript">
window._bd_share_config = {
  "common": {
    "bdSnsKey": {},
    "bdText": "",
    "bdMini": "2",
    "bdMiniList": false,
    "bdPic": "",
    "bdStyle": "0",
    "bdSize": "16"
  },
  "share": {
    "bdSize": 16
  }
};
if(!blogInfo.load_js.baiduShare)
	loadJsFile("http://bdimg.share.baidu.com/static/api/js/share.js?cdnversion="+~(-new Date()/36e5),function(){
		blogInfo.load_js.baiduShare=true;
	});
else
	window._bd_share_main.init();
</script>';
		$html = sprintf ( $nodeTpl, $tmp ['t_title'], date ( 'Y-m-d H:i:s', $tmp ['post_time'] ), $last_update_info, $tmp ['view_num'], $typeUrl, $tmp ['t_name'], $tmp ['t_content'],$shareCode, $taglist );
		$edit_topic_url = $urlHandler->createUrl ( 'web/BlogAdmin', 'editTopic', array (
				't_id' => $t_id 
		) );
		$html .= ('<script type="text/javascript">
            if(blogInfo.is_login==1)
                $("#topic_head_info").append("&nbsp; <a href=\"' . $edit_topic_url . '\"><span class=\"label label-primary\">管理文章</span></a>");
            $("#topic_head_info a").bindPushState();
            $("#tag_info a").bindPushState();
        </script>');
		// 回复列表
		$html .= '<div class="panel panel-default" id="reply_div">
          <div class="panel-heading">回复列表</div>';
		$html .= $this->getReplyList ( $t_id );
		$html .= '</div>';
		// 判断博客是否允许回复
		$stm = $db->query ( 'SELECT * FROM ' . $tablePre . 'config WHERE t_key=\'allow_reply\'' );
		$rst = $stm->fetch ();
		if ($rst ['t_value'] == 1)
			$html .= $this->getReplyEditor ( $t_id );
		else {
			$html .= '<div class="panel panel-default">
  <div class="panel-body">博主已关闭回复功能</div>
</div>';
		}
		$blogContext = substr ( $_SERVER ['SCRIPT_NAME'], 0, - 1 - strlen ( MVC_ENTRY_NAME ) );
		$ue_path = $blogContext . '/static/ueditor/';
		$html .= ('<script type="text/javascript">
			if(!blogInfo.load_js.shCore){
			/*高亮插件*/
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
				SyntaxHighlighter.highlight();
			</script>');
		return $html;
	}
	public function getTitle() {
		$stm = $this->db->query ( 'SELECT t_value FROM ' . $this->tablePre . 'config WHERE t_key=\'blogname\'' );
		$rst = $stm->fetch ();
		return $rst ['t_value'] . '-' . $this->title;
	}
	/**
	 * 获取回复处的编辑器
	 */
	private function getReplyEditor($t_id) {
		$blogContext = substr ( $_SERVER ['SCRIPT_NAME'], 0, - 1 - strlen ( MVC_ENTRY_NAME ) );
		$ue_path = $blogContext . '/static/ueditor/';
		$html = '<div class="panel panel-default">
  <div class="panel-body">
    <form class="form-horizontal" id="post_topic_form">';
		$html .= '<div class="row">
<div class="col-sm-12">
<input type="text" class="form-control" id="t_user" placeholder="请输入您的名字或者昵称">
</div>
</div>';
		$app = Application::getApp ();
		$urlHandler = $app->getUrlHandler ();
		$configUrl = $urlHandler->createUrl ( 'ajax/Ueditor', 'config', array (), false );
		$uploadimageUrl = $urlHandler->createUrl ( 'ajax/Ueditor', 'uploadimage', array (), false );
		$uploadscrawlUrl = $urlHandler->createUrl ( 'ajax/Ueditor', 'uploadscrawl', array (), false );
		$uploadfileUrl = $urlHandler->createUrl ( 'ajax/Ueditor', 'uploadfile', array (), false );
		$doReplyUrl = $urlHandler->createUrl ( 'ajax/Topic', 'doReply', array (), false );
		$loadReplyUrl = $urlHandler->createUrl ( 'ajax/Topic', 'loadReply', array (), false );
		$html .= '<div class="row">
<div class="col-sm-12">
    <!-- 加载编辑器的容器 -->
    <script class="form-control-static" id="t_contents" type="text/plain">回复内容</script>
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
    <button id="post_topic_btn" type="button" class="btn btn-primary">回复文章</button>
</div>
</div>');
		/* 发表文章的事件绑定 */
		$html .= ('<script type="text/javascript">
    $("#post_topic_btn").click(function(){
        var r=confirm("是否回复文章?");
        if(!r)
            return;
        $.ajax({
            "url" : "' . $doReplyUrl . '",
            "method" : "POST",
            "cache" : false,
            "dataType" : "json",
            "data" : {
                "topic_id":' . $t_id . ',
                "t_user":$("#t_user").val(),
                "t_contents":ue.getContent()
            },
            "success" : function(data) {
                if(data.success){
                /*刷新底部评论列表*/
                $.ajax({
                            "url" : "' . $loadReplyUrl . '",
                            "method" : "POST",
                            "cache" : false,
                            "dataType" : "json",
                            "data" : {
                                "topic_id":' . $t_id . '
                            },
                            "success" : function(data) {
							ue.setContent("");
                            $("#reply_div").html("<div class=\"panel-heading\">回复列表</div>"+data.msg);
							SyntaxHighlighter.highlight();
                            },
                            "error" : function(jqXHR, textStatus, errorThrown) {
                                alertModal("danger","异步失败",errorThrown);/*异步失败*/
                            }
                        });/*end ajax*/
                }
                else
                    alertModal("danger","回复失败",data.msg);
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
	public function getReplyList($t_id) {
		$db = $this->db;
		$tablePre = $this->tablePre;
		$stm = $db->query ( 'SELECT COUNT(*) as reply_num FROM ' . $tablePre . 'reply WHERE topic_id=' . $t_id );
		$rst = $stm->fetch ();
		if ($rst ['reply_num'] == 0) {
			return '<div class="panel-body">目前还没有回复</div>';
		}
		// 获取博主昵称
		$stm = $db->query ( 'SELECT t_value FROM ' . $tablePre . 'config WHERE t_key=\'nickname\'' );
		$rst = $stm->fetch ();
		$nickname = $rst ['t_value'];
		$html = '<ul class="list-group" id="reply_list">';
		$stm = $db->query ( 'SELECT * FROM ' . $tablePre . 'reply WHERE topic_id=' . $t_id .' ORDER BY t_id ASC');
		$user=new User();
		$isAdmin=$user->checkAdmin($db, $tablePre);
		$i = 0;
		while ( $tmp = $stm->fetch () ) {
			$reply_nick = $tmp ['t_user'];
			if ($tmp ['is_admin_post'] == 1)
				$reply_nick = $nickname;
			$html .= ('<li class="list-group-item">
                    <h4 class="list-group-item-heading">[' . (++ $i) . '楼]
                    <span class="glyphicon glyphicon-user" aria-hidden="true"></span> ' . htmlspecialchars ( $reply_nick ) . '&nbsp;&nbsp;&nbsp;[' . date ( 'Y-m-d H:i:s', $tmp ['post_time'] ) . ']');
			if($isAdmin){
				$html.=('  <a href="javascript:void(0)" data-t_id="'.$tmp['t_id'].'">[删除回复]</a>');
			}
			$html .= ('</h4><p class="list-group-item-heading">' . $tmp ['t_content'] . '</p></li>');
		}
		$html .= '</ul>';
		$app = Application::getApp ();
		$urlHandler = $app->getUrlHandler ();
		$del_reply_url=$urlHandler->createUrl ( 'ajax/AdminTopic', 'delReply', array (), false );
		$loadReplyUrl = $urlHandler->createUrl ( 'ajax/Topic', 'loadReply', array (), false );
		$html.=('<script type="text/javascript">
				$("#reply_list>li").each(function(){
				$(this).find("h4:first>a").click(function(){
					var r=confirm("是否删除这条回复?"),aNode=$(this);
			        if(!r)
			            return;
			        $.ajax({
			            "url" : "' . $del_reply_url . '",
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
			                            "url" : "' . $loadReplyUrl . '",
			                            "method" : "POST",
			                            "cache" : false,
			                            "dataType" : "json",
			                            "data" : {
			                                "topic_id":' . $t_id . '
			                            },
			                            "success" : function(data) {
			                                alertModal("success","执行成功","你已成功删除本条回复");
                            			$("#reply_div").html("<div class=\"panel-heading\">回复列表</div>"+data.msg);
			                            },
			                            "error" : function(jqXHR, textStatus, errorThrown) {
			                                alertModal("danger","异步失败",errorThrown);/*异步失败*/
			                            }
			                        });/*end ajax*/
			                }
			                else
			                    alertModal("danger","删除回复失败",data.msg);
			            },
			            "error" : function(jqXHR, textStatus, errorThrown) {
			                alertModal("danger","异步失败",errorThrown);/*异步失败*/
			            }
			        });/*end ajax*/
					});/*end click*/
				});
		</script>');
		return $html;
	}
}