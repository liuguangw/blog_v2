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
		$app=Application::getApp();
		$appConfig=$app->getAppConfig();
		$checkInputUrl=$app->getUrlHandler()->createUrl('ajax/Install', 'index',array(),false);
		$doInstallUrl=$app->getUrlHandler()->createUrl('ajax/Install', 'do',array(),false);
		$tpl->getTplData()->set('checkInputUrl',$checkInputUrl);
		$tpl->getTplData()->set('doInstallUrl', $doInstallUrl);
		$tpl->getTplData()->set('blogInit', $appConfig->get('blogInit',false)?'true':'false');
		$tpl->setCompress(true);
		$tpl->display();
	}
}