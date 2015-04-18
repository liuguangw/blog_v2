<?php

namespace liuguang\blog\view;

use liuguang\mvc\Application;

/**
 * 用于生产首页文章列表的视图
 *
 * @author liuguang
 *        
 */
class BlogIndex {
	private $db;
	private $tablePre;
	public function __construct(\PDO $db, $tablePre) {
		$this->db = $db;
		$this->tablePre = $tablePre;
	}
	public function getHtml() {
		$stm = $this->db->query ( 'SELECT COUNT(*) AS itemNum FROM ' . $this->tablePre . 'topic' );
		$rst = $stm->fetch ();
		if ($rst ['itemNum'] == 0)
			return '<div class="panel panel-default">
  <div class="panel-body">
   博主还没有发表过一篇文章
  </div>
</div>';
		$limit = 8; // 首页最多显示八条文章
		$app = Application::getApp ();
		$appConfig = $app->getAppConfig ();
		$urlHandler = $app->getUrlHandler ();
		date_default_timezone_set ( $appConfig->get ( 'timeZone' ) );
		$nodeTpl = '<div class="panel panel-default topic-node">
  <div class="panel-heading"><a href="%s">%s</a></div>
  <div class="panel-body">%s...</div>
  <div class="panel-footer" style="text-align:right">发表于%s,<a href="%s">[阅读全文]</a></div>
</div>';
		$html = '';
		$stm = $this->db->query ( 'SELECT t_id,t_title,t_prev_text,post_time FROM ' . $this->tablePre . 'topic ORDER BY t_id DESC Limit 0,' . $limit );
		while ( $tmp = $stm->fetch () ) {
			$topicHref=$urlHandler->createUrl ( 'web/Topic', 'index', array (
					't_id' => $tmp ['t_id'] 
			) );
			$html .= sprintf ( $nodeTpl,$topicHref, $tmp ['t_title'], $tmp ['t_prev_text'], date ( 'Y-m-d H:i:s', $tmp ['post_time'] ), $topicHref);
		}
		$html .= '<script type="text/javascript">
            $(".topic-node a").bindPushState();
        </script>';
		return $html;
	}
	public function getTitle() {
		$stm = $this->db->query ( 'SELECT t_value FROM ' . $this->tablePre . 'config WHERE t_key=\'blogname\'' );
		$rst = $stm->fetch ();
		return $rst['t_value'];
	}
}