<?php

namespace liuguang\blog\view;
use liuguang\mvc\FsInter;
/**
 * 后台显示博客环境信息
 *
 * @author liuguang
 *        
 */
class AdminEnv {

	private $db;
	private $tablePre;
	private $fs;
	public function __construct(\PDO $db, $tablePre, FsInter $fs) {
		$this->db = $db;
		$this->tablePre = $tablePre;
		$this->fs = $fs;
	}
	public function getHtml() {
		$html = '<div class="panel panel-default">
  <div class="panel-heading">博客运行环境信息</div>
  <div class="panel-body">
    <form class="form-horizontal">';
		$list=array(0=>array('php版本',PHP_VERSION));
		$list[1]=array('服务器软件','未知');
		if(isset($_SERVER['SERVER_SOFTWARE']))
			$list[1][1]=$_SERVER['SERVER_SOFTWARE'];
		$list[2]=array('域名',$_SERVER['HTTP_HOST']);
		$list[3]=array('文件上传大小限制','无法获取');
		if(function_exists('ini_get'))
			$list[3][1]=ini_get('upload_max_filesize');
		$list[4]=array('文件存储驱动',$this->fs->getDriverInfo());
		$list[5]=array('已加载的扩展',implode(' , ', get_loaded_extensions()));
		foreach ($list as $listInfo){
			$html.=('<div class="form-group">
<label class="col-sm-3 control-label">'.$listInfo[0].'</label>
<div class="col-sm-8">
    <p class="form-control-static">'.$listInfo[1].'</p>
</div></div>');
		}
		$html .= ('</form></div></div>');
		return $html;
	}
	public function getTitle() {
		$stm = $this->db->query ( 'SELECT t_value FROM ' . $this->tablePre . 'config WHERE t_key=\'blogname\'' );
		$rst = $stm->fetch ();
		return $rst ['t_value'] . '-博客环境信息';
	}
}