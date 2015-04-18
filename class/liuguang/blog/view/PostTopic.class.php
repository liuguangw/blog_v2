<?php

namespace liuguang\blog\view;

use liuguang\mvc\Application;

/**
 * 发表文章的界面
 *
 * @author liuguang
 *        
 */
class PostTopic {
	private $db;
	private $tablePre;
	public function __construct(\PDO $db, $tablePre) {
		$this->db = $db;
		$this->tablePre = $tablePre;
	}
	public function getHtml() {
		$blogContext = substr ( $_SERVER ['SCRIPT_NAME'], 0, - 1 - strlen ( MVC_ENTRY_NAME ) );
		$ue_path = $blogContext . '/static/ueditor/';
		$html = '<div class="panel panel-default">
  <div class="panel-heading">发表文章</div>
  <div class="panel-body">
    <form class="form-horizontal" id="post_topic_form">';
		$html .= '<div class="row">
<div class="col-sm-12">
<input type="text" class="form-control" id="t_title" placeholder="请输入文章标题">
</div>
</div>';
		$app = Application::getApp ();
		$urlHandler = $app->getUrlHandler ();
		$configUrl = $urlHandler->createUrl ( 'ajax/Ueditor', 'config', array (), false );
		$uploadimageUrl = $urlHandler->createUrl ( 'ajax/Ueditor', 'uploadimage', array (), false );
		$uploadscrawlUrl = $urlHandler->createUrl ( 'ajax/Ueditor', 'uploadscrawl', array (), false );
		$uploadfileUrl = $urlHandler->createUrl ( 'ajax/Ueditor', 'uploadfile', array (), false );
		$html .= ('<div class="row">
<div class="col-sm-12">
    <!-- 加载编辑器的容器 -->
    <script class="form-control-static" id="t_contents" type="text/plain">文章内容</script>
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
                    "initialFrameHeight":400});
            });
        });
    }
    else{
        ue.destroy();
        ue = UE.getEditor("t_contents",
                    {"UEDITOR_HOME_URL":"' . $ue_path . '",
                    "initialFrameHeight":400});
    }
    /*标签id列表*/
    var tagIdArr=[];
    </script>
