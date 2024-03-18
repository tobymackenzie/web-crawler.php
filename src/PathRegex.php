<?php
namespace TJM\WebCrawler;

class PathRegex implements PathMatchInterface{
	//--regex: PCRE regex string to test path against
	protected string $regex;

	public function __construct(string $regex){
		$this->regex = $regex;
	}
	public function matches(string $path){
		return (bool) preg_match($this->regex, $path);
	}
}
