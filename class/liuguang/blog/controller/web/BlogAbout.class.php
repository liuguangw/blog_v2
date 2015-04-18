<?php

namespace liuguang\blog\controller\web;

use liuguang\blog\controller\Index;
use liuguang\blog\view\BlogRight as RightView;
use liuguang\blog\view\BlogAbout as CenterView;

/**
 * 关于本站
 *
 * @author liuguang
 *        
 */
class BlogAbout extends Index {
	public function indexAction() {
		$this->checkInstall ();
		$tpl = $this->getMainTpl ();
		$tplData = $tpl->getTplData ();
		$vModel = new CenterView ( $this->getDb (), $this->getTablePre ());
		$tplData->set ( 'title', $vModel->getTitle () );
		$tplData->set ( 'blog_center', $vModel->getHtml ( ) );
		$rightM = new RightView ( $this->getDb (), $this->getTablePre () );
		$tplData->set ( 'blog_right', $rightM->getHtml () );
		$tplData->set ( 'nIndex', 5 );
		$tpl->display ();
	}
}