<?php

namespace liuguang\blog\view;

use liuguang\mvc\Application;

/**
 *
 * @author liuguang
 *        
 */
class TypesArchs {
	private $db;
	private $tablePre;
	private $isType;
	public function __construct(\PDO $db, $tablePre, $isType) {
		$this->db = $db;
		$this->tablePre = $tablePre;
		$this->isType = $isType;
	}
	public function getHtml($page) {
		$html = '<div id="list_div" class="panel panel-default">
  <div class="panel-heading">' . ($this->isType ? '文章分类' : '文章归档') . '第' . $page . '页</a></div>';
		if ($this->isType)
			$sql = 'SELECT COUNT(*) AS s_num FROM ' . $this->tablePre . 'leibie';
		else
			$sql = 'SELECT COUNT(DISTINCT post_ym) AS s_num FROM ' . $this->tablePre . 'topic';
		$stm = $this->db->query ( $sql );
		$rst = $stm->fetch ();
		if ($rst ['s_num'] == 0) {
			if ($this->isType)
				$html .= '<div class="panel-body">博主还没有创建一个分类</div></div>';
			else
				$html .= '<div class="panel-body">博主还没有发表一篇文章</div></div>';
			return $html;
		}
		$limit = 12; // 每页最多显示12条
		$page_num = ceil ( $rst ['s_num'] / $limit ); // 总页码数
		if (($page < 1) || ($page > $page_num))
			return '<div class="panel-body"><div class="alert alert-danger" role="alert">当前页面不存在</div></div></div>';
		$html .= '<div class="list-group">';
		$start = ($page - 1) * $limit;
		if ($this->isType) {
			$sql = 'SELECT ' . $this->tablePre . 'leibie.t_id,' . $this->tablePre . 'leibie.t_name,COUNT(' . $this->tablePre . 'topic.leibie_id) as f_num FROM ' . $this->tablePre . 'leibie LEFT JOIN ' . $this->tablePre . 'topic ON ' . $this->tablePre . 'leibie.t_id=' . $this->tablePre . 'topic.leibie_id GROUP BY ' . $this->tablePre . 'leibie.t_id ORDER BY ' . $this->tablePre . 'leibie.t_id ASC';
			$sql .= (' LIMIT ' . $start . ',' . $limit);
		} else {
			$sql = 'SELECT post_ym,COUNT(*) AS a_num FROM ' . $this->tablePre . 'topic GROUP BY post_ym ORDER BY post_ym DESC LIMIT ' . $start . ', ' . $limit;
		}
		$listTpl = '<a href="%s" class="list-group-item">
            <span class="badge">%d</span> %s
            </a>';
		$app = Application::getApp ();
		$urlHandler = $app->getUrlHandler ();
		$stm=$this->db->query($sql);
		while ( $tmp = $stm->fetch () ) {
			if ($this->isType)
				$html .= sprintf ( $listTpl, $urlHandler->createUrl ( 'web/TocType', 'index', array (
						't_id' => $tmp ['t_id'] 
				) ), $tmp ['f_num'], $tmp ['t_name'] );
			else
				$html .= sprintf ( $listTpl, $urlHandler->createUrl ( 'web/TocArch', 'index', array (
						't_id' => $tmp ['post_ym'] 
				) ), $tmp ['a_num'], substr ( $tmp ['post_ym'], 0, 4 ) . '-' . substr ( $tmp ['post_ym'], 4 ) );
		}
		$html .= '</div></div>';
		$fenyeV = new Fenye ();
		$html .= $fenyeV->getNav ( $urlHandler->createUrl ( (($this->isType)?'web/BlogTypes':'web/BlogArchs'), 'index', array (
				'page' => '--page--' 
		) ), $page, $page_num );
		$html .= '<script type="text/javascript">
            $("#list_div a").bindPushState();
            $("#f_fenye a").each(function(){
                if($(this).parent().is("li[class]"))
                    $(this).click(function(evt){
                        evt.preventDefault();
                    });
                else
                    $(this).bindPushState();
            });
        </script>';
		return $html;
	}
	public function getTitle($page) {
		$str = ($this->isType ? '-文章类别' : '-文章归档');
		$stm = $this->db->query ( 'SELECT t_value FROM ' . $this->tablePre . 'config WHERE t_key=\'blogname\'' );
		$rst = $stm->fetch ();
		return $rst ['t_value'] . $str . '-第' . $page . '页';
	}
}