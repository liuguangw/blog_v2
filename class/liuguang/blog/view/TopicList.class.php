<?php

namespace liuguang\blog\view;
use liuguang\mvc\Application;
/**
 * 显示文章列表
 *
 * @author liuguang
 *        
 */
class TopicList {
	private $db;
	private $tablePre;
	private $listInter;
	public function __construct(TopicListInter $listInter){
		$this->listInter=$listInter;
		$this->db=$listInter->getDb();
		$this->tablePre=$listInter->getTablePre();
	}
	public function getHtml($page){
		$topicNum=$this->listInter->getTopicCount();
		if ($topicNum == 0)
			return '<div class="panel panel-default">
  <div class="panel-body">
    没有文章
  </div>
</div>';
		$limit=$this->listInter->getPerPage();
		$page_num = ceil ( $topicNum / $limit ); // 总页码数
		if (($page < 1) || ($page > $page_num))
			return '<div class="alert alert-danger" role="alert">当前页面不存在</div>';
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
		$stm = $this->db->query($this->listInter->getSelectSql($page));
		while ( $tmp = $stm->fetch () ) {
			$topicHref=$urlHandler->createUrl ( 'web/Topic', 'index', array (
					't_id' => $tmp ['t_id'] 
			) );
			$html .= sprintf ( $nodeTpl,$topicHref, $tmp ['t_title'], $tmp ['t_prev_text'], date ( 'Y-m-d H:i:s', $tmp ['post_time'] ), $topicHref);
		}
		$fenyeV = new Fenye ();
		$urlTpl=$this->listInter->getUrlTpl();
		$html .= $fenyeV->getNav ($urlTpl,$page, $page_num );
		$html .= '<script type="text/javascript">
			$(".topic-node a").bindPushState();
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
		return $rst['t_value'].'-'.$this->listInter->getStr($page);
	}
}