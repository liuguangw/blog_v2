<?php

namespace liuguang\blog\controller\ajax;

use liuguang\blog\controller\BaseController;

/**
 * 后台的基础控制器
 *
 * @author liuguang
 *        
 */
class BaseAdmin extends BaseController {
	protected function isAdmin($osid = '') {
		if ($osid == '') {
			if (! isset ( $_COOKIE ['osid'] ))
				return false;
			$osid=$_COOKIE ['osid'];
		}
		$stm = $this->getDb ()->query ( 'SELECT * FROM ' . $this->getTablePre () . 'config WHERE t_key=\'pass\'' );
		$rst = $stm->fetch ();
		if ($osid != $rst ['t_value'])
			return false;
		else
			return true;
	}
}