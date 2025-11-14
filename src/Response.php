<?php
namespace TJM\WebCrawler;

class Response{
	protected string $content;
	protected ?string $contentType = null;
	public array $headers = [];
	protected int $statusCode;
	public function __construct(?string $content = null, int $status = 200, $headers = null){
		$this->content = $content;
		$this->statusCode = $status;
		if(is_array($headers)){
			$this->headers = $headers;
		}elseif(!empty($headers)){
			$this->headers = explode("\r\n", trim($headers));
		}
	}
	public function getContent(){
		return $this->content;
	}
	public function getContentType(){
		if(empty($this->contentType)){
			if($this->headers){
				if(isset($this->headers['Content-Type'])){
					$this->contentType = $this->headers['Content-Type'];
				}else{
					foreach($this->headers as $key=> $header){
						if(is_numeric($key) && preg_match('/^Content-Type:[\s]+(.*)$/i', $header, $matches)){
							$this->contentType = $matches[1];
							break;
						}
					}
				}
			}
			if(empty($this->contentType)){
				if(stripos('<!doctype', $this->content) !== false){
					$this->contentType = 'text/html';
				}else{
					$this->contentType = 'text/plain';
				}
			}
		}
		$contentType = $this->contentType;
		if(strpos($contentType, ';') !== false){
			$contentType = explode(';', $contentType)[0];
		}
		return $contentType;
	}
	public function getStatusCode(){
		return $this->statusCode;
	}
}
