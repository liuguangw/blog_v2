<?php

namespace liuguang\blog\view;

use liuguang\mvc\Application;

/**
 * 文章一览
 *
 * @author liuguang
 *        
 */
class BlogList implements TopicListInter {
	private $db;
	private $tablePre;
	public function __construct(\PDO $db, $tablePre) {
		$this->db = $db;
		$this->tablePre = $tablePre;
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \liuguang\blog\view\TopicListInter::getTopicCount()
	 *
	 */
	public function getTopicCount() {
		$stm = $this->db->query ( 'SELECT COUNT(*) AS topic_num FROM ' . $this->tablePre . 'topic' );
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
		return $urlHandler->createUrl ( 'web/BlogList', 'index', array (
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
		$sql = 'SELECT t_id,t_title,t_prev_text,post_time FROM ' . $this->tablePre . 'topic ORDER BY t_id DESC Limit ' . ($page - 1) * $limit . ',' . $limit;
		return $sql;
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \liuguang\blog\view\TopicListInter::getStr()
	 *
	 */
	public function getStr($page) {
		return '文章一览-第'.$page.'页';
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