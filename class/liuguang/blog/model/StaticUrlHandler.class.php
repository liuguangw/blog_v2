<?php

namespace liuguang\blog\model;

use liuguang\mvc\UrlHandler;
use liuguang\mvc\DataMap;
/**
 *
 * @author ac er
 *        
 */
class StaticUrlHandler implements UrlHandler {

	private $cKey;
	private $aKey;
	private $defaultC;
	private $defaultA;
	private $urlMap;
	private $urlData;
	public function __construct(DataMap $config) {
		$this->cKey=$config->get('cKey');
		$this->aKey=$config->get('aKey');
		$this->defaultC=$config->get('defaultC');
		$this->defaultA=$config->get('defaultA');
		$this->urlMap=$config->get('urlMap',array());
		$this->urlData=$this->parseUrl($_SERVER['REQUEST_URI']);
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \liuguang\mvc\UrlHandler::parseUrl()
	 *
	 */
	public function parseUrl($url) {
		$tmp=array();
		$urlData=new DataMap($tmp);
        $url=parse_url($url,PHP_URL_PATH);
		if(($url=='/')||($url=='')){
			$urlData->set($this->cKey, $this->defaultC);
			$urlData->set($this->aKey, $this->defaultA);
			return $urlData;
		}
		if($url=='/Index/index'){
			$urlData->set($this->cKey, 'Index');
			$urlData->set($this->aKey, 'index');
			return $urlData;
		}
		elseif($url=='/Err404/index'){
			$urlData->set($this->cKey, 'Err404');
			$urlData->set($this->aKey, 'index');
			return $urlData;
		}
		$url=trim($url," \t\n\r\0\x0B/");
		$data=explode('/', $url);
		$dataLength=count($data);
		if($dataLength<3){
			$urlData->set($this->cKey, 'Err404');
			$urlData->set($this->aKey, 'index');
			return $urlData;
		}
		$urlData->set($this->cKey, $data[0].'/'.$data[1]);
		$urlData->set($this->aKey, $data[2]);
		$mapKey=$data[0].'/'.$data[1].'/'.$data[2];
		if(($dataLength>3)&&isset($this->urlMap[$mapKey])){
			for($i=3;$i<$dataLength;$i++){
				$urlData->set($this->urlMap[$mapKey][$i-3], $data[$i]);
			}
		}
		return $urlData;
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \liuguang\mvc\UrlHandler::getAname()
	 *
	 */
	public function getAname() {
		return $this->urlData->get($this->aKey,$this->defaultA);
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \liuguang\mvc\UrlHandler::createUrl()
	 *
	 */
	public function createUrl($cname, $aname, array $data, $xmlSafe = true) {
		$url='/'.$cname.'/'.$aname;
		if(!empty($data))
			$url.=('/'.implode('/',$data));
		return $url;
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \liuguang\mvc\UrlHandler::getUrlData()
	 *
	 */
	public function getUrlData() {
		return $this->urlData;
	}
	
	/**
	 * (non-PHPdoc)
	 *
	 * @see \liuguang\mvc\UrlHandler::getCname()
	 *
	 */
	public function getCname() {
		return $this->urlData->get($this->cKey,$this->defaultC);
	}
}