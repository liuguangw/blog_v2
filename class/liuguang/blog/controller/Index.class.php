<?php

namespace liuguang\blog\controller;

use liuguang\blog\model\Template;
use liuguang\mvc\Application;
use liuguang\blog\view\BlogIndex as CenterView;
use liuguang\blog\view\BlogRight as RightView;

/**
 *
 * @author liuguang
 *        
 */
class Index extends BaseController {
	protected function getMainTpl() {
		$tpl = new Template ( 'index' );
		$tplData = $tpl->getTplData ();
		$result = array ();
		$db = $this->getDb ();
		$tablePre = $this->getTablePre ();
		$stm = $db->query ( 'SELECT * FROM ' . $tablePre . 'config WHERE t_key IN(\'blogname\',\'pass\',\'nickname\',\'touxiang_img\',\'description\',\'open_compress\')' );
		while ( $tmp = $stm->fetch () ) {
			$result [$tmp ['t_key']] = $tmp ['t_value'];
		}
		if ($result ['touxiang_img'] == '')
			$result ['touxiang_img'] = $tplData->get ( 'blog_context' ) . '/static/img/touxiang.jpg';
		$blogInfo = array (
				'blogname' => $result ['blogname'],
				'nickname' => $result ['nickname'],
				'touxiang_img' => $result ['touxiang_img'],
				'description' => $result ['description'],
				'load_js' => array (
						'ueditor' => false,
						'uploadify' => false 
				) 
		);
		if (! isset ( $_COOKIE ['osid'] ))
			$blogInfo ['is_login'] = false;
		else {
			if ($_COOKIE ['osid'] == $result ['pass'])
				$blogInfo ['is_login'] = true;
			else
				$blogInfo ['is_login'] = false;
		}
		$app = Application::getApp ();
		$urlHandler = $app->getUrlHandler ();
		$blogInfo ['pushUrl'] = $urlHandler->createUrl ( 'ajax/PushUrl', 'index', array (), false );
		$blogInfo ['rcodeUrlTpl'] = $urlHandler->createUrl ( 'ajax/BlogUtil', 'rcode', array ('rand'=>'[rand]'), false );
		$blogInfo ['doLoginUrl'] = $urlHandler->createUrl ( 'ajax/BlogUtil', 'dologin', array (), false );
		$blogInfo['logoutUrl']=$urlHandler->createUrl ( 'Index', 'index', array (),false );
		$tplData->set ( 'blogInfo', json_encode ( $blogInfo ) );
		$tplData->set ( 'nIndex', 0 );
		$tplData->set ( 'title', $result ['blogname'] );
		$tplData->set ( 'blogCsspath', $urlHandler->createUrl ( 'ajax/BlogUtil', 'css', array () ) );
		$tplData->set ( 'blogIndexUrl', $urlHandler->createUrl ( 'Index', 'index', array () ) );
		$tplData->set ( 'blogListUrl', $urlHandler->createUrl ( 'web/BlogList', 'index', array (
				'page' => 1 
		) ) );
		$tplData->set ( 'blogTypesUrl', $urlHandler->createUrl ( 'web/BlogTypes', 'index', array (
				'page' => 1 
		) ) );
		$tplData->set ( 'blogArchsUrl', $urlHandler->createUrl ( 'web/BlogArchs', 'index', array (
				'page' => 1 
		) ) );
		$tplData->set ( 'blogLiuyanUrl', $urlHandler->createUrl ( 'web/BlogLiuyan', 'index', array (
				'page' => 1 
		) ) );
		$tplData->set ( 'blogAboutUrl', $urlHandler->createUrl ( 'web/BlogAbout', 'index', array () ) );
		$tplData->set ( 'adminSetsUrl', $urlHandler->createUrl ( 'web/BlogAdmin', 'sets', array () ) );
		$tplData->set ( 'adminTypesUrl', $urlHandler->createUrl ( 'web/BlogAdmin', 'types', array () ) );
		$tplData->set ( 'adminTagsUrl', $urlHandler->createUrl ( 'web/BlogAdmin', 'tags', array () ) );
		$tplData->set ( 'adminFilesUrl', $urlHandler->createUrl ( 'web/BlogAdmin', 'files', array () ) );
		$tplData->set ( 'postTopicUrl', $urlHandler->createUrl ( 'web/BlogAdmin', 'postTopic', array () ) );
		$tplData->set ( 'adminEnvUrl', $urlHandler->createUrl ( 'web/BlogAdmin', 'env', array () ) );
		$tpl->setCompress ( ($result ['open_compress'] == 1) );
		return $tpl;
	}
	public function indexAction() {
		$this->checkInstall ();
		$tpl = $this->getMainTpl ();
		$tplData=$tpl->getTplData();
		$centerM=new CenterView($this->getDb(),$this->getTablePre());
		$tplData->set('blog_center', $centerM->getHtml());
		$tplData->set('title', $centerM->getTitle());
		$rightM=new RightView($this->getDb(),$this->getTablePre());
		$tplData->set('blog_right', $rightM->getHtml());
		$tpl->display ();
	}
}