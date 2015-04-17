<?php

namespace liuguang\blog\controller\ajax;

use liuguang\mvc\DataMap;
use liuguang\mvc\Application;
use liuguang\blog\controller\BaseController;
use liuguang\blog\view\BlogIndex;
use liuguang\blog\view\BlogList;
use liuguang\blog\view\TypesArchs;
use liuguang\blog\view\AdminSets;
use liuguang\blog\view\PostTopic;
use liuguang\blog\view\AdminTags;
use liuguang\blog\view\AdminFiles;
use liuguang\blog\view\AdminEnv;
use liuguang\blog\view\BlogAbout;
use liuguang\blog\view\TopicPage;
use liuguang\blog\view\Liuyan;
/**
 * 处理pushState提交过来的URL
 *
 * @author liuguang
 *        
 */
class PushUrl extends BaseController{
	public function indexAction(){
		$postData=new DataMap($_POST);
		$app=Application::getApp();
		$appConfig=$app->getAppConfig();
		$cKey=$appConfig->get('cKey');
		$aKey=$appConfig->get('aKey');
		$defaultC=$appConfig->get('defaultC');
		$defaultA=$appConfig->get('defaultA');
		$urlHandler=$app->getUrlHandler();
		$urlData=$urlHandler->parseUrl($postData->get('url','/'));
		$cname=$urlData->get($cKey,$defaultC);
		$aname=$urlData->get($aKey,$defaultA);
		$url_key=$cname.'/'.$aname;
		header ( 'Content-Type: application/json' );
		$result=array();
		switch ($url_key) {
			case 'Index/index':
				$vModel=new BlogIndex($this->getDb(), $this->getTablePre());
				$result['title']=$vModel->getTitle();
				$result['blog_center']=$vModel->getHtml();
			break;
			case 'web/BlogList/index':
				$vModel=new BlogList($this->getDb(), $this->getTablePre());
				$page=(int)$urlData->get('page',1);
				$result['title']=$vModel->getTitle($page);
				$result['blog_center']=$vModel->getHtml($page);
				break;
			case 'web/BlogTypes/index':
				$vModel=new TypesArchs($this->getDb(), $this->getTablePre(),true);
				$page=(int)$urlData->get('page',1);
				$result['title']=$vModel->getTitle($page);
				$result['blog_center']=$vModel->getHtml($page);
				break;
			case 'web/BlogArchs/index':
				$vModel=new TypesArchs($this->getDb(), $this->getTablePre(),false);
				$page=(int)$urlData->get('page',1);
				$result['title']=$vModel->getTitle($page);
				$result['blog_center']=$vModel->getHtml($page);
				break;
			case 'web/BlogAdmin/sets':
				$vModel=new AdminSets($this->getDb(), $this->getTablePre());
				$result['title']=$vModel->getTitle();
				$result['blog_center']=$vModel->getHtml();
			break;
			case 'web/BlogAdmin/postTopic':
				$vModel=new PostTopic($this->getDb(), $this->getTablePre());
				$result['title']=$vModel->getTitle();
				$result['blog_center']=$vModel->getHtml();
			break;
			case 'web/BlogAdmin/types':
				$vModel=new AdminTags($this->getDb(), $this->getTablePre(),false);
				$result['title']=$vModel->getTitle();
				$result['blog_center']=$vModel->getHtml();
			break;
			case 'web/BlogAdmin/tags':
				$vModel=new AdminTags($this->getDb(), $this->getTablePre(),true);
				$result['title']=$vModel->getTitle();
				$result['blog_center']=$vModel->getHtml();
			break;
			case 'web/BlogAdmin/files':
				$vModel=new AdminFiles($this->getDb(), $this->getTablePre(),$this->getFs());
				$page=(int)$urlData->get('page',1);
				$result['title']=$vModel->getTitle($page);
				$result['blog_center']=$vModel->getHtml($page);
				break;
			case 'web/BlogAdmin/env':
				$vModel=new AdminEnv($this->getDb(), $this->getTablePre(),$this->getFs());
				$result['title']=$vModel->getTitle();
				$result['blog_center']=$vModel->getHtml();
				break;
			case 'web/BlogAbout/index':
				$vModel=new BlogAbout($this->getDb(), $this->getTablePre());
				$result['title']=$vModel->getTitle();
				$result['blog_center']=$vModel->getHtml();
				break;
			case 'web/Topic/index':
				$t_id=(int)$urlData->get('t_id',0);
				$vModel=new TopicPage($this->getDb(), $this->getTablePre(),$t_id);
				$result['blog_center']=$vModel->getHtml();
				$result['title']=$vModel->getTitle();
				break;
			case 'web/BlogLiuyan/index':
				$page=(int)$urlData->get('page',0);
				$vModel=new Liuyan($this->getDb(), $this->getTablePre());
				$result['blog_center']=$vModel->getHtml($page);
				$result['title']=$vModel->getTitle($page);
				break;
			default:
				$result['title']='未知页面';
				$result['blog_center']='当前页面还不支持pushState';
		}
		echo json_encode($result);
	}
	/**
	 * 检测管理员身份
	 * 
	 * @todo
	 */
	public function checkAdmin(){
		
	}
}