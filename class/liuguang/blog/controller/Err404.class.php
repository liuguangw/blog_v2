<?php

namespace liuguang\blog\controller;
use liuguang\blog\model\Template;
use liuguang\mvc\Application;
/**
 *
 * @author ac er
 *        
 */
class Err404 {
	public function indexAction() {
		$tpl = new Template ( '404');
		$tplData=$tpl->getTplData();
		$app=Application::getApp();
		$urlHandler=$app->getUrlHandler();
		$tplData->set ( 'blogIndexUrl', $urlHandler->createUrl ( 'Index', 'index', array () ) );
		$tpl->display();
	}
}