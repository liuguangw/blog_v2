<?php

namespace liuguang\blog\view;

use liuguang\mvc\Application;

/**
 * 管理文章的界面
 *
 * @author liuguang
 *        
 */
class EditTopic {
	private $db;
	private $tablePre;
	private $t_id;
	public function __construct(\PDO $db, $tablePre, $t_id) {
		$this->db = $db;
		$this->tablePre = $tablePre;
		$this->t_id = $t_id;
	}
	public function getHtml() {
		// 判断文章id是否存在
		$sql = 'SELECT COUNT(*) AS t_num FROM ' . $this->tablePre . 'topic WHERE t_id=' . $this->t_id;
		$stm = $this->db->query ( $sql );
		$rst = $stm->fetch ();
		if ($rst ['t_num'] == 0)
			return '<div class="alert alert-danger" role="alert">文章不存在</div>
                    </form></div></div></div>';
			// 获取文章信息
		$sql = 'SELECT t_title,t_content,leibie_id FROM ' . $this->tablePre . 'topic WHERE t_id=' . $this->t_id;
		$stm = $this->db->query ( $sql );
		$topicInfo = $stm->fetch ();
		$blogContext = substr ( $_SERVER ['SCRIPT_NAME'], 0, - 1 - strlen ( MVC_ENTRY_NAME ) );
		$ue_path = $blogContext . '/static/ueditor/';
		$html = '<div class="panel panel-default">
  <div class="panel-heading">管理文章</div>
  <div class="panel-body">
    <form class="form-horizontal" id="post_topic_form">';
		$html .= '<div class="row">
<div class="col-sm-12">
<input type="text" class="form-control" id="t_title" placeholder="请输入文章标题" value="' . str_replace ( '"', '\\"', $topicInfo ['t_title'] ) . '">
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
    <script class="form-control-static" id="t_contents" type="text/plain">' . $topicInfo ['t_content'] . '</script>
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
			$html .= $this->getFenleiSec ( $topicInfo ['leibie_id'] );
			$stm1 = $this->db->query ( 'SELECT COUNT(*) AS s_num FROM ' . $this->tablePre . 'tag' );
			$rst1 = $stm1->fetch ();
			if ($rst1 ['s_num'] != 0) {
				$stm2 = $this->db->query ( 'SELECT t_id,t_name FROM ' . $this->tablePre . 'tag WHERE t_id IN (SELECT tag_id FROM ' . $this->tablePre . 'topic_tag WHERE topic_id=' . $this->t_id . ')' );
				$tagsArr=array();
				while($tagInfo=$stm2->fetch()){
					$tagsArr[]=array('tag_id'=>$tagInfo['t_id'],'tag_name'=>$tagInfo['t_name']);
				}
				$html .= $this->getTagSec ( json_encode($tagsArr) );
			}
			$html .= ('<div class="row">
<div class="col-sm-4 col-sm-offset-8">
    <button id="del_topic_btn" type="button" class="btn btn-danger">删除文章</button>&nbsp;&nbsp;
    <button id="edit_topic_btn" type="button" class="btn btn-primary">修改文章</button>
</div>
</div>');
			/* 修改文章的事件绑定 */
			$html .= ('<script type="text/javascript">
    $("#edit_topic_btn").click(function(){
        var r=confirm("是否修改文章");
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
            "url" : "' . $urlHandler->createUrl ( 'ajax/AdminTopic', 'edit', array (), false ) . '",
            "method" : "POST",
            "cache" : false,
            "dataType" : "json",
            "data" : {
				"t_id":' . $this->t_id . ',
                "t_title":$("#t_title").val(),
                "t_contents":ue.getContent(),
                "posts_type":$("#posts_type").val()
            },
            "success" : function(data) {
                if(data.success){
                    alertModal("success","修改成功","修改文章成功");
					refreshRight();
					}
                else
                    alertModal("danger","修改失败",data.msg);
            },
            "error" : function(jqXHR, textStatus, errorThrown) {
                alertModal("danger","异步失败",errorThrown);
            }
        });/*end ajax*/
    });

    /*delete*/
    $("#del_topic_btn").click(function(){
        var r=confirm("你确定要删除文章吗");
        if(!r)
         return;
        $.ajax({
            "url" : "' . $urlHandler->createUrl ( 'ajax/AdminTopic', 'delete', array (), false ) . '",
            "method" : "POST",
            "cache" : false,
            "dataType" : "json",
            "data" : {
                "t_id":' . $this->t_id . '					
            },
            "success" : function(data) {
                if(data.success){
                    alertModal("success","删除成功","删除文章成功");
					refreshRight();
					}
                else
                    alertModal("danger","删除失败",data.msg);
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
	private function getFenleiSec($secId) {
		$html = '<div class="form-group">
<label for="posts_type" class="col-sm-3 control-label">选择分类</label>
<div class="col-sm-9">
    <select id="posts_type" class="form-control">';
		$stm = $this->db->query ( 'SELECT t_id,t_name FROM ' . $this->tablePre . 'leibie ORDER BY t_id ASC' );
		while ( $tmp = $stm->fetch () ) {
			$selected = '';
			if ($tmp ['t_id'] == $secId)
				$selected = ' selected="selected"';
			$html .= ('<option value="' . $tmp ['t_id'] . '"' . $selected . '>' . $tmp ['t_name'] . '</option>');
		}
		$html .= '</select>
</div>
</div>';
		return $html;
	}
	/**
	 * 获取标签选择列表
	 *
	 * @param
	 *        	string 标签列表的js数组形式字符串
	 * @return string
	 */
	private function getTagSec($tagsArr) {
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
		$tagUrl = $urlHandler->createUrl ( 'web/Tag', 'index', array (
				't_id' => '[tag]',
				'page' => '1' 
		), false );
		$html .= ('<script type="text/javascript">
			var addTagNode=function(tag_id,tag_name){
				var tagUrl="' . $tagUrl . '";
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
					/*start 删除标签*/
				    var tag_id2=$(this).attr("data-tag_id"),tagBtn=$(this),i;
					$.ajax({
			            "url" : "' . $urlHandler->createUrl ( 'ajax/AdminTopic', 'deleteTag', array (), false ) . '",
			            "method" : "POST",
			            "cache" : false,
			            "dataType" : "json",
			            "data" : {
			                "t_id":' . $this->t_id . ',
							"tag_id":tag_id2					
			            },
			            "success" : function(data) {
			                if(data.success){
								for(i=0;i<tagIdArr.length;i++){
									if(tagIdArr[i]==tag_id2)
										tagIdArr.splice(i,1);
								}
								tagBtn.parent().remove();
							}
			                else
			                    alertModal("danger","删除失败",data.msg);
			            },
			            "error" : function(jqXHR, textStatus, errorThrown) {
			                alertModal("danger","异步失败",errorThrown);/*异步失败*/
			            }
			        });
					/*end 删除标签*/
				});
			};
            $("#add_tag_btn").click(function(){
                var tag_id=$("#posts_tag").val(),
                    tag_name=$("#posts_tag option:selected").text(),
					tagUrl="' . $tagUrl . '";
                if($.inArray(tag_id,tagIdArr)==-1){
					/*start 添加标签*/
					$.ajax({
			            "url" : "' . $urlHandler->createUrl ( 'ajax/AdminTopic', 'addTag', array (), false ) . '",
			            "method" : "POST",
			            "cache" : false,
			            "dataType" : "json",
			            "data" : {
			                "t_id":' . $this->t_id . ',
							"tag_id":tag_id					
			            },
			            "success" : function(data) {
			                if(data.success)
								addTagNode(tag_id,tag_name);
			                else
			                    alertModal("danger","添加失败",data.msg);
			            },
			            "error" : function(jqXHR, textStatus, errorThrown) {
			                alertModal("danger","异步失败",errorThrown);/*异步失败*/
			            }
			        });
					/*end 添加标签*/
                }
                else{
                    alertModal("danger","标签已添加","此文章已添加"+tag_name+"标签");
                }
            });
			var tagsArr='.$tagsArr.',tagI;
			for(tagI=0;tagI<tagsArr.length;tagI++){
				addTagNode(tagsArr[tagI].tag_id,tagsArr[tagI].tag_name);
			}
			</script>');
		return $html;
	}
	public function getTitle() {
		$stm = $this->db->query ( 'SELECT t_value FROM ' . $this->tablePre . 'config WHERE t_key=\'blogname\'' );
		$rst = $stm->fetch ();
		return $rst ['t_value'] . '-管理文章';
	}
}