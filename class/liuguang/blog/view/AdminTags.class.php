<?php

namespace liuguang\blog\view;

use liuguang\mvc\Application;

/**
 * 管理分类和标签的页面视图
 *
 * @author liuguang
 *        
 */
class AdminTags {
	private $db;
	private $tablePre;
	private $isTag;
	private $tName;
	public function __construct(\PDO $db, $tablePre, $isTag) {
		$this->db = $db;
		$this->tablePre = $tablePre;
		$this->isTag = $isTag;
		$this->tName = ($isTag ? '标签' : '分类');
	}
	public function getHtml() {
		$app = Application::getApp ();
		$appConfig = $app->getAppConfig ();
		$urlHandler = $app->getUrlHandler ();
		date_default_timezone_set ( $appConfig->get ( 'timeZone' ) );
		if ($this->isTag) {
			$tableName = $this->tablePre . 'tag';
			$t_type = 1;
		} else {
			$tableName = $this->tablePre . 'leibie';
			$t_type = 2;
		}
		$html = '<div class="panel panel-default">
  <div class="panel-heading">文章'.$this->tName.'管理</div>
  <div class="panel-body">';
		$stm = $this->db->query ( 'SELECT COUNT(*) AS s_num FROM ' . $tableName );
		$rst = $stm->fetch ();
		$fenlei_num = $rst ['s_num'];
		if ($fenlei_num != 0) {
			$html .= '<table id="fenlei_list" class="table table-hover table-bordered">';
			$html .= ('<tr><th>' . $this->tName . '编号</th><th>' . $this->tName . '名称</th><th>创建时间</th><th></th></tr>');
			$trTpl = '<tr><td>%s</td><td><input type="text" class="form-control" placeholder="' . $this->tName . '名" value="%s"/></td><td>%s</td><td><button type="button" class="btn btn-primary">修改</button>&nbsp;&nbsp;<button type="button" class="btn btn-danger">删除</button></td></tr>';
			$stm=$this->db->query('SELECT * FROM ' . $tableName.' ORDER BY t_id ASC');
			while($tmp=$stm->fetch()) {
				$html .= sprintf ( $trTpl, $tmp ['t_id'], $tmp ['t_name'], date ( 'Y-m-d H:i:s', $tmp ['create_time'] ) );
			}
			$html .= '</table>';
			$ajaxUpdateUrl=$urlHandler->createUrl('ajax/AdminTags', 'update', array(),false);
			$ajaxDelUrl=$urlHandler->createUrl('ajax/AdminTags', 'delete', array(),false);
			$html .= ('<script type="text/javascript">
            $("#fenlei_list tr:gt(0)").each(function(){
                var trNode=$(this);
                trNode.find("button").each(function(index){
                    $(this).click(function(){
                    var postData,actionArrs=[["修改标签","删除标签"],["修改分类","删除分类"]],ajaxUrl;
                    var r=confirm("你确定要"+actionArrs[' . $t_type . '-1][index]+"吗?");
                    if(r==false)
                        return;
                    if(index==0){
                        postData={
                        "t_id":trNode.find("td:eq(0)").html(),
                        "t_type":' . $t_type . ',
                        "t_name":trNode.find("input").val()
                        };
						ajaxUrl="'.$ajaxUpdateUrl.'";
                    }
                    else{
                        postData={
                        "t_id":trNode.find("td:eq(0)").html(),
                        "t_type":' . $t_type . '
                        };
						ajaxUrl="'.$ajaxDelUrl.'";
                    }
                        $.ajax({
                            "url" : ajaxUrl,
                            "method" : "POST",
                            "cache" : false,
                            "dataType" : "json",
                            "data" : postData,
                            "success" : function(data) {
                                if(data.success==1){
                                    /*操作成功*/
                                    if(index==0)
                                        alertModal("success","修改成功","修改' . $this->tName . '成功");
                                    else{
                                        alertModal("success","删除成功","删除' . $this->tName . '"+trNode.find("input").val()+"成功");
                                        trNode.remove();
                                    }
                                }
                                else
                                    alertModal("danger","操作失败",data.msg);//操作失败
                            },
                            "error" : function(jqXHR, textStatus, errorThrown) {
                                alertModal("danger","异步失败",errorThrown);//异步失败
                            }
                        });/*end ajax*/
                    });
                });
            });
            </script>');
		} else
			$html .= ('<div class="alert alert-warning" role="alert">当前还没有' . $this->tName . '</div>');
		$html .= ('<form class="form-horizontal" id="add_fenlei">
<div class="row">
    <div class="col-sm-10">
    <input type="text" class="form-control" id="t_title" placeholder="新' . $this->tName . '名称">
    </div>
    <div class="col-sm-2">
    <button type="button" class="btn btn-primary">添加' . $this->tName . '</button>
    </div>
</div>
</form>');
		$html .= '</div></div></div>';
		$ajaxAddUrl=$urlHandler->createUrl('ajax/AdminTags', 'add', array(),false);
		$html .= ('<script type="text/javascript">
        $("#add_fenlei button").click(function(){
            var actionArrs=["添加标签","添加分类"],tName;
            var r=confirm("你确定要"+actionArrs[' . $t_type . '-1]+"吗?");
            if(r==false)
                return;
            tName=$("#t_title").val();
            $.ajax({
                "url" : "' . $ajaxAddUrl . '",
                "method" : "POST",
                "cache" : false,
                "dataType" : "json",
                "data" : {
                    "t_name":tName,
                    "t_type":' . $t_type . '
                },
                "success" : function(data) {
                    if(data.success==1){
                        alertModal("success","添加成功","添加' . $this->tName . '"+tName+"成功");
                    }
                    else
                        alertModal("danger","操作失败",data.msg);
                },
                "error" : function(jqXHR, textStatus, errorThrown) {
                    alertModal("danger","异步失败",errorThrown);
                }
            });
        });
        </script>');
		return $html;
	}
	public function getTitle() {
		$stm = $this->db->query ( 'SELECT t_value FROM ' . $this->tablePre . 'config WHERE t_key=\'blogname\'' );
		$rst = $stm->fetch ();
		return $rst ['t_value'] . '-' . $this->tName . '管理';
	}
}