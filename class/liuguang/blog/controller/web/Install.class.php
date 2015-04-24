<?php

namespace liuguang\blog\controller\web;

use liuguang\blog\controller\BaseController;
use liuguang\blog\model\Template;
use liuguang\mvc\Application;
/**
 *
 * @author liuguang
 *        
 */
class Install extends BaseController{
	public function indexAction(){
		//检查数据库连接
		$db=$this->getDb();
		$tpl=new Template('install');
		$tplData=$tpl->getTplData();
		$app=Application::getApp();
		$appConfig=$app->getAppConfig();
		$urlHandler=$app->getUrlHandler();
		$checkInputUrl=$urlHandler->createUrl('ajax/Install', 'index',array(),false);
		$doInstallUrl=$urlHandler->createUrl('ajax/Install', 'do',array(),false);
		$tplData->set('checkInputUrl',$checkInputUrl);
		$tplData->set('doInstallUrl', $doInstallUrl);
		$tplData->set('blogInit', $appConfig->get('blogInit',false)?'true':'false');
		$tplData->set ( 'blogIndexUrl', $urlHandler->createUrl ( 'Index', 'index', array () ,false) );
		$tpl->setCompress(true);
		$tpl->display();
	}
}