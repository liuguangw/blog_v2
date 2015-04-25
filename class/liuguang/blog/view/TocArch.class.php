<?php

namespace liuguang\blog\view;

use liuguang\mvc\Application;

/**
 * 某个时间的文章归档列表
 *
 * @author liuguang
 *        
 */
class TocArch implements TopicListInter {
	private $db;
	private $tablePre;
	private $t_id;
	public function __construct(\PDO $db, $tablePre,$t_id) {
		$this->db = $db;
		$this->tablePre = $tablePre;
		$this->t_id=$t_id;
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \liuguang\blog\view\TopicListInter::getTopicCount()
	 *
	 */
	public function getTopicCount() {
		$stm = $this->db->query ( 'SELECT COUNT(*) AS topic_num FROM ' . $this->tablePre . 'topic WHERE post_ym='.$this->t_id );
		$rst = $stm->fetch ();
		return $rst ['topic_num'];
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \liuguang\blog\view\TopicListInter::getUrlTpl()
	 *
	 */
	public function getUrlTpl() {
		$app = Application::getApp ();
		$urlHandler = $app->getUrlHandler ();
		return $urlHandler->createUrl ( 'web/TocArch', 'index', array (
				't_id'=>$this->t_id,
				'page' => '--page--' 
		) );
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \liuguang\blog\view\TopicListInter::getTablePre()
	 *
	 */
	public function getTablePre() {
		return $this->tablePre;
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \liuguang\blog\view\TopicListInter::getPerPage()
	 *
	 */
	public function getPerPage() {
		return 12;
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \liuguang\blog\view\TopicListInter::getSelectSql()
	 *
	 */
	public function getSelectSql($page) {
		$limit = $this->getPerPage ();
		$sql='SELECT t_id,t_title,t_prev_text,post_time FROM ' . $this->tablePre. 'topic WHERE post_ym='.$this->t_id.' ORDER BY t_id DESC';
		$sql.= (' LIMIT ' . ($page - 1) * $limit . ',' . $limit);
		return $sql;
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \liuguang\blog\view\TopicListInter::getStr()
	 *
	 */
	public function getStr($page) {
		$str='['.substr($this->t_id, 0,4).'年'.substr($this->t_id, 4).'月]'.'-第'.$page.'页';
		return $str;
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \liuguang\blog\view\TopicListInter::getDb()
	 *
	 */
	public function getDb() {
		return $this->db;
	}
}