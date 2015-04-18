<?php

namespace liuguang\blog\controller\web;

use liuguang\blog\controller\Index;
use liuguang\blog\view\BlogRight as RightView;
use liuguang\blog\view\TopicPage as CenterView;
use liuguang\mvc\Application;

/**
 * 帖子页面
 *
 * @author liuguang
 *        
 */
class Topic extends Index {
	public function indexAction() {
		$this->checkInstall ();
		$tpl = $this->getMainTpl ();
		$tplData = $tpl->getTplData ();
		$app=Application::getApp();
		$urlData=$app->getUrlHandler()->getUrlData();
		$t_id=(int)$urlData->get('t_id',1);
		$vModel = new CenterView ( $this->getDb (), $this->getTablePre (),$t_id);
		$tplData->set ( 'title', $vModel->getTitle () );
		$tplData->set ( 'blog_center', $vModel->getHtml ( ) );
		$rightM = new RightView ( $this->getDb (), $this->getTablePre () );
		$tplData->set ( 'blog_right', $rightM->getHtml () );
		$tplData->set ( 'nIndex', 1 );
		$tpl->display ();
	}
}