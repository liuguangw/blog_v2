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
		$html .= '<div class="form-group">
<div class="col-sm-4 col-sm-offset-3">
    <input type="file" id="blog_upload_f" class="form-control"/>
</div>
<div class="col-sm-3">
    <button id="blog_upload_btn" type="button" class="btn btn-primary">立刻上传</button>
</div>
</div>';
		$blog_context = substr ( $_SERVER ['SCRIPT_NAME'], 0, - 1 - strlen ( MVC_ENTRY_NAME ) );
		$ajax_uploader = $urlHandler->createUrl ( 'ajax/AdminFile', 'ajaxUpload', array (), false );
		$ajax_update_url=$urlHandler->createUrl ( 'ajax/AdminFile', 'update', array (), false );
		$ajax_del_url=$urlHandler->createUrl ( 'ajax/AdminFile', 'delete', array (), false );
		$html .= '<div class="row"><div class="col-sm-12"><p id="upload_info"></p></div></div>';
		$html .= ('<link rel="stylesheet" type="text/css" href="' . $blog_context . '/static/uploadify/uploadify.css" />
        <script type="text/javascript">
        if(!blogInfo.load_js.uploadify){
            loadJsFile("' . $blog_context . '/static/uploadify/jquery.uploadify.min.js",function(){
                blogInfo.load_js.uploadify=1;
                $("#blog_upload_f").uploadify({
                    "swf"      : "' . $blog_context . '/static/uploadify/uploadify.swf",
                    "uploader" : "' . $ajax_uploader . '",
                    "buttonText" : "选择文件",
                    "auto" : false,
                    "formData":{"osid":getCookie("osid")},
                    "onUploadSuccess" : function(file, data, response) {
                    $("#upload_info").append(data);
                }
                    // Put your options here
                });
                $("#blog_upload_btn").click(function(){
                        $("#blog_upload_f").uploadify("upload","*");
                });
            });
        }
        else{
            $("#blog_upload_f").uploadify({
                "swf"      : "' . $blog_context . '/static/uploadify/uploadify.swf",
                "uploader" : "' . $ajax_uploader . '",
                "buttonText" : "选择文件",
                "auto" : false,
                "formData":{"osid":getCookie("osid")},
                "onUploadSuccess" : function(file, data, response) {
                $("#upload_info").append(data);
            }
                // Put your options here
            });
            $("#blog_upload_btn").click(function(){
                    $("#blog_upload_f").uploadify("upload","*");
            });
        }
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
                                alertModal("success",act_names+"操作成功",data.msg);
                                if(index==1)
                                    trNode.remove();
                            }
                            else
                                alertModal("danger",act_names+"操作失败",data.msg);
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
		$stm = $this->db->query ( 'SELECT * FROM ' . $this->tablePre . 'blog_upload ORDER BY index_id ASC LIMIT ' . $offset . ', ' . $perPage );
		while ( $tmp = $stm->fetch () ) {
			if ($this->fs->canGetUrl ())
				$fileUrl = $this->fs->getUrl ( $tmp ['obj_name'] );
			else 
				$fileUrl=$urlHandler->createUrl('ajax/BlogUtil', 'showFile', array('f'=>$tmp ['obj_name']));
			$html .= sprintf ( $trTpl, $tmp ['index_id'], $fileUrl, $tmp ['obj_name'], $tmp ['obj_beizhu'], date ( 'Y-m-d H:i:s', $tmp ['add_time'] ) );
		}
		$html .= '</table>';
		$fenyeV=new Fenye();
		$urlTpl=$urlHandler->createUrl('web/BlogAdmin', 'files', array('page'=>'%d'));
		$html.=$fenyeV->getNav($urlTpl, $page, $page_num);
		return $html;
	}
	public function getTitle($page) {
		$stm = $this->db->query ( 'SELECT t_value FROM ' . $this->tablePre . 'config WHERE t_key=\'blogname\'' );
		$rst = $stm->fetch ();
		return $rst ['t_value'] . '文件管理-第'.$page.'页';
	}
}