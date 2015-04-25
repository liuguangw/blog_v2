<?php

namespace liuguang\blog\view;

use liuguang\mvc\Application;
use liuguang\mvc\UrlHandler;
use liuguang\mvc\FsInter;

/**
 * 后台管理文章的界面
 *
 * @author liuguang
 *        
 */
class AdminFiles {
	private $db;
	private $tablePre;
	private $fs;
	public function __construct(\PDO $db, $tablePre, FsInter $fs) {
		$this->db = $db;
		$this->tablePre = $tablePre;
		$this->fs = $fs;
	}
	public function getHtml($page) {
		$app = Application::getApp ();
		$appConfig = $app->getAppConfig ();
		date_default_timezone_set ( $appConfig->get ( 'timeZone' ) );
		$urlHandler = $app->getUrlHandler ();
		$html = '<div class="panel panel-default" id="admin_bfiles">
  <div class="panel-heading">文件管理</div>
  <div class="panel-body">';
		$stm = $this->db->query ( 'SELECT COUNT(*) as fs_num FROM ' . $this->tablePre . 'blog_upload' );
		$rst = $stm->fetch ();
		$fs_num = $rst ['fs_num'];
		$perPage = 15; // 每页最多15条
		$page_num = ceil ( $fs_num / $perPage );
		if ($fs_num == 0)
			$page_num = 1;
		if (($page < 1) || ($page > $page_num))
			$html .= '<div class="alert alert-danger" role="alert">当前页面不存在</div>';
		else {
			if ($fs_num == 0)
				$html .= '<div class="alert alert-danger" role="alert">当前还没有上传文件</div>';
			else
				$html .= $this->getFsTable ( $page, $perPage, $page_num, $urlHandler );
		} // end else
		$html .= '</div></div>';
		$html .= '<div class="panel panel-default">
  <div class="panel-heading">文件上传</div>
  <div class="panel-body">';
		$html .= '<div class="row">
<div class="col-md-2 col-md-offset-2">
	<button id="select_file_btn" type="button" class="btn btn-default">点击选择文件</button>
</div>
<div class="col-md-2">
    <button id="clean_upload_btn" type="button" class="btn btn-default">清空列表</button>
</div>
<div class="col-md-2">
    <button id="blog_upload_btn" type="button" class="btn btn-primary">立刻上传</button>
</div>
</div>
<div class="row" style="margin-top:10px;margin-buttom:10px;">
<ul class="list-group col-md-8 col-md-offset-2" id="showFiles">
	<li class="list-group-item active">文件列表</li>
</ul>
</div>';
		$ajax_uploader = $urlHandler->createUrl ( 'ajax/AdminFile', 'ajaxUpload', array (), false );
		$ajax_update_url=$urlHandler->createUrl ( 'ajax/AdminFile', 'update', array (), false );
		$ajax_del_url=$urlHandler->createUrl ( 'ajax/AdminFile', 'delete', array (), false );
		$html .= '<div class="row" id="upload_info"></div>';
		$html .= ('<script type="text/javascript">
		(function(){
			var filesrcList=[];
			$("#select_file_btn").click(function(){
				var fileObj=$("<input type=\"file\" style=\"display:none\"/>");
				fileObj[0].onchange=function(){	
					if($.inArray(this.value,filesrcList)!=-1)
						alert("文件"+this.files[0].name+"已在上传列表中");
					else{
						var fileSize = 0;
				        if (this.files[0].size > 1024 * 1024)
				            fileSize = (Math.round(this.files[0].size * 100 / (1024 * 1024)) / 100).toString() + "MB";
				        else
				            fileSize = (Math.round(this.files[0].size * 100 / 1024) / 100).toString() + "KB";
						var fIndex=filesrcList.push(this.value)-1;
						var listNode=$("<li class=\"list-group-item\">\
						  <button type=\"button\" class=\"close\"><span aria-hidden=\"true\">&times;</span></button>\
						  <p>"+this.files[0].name+"["+fileSize+"]<span class=\"label label-default\">准备上传</span></p>\
						  <div class=\"progress\" style=\"display:none;\">\
							  <div class=\"progress-bar progress-bar-striped active\" role=\"progressbar\" aria-valuenow=\"0\" aria-valuemin=\"0\" aria-valuemax=\"100\" style=\"width: 0%;\">\
							    <span>已上传0%</span>\
							  </div>\
						  </div></li>");
						$("#showFiles").append(listNode.append(fileObj));
						 listNode.find(".close").click(function(){
							delete filesrcList[fIndex];
							$(this).parent().remove();
						 });
					}
				};
				fileObj.click();
			});/*end selectfile*/
			$("#clean_upload_btn").click(function(){
				filesrcList=[];
				$("#showFiles li:gt(0)").remove();
				$("#upload_info").html("");
			});
			var createMsg=function(msgType, msg) {
				return ("<div class=\"alert alert-" + msgType
			+ "\" role=\"alert\">" + msg + "</div>");
			}
			var uploadListFile=function(fileId){
					var maxFileId=document.getElementById("showFiles").children.length-2;
					if(fileId> maxFileId)
						return;
		        	var fd= new FormData(),
					proBar=$("#showFiles div.progress-bar:eq("+fileId+")"),
					infoLabel=$("#showFiles span.label:eq("+fileId+")"),
					Filedata=$("#showFiles input:eq("+fileId+")")[0];
					proBar.attr({
							"aria-valuenow":0,
							"style":"width:0%"
						}).html("<span>已上传0%</span>");
					infoLabel.html("正在上传中").attr("class","label label-info");
					proBar.parent().show();
					fd.append("Filedata", Filedata.files[0]);
			        var xhr = new XMLHttpRequest();
			        xhr.upload.addEventListener("progress", function(evt){
						var percentComplete = Math.round(evt.loaded * 100 / evt.total);
						proBar.attr({
							"aria-valuenow":percentComplete,
							"style":"width:"+percentComplete+"%"
						});
						if(percentComplete==100)
							proBar.html("<span>已上传100%,服务器正在存储文件中...</span>");
						else
							proBar.html("<span>已上传"+percentComplete+"%</span>");
					}, false);
			        xhr.addEventListener("load", function(){
						uploadListFile(++fileId);
						proBar.parent().hide();
					}, false);
			        xhr.addEventListener("error", function(){
                            alertModal("danger","异步失败","errorThrown");
					}, false);
			        xhr.addEventListener("abort", function(){
					}, false);
			        xhr.open("POST", "'.$ajax_uploader.'");
					xhr.onreadystatechange=function(){
					  if (xhr.readyState==4){
						if(xhr.status==200){
							var data,tipHtml;
							eval("data="+xhr.responseText+";");
							if(data.success){
								infoLabel.html("文件上传成功").attr("class","label label-success");
							$("#upload_info").append(createMsg("success","上传文件"+Filedata.files[0].name+"成功,新文件名为:"+data.newname));
							}
							else{
								infoLabel.html("文件上传失败").attr("class","label label-danger");
							$("#upload_info").append(createMsg("danger","上传文件"+Filedata.files[0].name+"失败"+data.msg));
							}
						}
						/*非200*/
						 else
							alertModal("danger","异步只休息失败","errorThrown");
					  }/*readyState==4*/
					};
			        xhr.send(fd);
			};
			$("#blog_upload_btn").click(function(){
				if(filesrcList.length==0)
					alert("请先选择文件");
				uploadListFile(0);
			});
		})();
        //文件管理
        $("#admin_bfiles tr:gt(0)").each(function(){
            var trNode=$(this);
            trNode.find("button").each(function(index){
                $(this).click(function(){
                    var postData,act_names=["修改备注","删除文件"],ajax_url;
                    var r=confirm("你确定要"+act_names[index]+"吗?");
                    if(r==false)
                        return;
                    if(index==0){
						ajax_url="'.$ajax_update_url.'";
                        postData={
                            "f_id":trNode.find("td:eq(0)").html(),
                            "beizhu":trNode.find("input").val()
                        };
					}
                    else{
						ajax_url="'.$ajax_del_url.'";
                        postData={
                            "f_id":trNode.find("td:eq(0)").html()
                        };
					}
                    $.ajax({
                        "url" : ajax_url,
                        "method" : "POST",
                        "cache" : false,
                        "dataType" : "json",
                        "data" : postData,
                        "success" : function(data) {
                            if(data.success){
                                alertModal("success",act_names[index]+"操作成功",data.msg);
                                if(index==1)
                                    trNode.remove();
                            }
                            else
                                alertModal("danger",act_names[index]+"操作失败",data.msg);
                        },
                        "error" : function(jqXHR, textStatus, errorThrown) {
                            alertModal("danger","异步失败",errorThrown);
                        }
                    });
                });
            });
        });
        //分页
        $("#f_fenye a").each(function(){
            if($(this).parent().is("li[class]"))
                $(this).click(function(evt){
                    evt.preventDefault();
                });
            else
                $(this).bindPushState();
        });
        </script>');
		$html .= '</div></div>';
		return $html;
	}
	/**
	 * 获取文件列表的表格
	 *
	 * @param int $page
	 *        	当前页码
	 * @param int $perPage
	 *        	每页显示的条数
	 * @param int $page_num
	 *        	页码总数
	 * @param UrlHandler $urlHandler        	
	 * @return string
	 */
	private function getFsTable($page, $perPage, $page_num, $urlHandler) {
		$html = '<table class="table table-hover table-bordered">';
		$html .= '<tr><th>文件编号</th><th>文件名</th><th>备注名</th><th>上传时间</th><th></th></tr>';
		$trTpl = '<tr><td>%s</td>
                    <td><a href="%s">%s</a></td>
                    <td><input type="text" class="form-control" placeholder="备注名" value="%s"/></td>
                    <td>%s</td>
                    <td><button type="button" class="btn btn-primary">修改备注</button>&nbsp;&nbsp;<button type="button" class="btn btn-danger">删除</button>
                    </td></tr>';
		$offset = ($page - 1) * $perPage;
		$stm = $this->db->query ( 'SELECT * FROM ' . $this->tablePre . 'blog_upload ORDER BY index_id DESC LIMIT ' . $offset . ', ' . $perPage );
		while ( $tmp = $stm->fetch () ) {
			if ($this->fs->canGetUrl ())
				$fileUrl = $this->fs->getUrl ( $tmp ['obj_name'] );
			else 
				$fileUrl=$urlHandler->createUrl('ajax/BlogUtil', 'showFile', array('f'=>$tmp ['obj_name']));
			$html .= sprintf ( $trTpl, $tmp ['index_id'], $fileUrl, $tmp ['obj_name'], $tmp ['obj_beizhu'], date ( 'Y-m-d H:i:s', $tmp ['add_time'] ) );
		}
		$html .= '</table>';
		$fenyeV=new Fenye();
		$urlTpl=$urlHandler->createUrl('web/BlogAdmin', 'files', array('page'=>'--page--'));
		$html.=$fenyeV->getNav($urlTpl, $page, $page_num);
		return $html;
	}
	public function getTitle($page) {
		$stm = $this->db->query ( 'SELECT t_value FROM ' . $this->tablePre . 'config WHERE t_key=\'blogname\'' );
		$rst = $stm->fetch ();
		return $rst ['t_value'] . '文件管理-第'.$page.'页';
	}
}