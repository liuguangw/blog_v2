<?php

namespace liuguang\blog\controller\web;

use liuguang\blog\controller\Index;
use liuguang\blog\view\BlogRight as RightView;
use liuguang\blog\view\TagList as CenterView;
use liuguang\blog\view\TopicList;
use liuguang\mvc\Application;
/**
 * 某个tag的某一页文章列表
 *
 * @author liuguang
 *        
 */
class Tag extends Index {
	public function indexAction() {
		$this->checkInstall ();
		$tpl = $this->getMainTpl ();
		$tplData=$tpl->getTplData();
		$app=Application::getApp();
		$urlData=$app->getUrlHandler()->getUrlData();
		$t_id=(int)$urlData->get('t_id',1);
		$vModel0=new CenterView($this->getDb(), $this->getTablePre(),$t_id);
		$vModel=new TopicList($vModel0);
		$page=(int)$urlData->get('page',1);
		$tplData->set('title', $vModel->getTitle($page));
		$tplData->set('blog_center', $vModel->getHtml($page));
		$rightM=new RightView($this->getDb(),$this->getTablePre());
		$tplData->set('blog_right', $rightM->getHtml());
		$tplData->set('nIndex', 1);
		$tpl->display ();
	}
}