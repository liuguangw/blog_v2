<?php

namespace liuguang\blog\controller\ajax;

use liuguang\blog\model\Template;
use liuguang\blog\controller\BaseController;
use think\Verify;
use liuguang\mvc\DataMap;
use liuguang\blog\model\User;
use liuguang\blog\view\BlogRight;

/**
 * 博客的工具类控制器
 *
 * @author liuguang
 *        
 */
class BlogUtil extends BaseController {
	/**
	 * 动态获取博客的CSS
	 */
	public function cssAction() {
		$tpl = new Template ( 'blogcss', 'text/css' );
		$tplData = $tpl->getTplData ();
		$blogContext = substr ( $_SERVER ['SCRIPT_NAME'], 0, - 1 - strlen ( MVC_ENTRY_NAME ) );
		$db = $this->getDb ();
		$tablePre = $this->getTablePre ();
		$data = array ();
		$stm = $db->query ( 'SELECT * FROM ' . $tablePre . 'config WHERE t_key IN(\'bg_img\',\'top_img\',\'blogname_color\',\'descr_color\',\'nav_color\',\'nav_active_color\')' );
		while ( $tmp = $stm->fetch () ) {
			$data [$tmp ['t_key']] = $tmp ['t_value'];
		}
		if ($data ['bg_img'] == '')
			$data ['bg_img'] = $blogContext . '/static/img/blog_bg.jpg';
		if ($data ['top_img'] == '')
			$data ['top_img'] = $blogContext . '/static/img/blog_top.jpg';
		foreach ( $data as $key => $value ) {
			$tplData->set ( $key, $value );
		}
		$tpl->display ();
	}
	/**
	 * 产生验证码图片
	 *
	 * @return void
	 */
	public function rcodeAction() {
		session_start ();
		$rcodeObj = new Verify ();
		$_SESSION ['rcode'] = $rcodeObj->createCodeStr ();
		$rcodeObj->entry ();
	}
	/**
	 * 处理用户提交的登录表单
	 *
	 * @return string json格式
	 */
	public function dologinAction() {
		$postData = new DataMap ( $_POST );
		$postUsername = $postData->get ( 'username' );
		$postPass = $postData->get ( 'pass' );
		$postRcode = $postData->get ( 'rcode' );
		session_start ();
		header ( 'Content-Type: application/json' );
		if ($_SESSION ['rcode'] == '') {
			$this->setErrorReturn ( '无法获取到验证码，请确认是否打开了cookies功能' );
			return;
		}
		if (strcasecmp ( $_SESSION ['rcode'], $postRcode ) != 0) {
			$this->setErrorReturn ( '验证码不正确' );
			return;
		}
		$db = $this->getDb ();
		$tablePre = $this->getTablePre ();
		$data = array ();
		$stm = $db->query ( 'SELECT * FROM ' . $tablePre . 'config WHERE t_key IN(\'username\',\'pass\')' );
		while ( $tmp = $stm->fetch () ) {
			$data [$tmp ['t_key']] = $tmp ['t_value'];
		}
		if ($postUsername != $data ['username']) {
			$this->setErrorReturn ( '用户名不正确' );
			return;
		}
		$user = new User ();
		if ($user->encodePass ( $postUsername, $postPass ) != $data ['pass']) {
			$this->setErrorReturn ( '密码错误' );
			return;
		}
		$result = array (
				'success' => true,
				'osid' => $data ['pass'] 
		);
		echo json_encode ( $result );
	}
	private function setErrorReturn($msg) {
		$result = array (
				'success' => false,
				'msg' => $msg 
		);
		echo json_encode ( $result );
	}
	public function blogRightAction(){
		header ( 'Content-Type: application/json' );
		$rightM=new BlogRight($this->getDb(),$this->getTablePre());
		$result=array("success"=>true,"html"=>$rightM->getHtml());
		echo json_encode ( $result );
	}
}