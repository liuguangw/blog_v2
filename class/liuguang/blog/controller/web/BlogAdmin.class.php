<?php

namespace liuguang\blog\controller\web;

use liuguang\blog\controller\Index;
use liuguang\blog\view\BlogRight as RightView;
use liuguang\mvc\Application;
use liuguang\blog\view\AdminSets;
use liuguang\blog\view\PostTopic;
use liuguang\blog\view\EditTopic;
use liuguang\blog\view\AdminTags;
use liuguang\blog\view\AdminFiles;
use liuguang\blog\view\AdminEnv;
use liuguang\blog\model\User;

/**
 * 后台
 *
 * @author liuguang
 *        
 */
class BlogAdmin extends Index {
	public function setsAction() {
		$this->checkInstall ();
		$this->checkAdmin ();
		$tpl = $this->getMainTpl ();
		$tplData = $tpl->getTplData ();
		$vModel = new AdminSets ( $this->getDb (), $this->getTablePre () );
		$tplData->set ( 'title', $vModel->getTitle () );
		$tplData->set ( 'blog_center', $vModel->getHtml () );
		$rightM = new RightView ( $this->getDb (), $this->getTablePre () );
		$tplData->set ( 'blog_right', $rightM->getHtml () );
		$tplData->set ( 'nIndex', 1 );
		$tpl->display ();
	}
	public function postTopicAction() {
		$this->checkInstall ();
		$this->checkAdmin ();
		$tpl = $this->getMainTpl ();
		$tplData = $tpl->getTplData ();
		$vModel = new PostTopic ( $this->getDb (), $this->getTablePre () );
		$tplData->set ( 'title', $vModel->getTitle () );
		$tplData->set ( 'blog_center', $vModel->getHtml () );
		$rightM = new RightView ( $this->getDb (), $this->getTablePre () );
		$tplData->set ( 'blog_right', $rightM->getHtml () );
		$tplData->set ( 'nIndex', 1 );
		$tpl->display ();
	}
	public function editTopicAction() {
		$this->checkInstall ();
		$this->checkAdmin ();
		$tpl = $this->getMainTpl ();
		$tplData = $tpl->getTplData ();
		$app = Application::getApp ();
		$urlData = $app->getUrlHandler ()->getUrlData ();
		$t_id = ( int ) $urlData->get ( 't_id', 1 );
		$vModel = new EditTopic ( $this->getDb (), $this->getTablePre (), $t_id );
		$tplData->set ( 'title', $vModel->getTitle () );
		$tplData->set ( 'blog_center', $vModel->getHtml () );
		$rightM = new RightView ( $this->getDb (), $this->getTablePre () );
		$tplData->set ( 'blog_right', $rightM->getHtml () );
		$tplData->set ( 'nIndex', 1 );
		$tpl->display ();
	}
	public function typesAction() {
		$this->checkInstall ();
		$this->checkAdmin ();
		$tpl = $this->getMainTpl ();
		$tplData = $tpl->getTplData ();
		$vModel = new AdminTags ( $this->getDb (), $this->getTablePre (), false );
		$tplData->set ( 'title', $vModel->getTitle () );
		$tplData->set ( 'blog_center', $vModel->getHtml () );
		$rightM = new RightView ( $this->getDb (), $this->getTablePre () );
		$tplData->set ( 'blog_right', $rightM->getHtml () );
		$tplData->set ( 'nIndex', 1 );
		$tpl->display ();
	}
	public function tagsAction() {
		$this->checkInstall ();
		$this->checkAdmin ();
		$tpl = $this->getMainTpl ();
		$tplData = $tpl->getTplData ();
		$vModel = new AdminTags ( $this->getDb (), $this->getTablePre (), true );
		$tplData->set ( 'title', $vModel->getTitle () );
		$tplData->set ( 'blog_center', $vModel->getHtml () );
		$rightM = new RightView ( $this->getDb (), $this->getTablePre () );
		$tplData->set ( 'blog_right', $rightM->getHtml () );
		$tplData->set ( 'nIndex', 1 );
		$tpl->display ();
	}
	public function filesAction() {
		$this->checkInstall ();
		$this->checkAdmin ();
		$tpl = $this->getMainTpl ();
		$tplData = $tpl->getTplData ();
		$app = Application::getApp ();
		$urlData = $app->getUrlHandler ()->getUrlData ();
		$vModel = new AdminFiles ( $this->getDb (), $this->getTablePre (), $this->getFs () );
		$page = ( int ) $urlData->get ( 'page', 1 );
		$tplData->set ( 'title', $vModel->getTitle ( $page ) );
		$tplData->set ( 'blog_center', $vModel->getHtml ( $page ) );
		$rightM = new RightView ( $this->getDb (), $this->getTablePre () );
		$tplData->set ( 'blog_right', $rightM->getHtml () );
		$tplData->set ( 'nIndex', 1 );
		$tpl->display ();
	}
	public function envAction() {
		$this->checkInstall ();
		$this->checkAdmin ();
		$tpl = $this->getMainTpl ();
		$tplData = $tpl->getTplData ();
		$vModel = new AdminEnv ( $this->getDb (), $this->getTablePre (), $this->getFs () );
		$tplData->set ( 'title', $vModel->getTitle () );
		$tplData->set ( 'blog_center', $vModel->getHtml () );
		$rightM = new RightView ( $this->getDb (), $this->getTablePre () );
		$tplData->set ( 'blog_right', $rightM->getHtml () );
		$tplData->set ( 'nIndex', 1 );
		$tpl->display ();
	}
	private function checkAdmin() {
		$db = $this->getDb ();
		$tablePre = $this->getTablePre ();
		$user = new User ();
		if (! $user->checkAdmin ( $db, $tablePre )) {
			$tpl = $this->getMainTpl ();
			$tplData = $tpl->getTplData ();
			$tplData->set ( 'title', '无权访问' );
			$tplData->set ( 'blog_center', '<div class="alert alert-danger" role="alert">只有博主有权限访问当前页面</div>' );
			$rightM = new RightView ( $this->getDb (), $this->getTablePre () );
			$tplData->set ( 'blog_right', $rightM->getHtml () );
			$tplData->set ( 'nIndex', 1 );
			$tpl->display ();
			exit ();
		}
	}
}