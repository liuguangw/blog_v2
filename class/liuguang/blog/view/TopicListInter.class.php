<?php

namespace liuguang\blog\view;

/**
 * 获取文章列表的接口
 *
 * @author liuguang
 *        
 */
interface TopicListInter {
	/**
	 * 根据页码获取说明文字,如"XXX类别-第5页"
	 * 
	 * @param int $page
	 * @return string
	 */
	public function getStr($page);
	/**
	 * 获取翻页URL模板
	 * 
	 * @return string
	 */
	public function getUrlTpl();
	/**
	 * 获取可以显示的文章总数目
	 * 
	 * @return int
	 */
	public function getTopicCount();
	/**
	 * 获取每页最多显示的数目
	 * 
	 * @return int
	 */
	public function getPerPage();
	/**
	 * 获取此页数据的sql语句
	 * 
	 * @return string
	 */
	public function getSelectSql($page);
	/**
	 * 获取数据库对象
	 * 
	 * @return \PDO
	 */
	public function getDb();
	/**
	 * 获取数据表前缀
	 * 
	 * @return string
	 */
	public function getTablePre();
}