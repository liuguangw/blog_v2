<?php

namespace liuguang\blog\controller\web;

use liuguang\blog\controller\Index;
use liuguang\blog\view\BlogRight as RightView;
use liuguang\blog\view\BlogList as CenterView;
use liuguang\blog\view\TopicList;
use liuguang\mvc\Application;
/**
 * 文章一览
 *
 * @author liuguang
 *        
 */
class BlogList extends Index {
	public function indexAction() {
		$this->checkInstall ();
		$tpl = $this->getMainTpl ();
		$tplData=$tpl->getTplData();
		$app=Application::getApp();
		$urlData=$app->getUrlHandler()->getUrlData();
		$vModel0=new CenterView($this->getDb(), $this->getTablePre());
		$vModel=new TopicList($vModel0);
		$page=(int)$urlData->get('page',1);
		$tplData->set('title', $vModel->getTitle($page));
		$tplData->set('blog_center', $vModel->getHtml($page));
		$rightM=new RightView($this->getDb(),$this->getTablePre());
		$tplData->set('blog_right', $rightM->getHtml());
		$tpl->display ();
	}
}