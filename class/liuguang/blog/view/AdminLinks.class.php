<?php

namespace liuguang\blog\view;

use liuguang\mvc\Application;
use liuguang\mvc\UrlHandler;

/**
 * 后台管理友链的界面
 *
 * @author liuguang
 *        
 */
class AdminLinks {
	private $db;
	private $tablePre;
	public function __construct(\PDO $db, $tablePre) {
		$this->db = $db;
		$this->tablePre = $tablePre;
	}
	public function getHtml($page) {
		$app = Application::getApp ();
		$appConfig = $app->getAppConfig ();
		date_default_timezone_set ( $appConfig->get ( 'timeZone' ) );
		$urlHandler = $app->getUrlHandler ();
		$html = '<div class="panel panel-default" id="admin_url">
  <div class="panel-heading">友链管理</div>
  <div class="panel-body">';
		$stm = $this->db->query ( 'SELECT COUNT(*) as s_num FROM ' . $this->tablePre . 'links' );
		$rst = $stm->fetch ();
		$fs_num = $rst ['s_num'];
		$perPage = 15; // 每页最多15条
		$page_num = ceil ( $fs_num / $perPage );
		if ($fs_num == 0)
			$page_num = 1;
		if (($page < 1) || ($page > $page_num))
			$html .= '<div class="alert alert-danger" role="alert">当前页面不存在</div>';
		else {
			if ($fs_num == 0)
				$html .= '<div class="alert alert-danger" role="alert">当前还没有友链</div>';
			else
				$html .= $this->getLinksTable ( $page, $perPage, $page_num, $urlHandler );
		} // end else
		$html .= '</div></div>';
		$html .= '<div id="add_url" class="panel panel-default">
  <div class="panel-heading">添加友链</div>
  <div class="panel-body">';
		$html .= '<form class="form-horizontal">
  <div class="form-group">
    <label for="t_name" class="col-md-2 control-label">网站名称</label>
    <div class="col-md-10">
      <input type="text" class="form-control" id="t_name" placeholder="网站名称">
    </div>
  </div>
  <div class="form-group">
    <label for="t_url" class="col-md-2 control-label">网站地址</label>
    <div class="col-md-10">
      <input type="text" class="form-control" id="t_url" placeholder="网站地址" value="http://">
    </div>
  </div>
  <div class="form-group">
    <label for="t_color" class="col-md-2 control-label">链接颜色</label>
    <div class="col-md-10">
      <input type="text" class="form-control" id="t_color" placeholder="链接颜色">
    </div>
  </div>
  <div class="form-group">
    <div class="col-md-offset-2 col-md-10">
      <button type="button" class="btn btn-primary">添加友链</button>
    </div>
  </div>
</form>
</div></div>';
		$ajax_add_url = $urlHandler->createUrl ( 'ajax/AdminLinks', 'add', array (), false );
		$ajax_del_url = $urlHandler->createUrl ( 'ajax/AdminLinks', 'delete', array (), false );
		$ajax_update_url=$urlHandler->createUrl ( 'ajax/AdminLinks', 'update', array (), false );
		$html .= ('<script type="text/javascript">
		/*添加友链*/
		$("#add_url button").click(function(){
           	var r=confirm("你确定要添加此链接吗?");
           	if(r==false)
           		return;
			$.ajax({
				"url" : "'.$ajax_add_url.'",
				"method" : "POST",
				"cache" : false,
				"dataType" : "json",
				"data" : {
					"t_name":$("#t_name").val(),
					"t_url":$("#t_url").val(),
					"t_color":$("#t_color").val()
				},
				"success" : function(data) {
					if(data.success){
						alertModal("success","添加友链成功","成功添加链接:"+$("#t_url").val());
					}
					else
						alertModal("danger","添加友链失败",data.msg);
				},
				"error" : function(jqXHR, textStatus, errorThrown) {
					alertModal("danger","异步失败",errorThrown);
				}
			});/*end ajax*/
		});
        /*友链管理*/
        $("#admin_url tr:gt(0)").each(function(){
            var trNode=$(this);
            trNode.find("button").each(function(index){
                $(this).click(function(){
                    var postData,act_names=["修改","删除"],ajax_url;
                    var r=confirm("你确定要"+act_names[index]+"此链接吗?");
                    if(r==false)
                        return;
                    if(index==0){
						ajax_url="'.$ajax_update_url.'";
                        postData={
                            "t_id":trNode.find("td:eq(0)").html(),
							"t_name":trNode.find("input:eq(0)").val(),
							"t_url":trNode.find("input:eq(1)").val(),
							"t_color":trNode.find("input:eq(2)").val()
                        };
					}
                    else{
						ajax_url="'.$ajax_del_url.'";
                        postData={
                            "t_id":trNode.find("td:eq(0)").html()
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
                                alertModal("success",act_names[index]+"操作成功",act_names[index]+"友链成功");
                                if(index==1)
                                    trNode.remove();
                            }
                            else
                                alertModal("danger",act_names[index]+"操作失败",data.msg);
                        },
                        "error" : function(jqXHR, textStatus, errorThrown) {
                            alertModal("danger","异步失败",errorThrown);
                        }
                    });/*end ajax*/
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
		return $html;
	}
	/**
	 * 获取友链列表的表格
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
	private function getLinksTable($page, $perPage, $page_num, $urlHandler) {
		$html = '<table class="table table-hover table-bordered">';
		$html .= '<tr><th>链接编号</th><th>名称</th><th>URL</th><th>链接颜色</th><th></th></tr>';
		$trTpl = '<tr>
					<td>%s</td>
                    <td><input type="text" class="form-control" placeholder="名称" value="%s"/></td>
                    <td><input type="text" class="form-control" placeholder="URL" value="%s"/></td>
                    <td><input type="text" class="form-control" placeholder="颜色" value="%s"/></td>
                    <td><button type="button" class="btn btn-primary">修改</button>&nbsp;&nbsp;<button type="button" class="btn btn-danger">删除</button>
                    </td>
				</tr>';
		$offset = ($page - 1) * $perPage;
		$stm = $this->db->query ( 'SELECT * FROM ' . $this->tablePre . 'links ORDER BY t_id ASC LIMIT ' . $offset . ', ' . $perPage );
		while ( $tmp = $stm->fetch () ) {
			$html .= sprintf ( $trTpl, $tmp ['t_id'], $tmp ['t_name'], $tmp ['t_url'], $tmp ['t_color'] );
		}
		$html .= '</table>';
		$fenyeV=new Fenye();
		$urlTpl=$urlHandler->createUrl('web/BlogAdmin', 'links', array('page'=>'--page--'));
		$html.=$fenyeV->getNav($urlTpl, $page, $page_num);
		return $html;
	}
	public function getTitle($page) {
		$stm = $this->db->query ( 'SELECT t_value FROM ' . $this->tablePre . 'config WHERE t_key=\'blogname\'' );
		$rst = $stm->fetch ();
		return $rst ['t_value'] . '友链管理-第'.$page.'页';
	}
}