<?php

namespace liuguang\blog\view;

use liuguang\mvc\Application;

/**
 *
 * @author liuguang
 *        
 */
class BlogList {
	private $db;
	private $tablePre;
	public function __construct(\PDO $db, $tablePre) {
		$this->db = $db;
		$this->tablePre = $tablePre;
	}
	/**
	 * 获取文章列表,使用之前需要确定$page是否为数字
	 *
	 * @param int $page
	 *        	页码数
	 */
	public function getHtml($page) {
		$stm = $this->db->query ( 'SELECT COUNT(*) AS topic_num FROM ' . $this->tablePre . 'topic' );
		$rst = $stm->fetch ();
		if ($rst ['topic_num'] == 0)
			return '<div class="panel panel-default">
  <div class="panel-body">
    没有文章
  </div>
</div>';
		$limit = 12; // 每页最多显示12条最新文章
		$page_num = ceil ( $rst ['topic_num'] / $limit ); // 总页码数
		if (($page < 1) || ($page > $page_num))
			return '<div class="alert alert-danger" role="alert">当前页面不存在</div>';
		$app = Application::getApp ();
		$appConfig = $app->getAppConfig ();
		$urlHandler = $app->getUrlHandler ();
		date_default_timezone_set ( $appConfig->get ( 'timeZone' ) );
		$nodeTpl = '<div class="panel panel-default">
  <div class="panel-heading">%s</div>
  <div class="panel-body">%s...</div>
  <div class="panel-footer" style="text-align:right">发表于%s,<a href="%s">[阅读全文]</a></div>
</div>';
		$html = '';
		$stm = $this->db->query ( 'SELECT t_id,t_title,t_prev_text,post_time FROM ' . $this->tablePre . 'topic ORDER BY t_id DESC Limit ' . ($page - 1) * $limit . ',' . $limit );
		while ( $tmp = $stm->fetch () ) {
			$html .= sprintf ( $nodeTpl, $tmp ['t_title'], $tmp ['t_prev_text'], date ( 'Y-m-d H:i:s', $tmp ['post_time'] ), $urlHandler->createUrl ( 'web/Topic', 'index', array (
					't_id' => $tmp ['t_id'] 
			) ) );
		}
		$fenyeV = new Fenye ();
		$html .= $fenyeV->getNav ( $urlHandler->createUrl ( 'web/Topic', 'index', array (
				't_id' => '%d' 
		) ), $page, $page_num );
		$html .= '<script type="text/javascript">
            $("#blog_center .panel-footer").find("a").bindPushState();
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
		$stm = $this->db->query ( 'SELECT t_value FROM ' . $this->tablePre . 'config WHERE t_key=\'blogname\'' );
		$rst = $stm->fetch ();
		return $rst['t_value'].'-文章一览-第'.$page.'页';
	}
}