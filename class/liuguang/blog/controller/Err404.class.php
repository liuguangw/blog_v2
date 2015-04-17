<?php

namespace liuguang\blog\controller;
use liuguang\blog\model\Template;
/**
 *
 * @author ac er
 *        
 */
class Err404 {
	public function indexAction() {
		$tpl = new Template ( '404');
		$tpl->display();
	}
}