</div></div>');
		$stm = $this->db->query ( 'SELECT COUNT(*) AS s_num FROM ' . $this->tablePre . 'leibie' );
		$rst = $stm->fetch ();
		if ($rst ['s_num'] != 0) {
			$html .= $this->getFenleiSec ();
			$stm1 = $this->db->query ( 'SELECT COUNT(*) AS s_num FROM ' . $this->tablePre . 'tag' );
			$rst1 = $stm1->fetch ();
			if ($rst1 ['s_num'] != 0)
				$html .= $this->getTagSec ();
			$html .= ('<div class="row">
<div class="col-sm-2 col-sm-offset-10">
    <button id="post_topic_btn" type="button" class="btn btn-primary">发表文章</button>
</div>
</div>');
			/* 发表文章的事件绑定 */
			$html .= ('<script type="text/javascript">
    $("#post_topic_btn").click(function(){
        var r=confirm("是否发表文章");
        if(!r)
            return;
        var tag_ids="",j;
        if(tagIdArr.length>0){
            tag_ids=tagIdArr[0];
        }
        for(j=1;j<tagIdArr.length;j++){
            tag_ids+=(","+tagIdArr[j]);
        }
        $.ajax({
            "url" : "' . $urlHandler->createUrl ( 'ajax/AdminTopic', 'post', array (), false ) . '",
            "method" : "POST",
            "cache" : false,
            "dataType" : "json",
            "data" : {
                "t_title":$("#t_title").val(),
                "t_contents":ue.getContent(),
                "posts_type":$("#posts_type").val(),
                "tag_ids":tag_ids
            },
            "success" : function(data) {
                if(data.success){
                    alertModal("success","发表成功","发表文章成功");
					refreshRight();
				}
                else
                    alertModal("danger","发表失败",data.msg);
            },
            "error" : function(jqXHR, textStatus, errorThrown) {
                alertModal("danger","异步失败",errorThrown);/*异步失败*/
            }
        });/*end ajax*/
    });
    </script>');
		} else
			$html .= '<div class="row"><div class="alert alert-danger" role="alert">没有分类可供选择,请先创建一个分类</div></div>';
		$html .= ('</form></div></div>');
		return $html;
	}
	/**
	 * 获取分类选择列表
	 *
	 * @return string
	 */
	private function getFenleiSec() {
		$html = '<div class="form-group">
<label for="posts_type" class="col-sm-3 control-label">选择分类</label>
<div class="col-sm-9">
    <select id="posts_type" class="form-control">';
		$stm = $this->db->query ( 'SELECT t_id,t_name FROM ' . $this->tablePre . 'leibie ORDER BY t_id ASC' );
		while ( $tmp = $stm->fetch () ) {
			$html .= ('<option value="' . $tmp ['t_id'] . '">' . $tmp ['t_name'] . '</option>');
		}
		$html .= '</select>
</div>
</div>';
		return $html;
	}
	/**
	 * 获取标签选择列表
	 *
	 * @return string
	 */
	private function getTagSec() {
		$html = '<div class="form-group">
<label for="posts_tag" class="col-sm-3 control-label">选择标签</label>
<div class="col-sm-9">
    <select id="posts_tag" class="form-control">';
		$stm = $this->db->query ( 'SELECT t_id,t_name FROM ' . $this->tablePre . 'tag ORDER BY t_id ASC' );
		while ( $tmp = $stm->fetch () ) {
			$html .= ('<option value="' . $tmp ['t_id'] . '">' . $tmp ['t_name'] . '</option>');
		}
		$html .= '</select>
</div>
<div class="col-sm-2">
    <button id="add_tag_btn" type="button" class="btn btn-primary">
    <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> 添加标签</button>
</div>
</div>';
		$html .= '<div class="form-group">
                    <div class="panel panel-default">
                      <div class="panel-heading">标签列表</div>
                      <div class="panel-body" id="tag_list">
                    </div><!-- end tag_list-->
                    </div>
                </div>';
		/* 添加标签的事件 */
		$app = Application::getApp ();
		$urlHandler = $app->getUrlHandler ();
		$tagUrl = $urlHandler->createUrl ( 'web/Tag', 'index', array ('t_id'=>'[tag]','page'=>'1'), false );
		$html .= ('<script type="text/javascript">
            $("#add_tag_btn").click(function(){
                var tag_id=$("#posts_tag").val(),
                    tag_name=$("#posts_tag option:selected").text(),
					tagUrl="'.$tagUrl.'";
                if($.inArray(tag_id,tagIdArr)==-1){
                    tagIdArr.push(tag_id);
                    var tagNode=$("<div class=\"btn-group\">"+
                                "<a href=\""+tagUrl.replace(/\[tag\]/,tag_id)+"\" class=\"btn btn-info btn-sm\">"+tag_name+"</a>"+
                                "<button type=\"button\" class=\"btn btn-default btn-sm\" data-tag_id=\""+tag_id+"\">"+
                                "<span class=\"glyphicon glyphicon-remove\" aria-hidden=\"true\"></span></button>"+
                                "</div>");
                    $("#tag_list").append("&nbsp;&nbsp;");
                    $("#tag_list").append(tagNode);
                    tagNode.find("a").bindPushState();
                    tagNode.find("button").click(function(){
                        var tag_id2=$(this).attr("data-tag_id"),i;
                        for(i=0;i<tagIdArr.length;i++){
                            if(tagIdArr[i]==tag_id2)
                                tagIdArr.splice(i,1);
                        }
                        $(this).parent().remove();
                    });
                }
                else{
                    alertModal("danger","标签已添加","此文章已添加"+tag_name+"标签");
                }
            });
            </script>');
		return $html;
	}
	public function getTitle() {
		$stm = $this->db->query ( 'SELECT t_value FROM ' . $this->tablePre . 'config WHERE t_key=\'blogname\'' );
		$rst = $stm->fetch ();
		return $rst ['t_value'] . '-发表文章';
	}
}