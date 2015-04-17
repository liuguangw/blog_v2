<?php

namespace liuguang\blog\view;

/**
 * 关于博客
 *
 * @author liuguang
 *        
 */
class BlogAbout {
	private $db;
	private $tablePre;
	public function __construct(\PDO $db, $tablePre) {
		$this->db = $db;
		$this->tablePre = $tablePre;
	}
	public function getHtml() {
		$html = '<div class="panel panel-default">
  <div class="panel-body">';
		$stm = $this->db->query ( 'SELECT t_value FROM ' . $this->tablePre . 'config WHERE t_key=\'abouts\'' );
		$rst = $stm->fetch ();
		$html .= ($rst ['t_value'].'</div></div>');
		return $html;
	}
	public function getTitle() {
		$stm = $this->db->query ( 'SELECT t_value FROM ' . $this->tablePre . 'config WHERE t_key=\'blogname\'' );
		$rst = $stm->fetch ();
		return $rst ['t_value'] . '-关于本站';
	}
}