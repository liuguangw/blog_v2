<?php

namespace liuguang\blog\view;

use liuguang\mvc\Application;

/**
 * 用于获取博客右侧部分的html代码
 *
 * @author liuguang
 *        
 */
class BlogRight {
	private $db;
	private $tablePre;
	private $limit = 10; // 最多显示数目
	public function __construct(\PDO $db, $tablePre) {
		$this->db = $db;
		$this->tablePre = $tablePre;
	}
	public function getHtml() {
		return $this->getFenleiList ( 0, $this->limit ).$this->getArchList(0, $this->limit);
	}
	public function getFenleiList($start, $limit) {
		$app = Application::getApp ();
		$urlHandler = $app->getUrlHandler ();
		$blogTypesUrl = $urlHandler->createUrl ( 'web/BlogTypes', 'index', array (
				'page' => 1 
		) );
		$aBlind='<script type="text/javascript">
        $("#types_div a:not([class=\"pull-right\"])").bindPushState().click(function(){
			updateNav("#main_navbar a:eq(2)");	
		});
        </script>';
		$html = '<div id="types_div" class="panel panel-default">
  <div class="panel-heading">
	<a href="' . $blogTypesUrl . '">文章分类</a>
	<a href="javascript:refreshRight();" class="pull-right"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span></a>
  </div>';
		// 获取类别总数目
		$stm = $this->db->query ( 'SELECT COUNT(*) AS s_num FROM ' . $this->tablePre . 'leibie' );
		$rst = $stm->fetch ();
		if ($rst ['s_num'] == 0) {
			$html .= '<div class="panel-body">博主还没有创建一个分类</div>
</div>';
			return $html.$aBlind;
		}
		$html .= '<div class="list-group">';
		$sql = 'SELECT ' . $this->tablePre . 'leibie.t_id,' . $this->tablePre . 'leibie.t_name,COUNT(' . $this->tablePre . 'topic.leibie_id) as f_num FROM ' . $this->tablePre . 'leibie LEFT JOIN ' . $this->tablePre . 'topic ON ' . $this->tablePre . 'leibie.t_id=' . $this->tablePre . 'topic.leibie_id GROUP BY ' . $this->tablePre . 'leibie.t_id ORDER BY ' . $this->tablePre . 'leibie.t_id ASC';
		$sql .= (' LIMIT ' . $start . ',' . $limit);
		$stm = $this->db->query ( $sql );
		$listTpl = '<a href="%s" class="list-group-item">
            <span class="badge">%d</span> %s
            </a>';
		while ( $typeInfo = $stm->fetch () ) {
			$html .= sprintf ( $listTpl, $urlHandler->createUrl ( 'web/TocType', 'index', array (
					't_id' => $typeInfo ['t_id'] 
			) ), $typeInfo ['f_num'], $typeInfo ['t_name'] );
		}
		$html .= '</div></div>';
		return $html.$aBlind;
	}
	public function getArchList($start, $limit) {
		$app = Application::getApp ();
		$urlHandler = $app->getUrlHandler ();
		$blogArchUrl = $urlHandler->createUrl ( 'web/BlogArchs', 'index', array (
				'page' => 1 
		) );
		$aBlind='<script type="text/javascript">
        $("#archives a:not([class=\"pull-right\"])").bindPushState().click(function(){
			updateNav("#main_navbar a:eq(3)");	
		});
        </script>';
		$html = '<div id="archives" class="panel panel-default">
  <div class="panel-heading">
	<a href="' . $blogArchUrl . '">文章归档</a>
	<a href="javascript:refreshRight();" class="pull-right"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span></a>
  </div>';
		// 获取发表文章的总数目
		$stm = $this->db->query ( 'SELECT COUNT(*) AS s_num FROM ' . $this->tablePre . 'topic' );
		$rst = $stm->fetch ();
		if ($rst ['s_num'] == 0) {
			$html .= '<div class="panel-body">博主还没有发表一篇文章</div>
</div>';
			return $html.$aBlind;
		}
		$html .= '<div class="list-group">';
		$listTpl = '<a href="%s" class="list-group-item">
            <span class="badge">%d</span> %s
            </a>';
		$stm = $this->db->query ( 'SELECT post_ym,COUNT(*) AS a_num FROM ' . $this->tablePre . 'topic GROUP BY post_ym ORDER BY post_ym DESC LIMIT '.$start.', '.$limit );
		while ( $tmp = $stm->fetch () ) {
			$html .= sprintf ( $listTpl, $urlHandler->createUrl ( 'web/TocArch', 'index', array (
					't_id' => $tmp ['post_ym'] 
			) ), $tmp ['a_num'], substr ( $tmp ['post_ym'], 0, 4 ) . '-' . substr ( $tmp ['post_ym'], 4 ) );
		}
		$html .= '</div></div>';
		return $html.$aBlind;
	}
}