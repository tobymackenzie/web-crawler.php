<?php
namespace TJM\WebCrawler\Tests\Entities;

class Expect{
	protected $expect;
	protected $value;
	public function __construct($value, $expect){
		$this->expect = $expect;
		$this->value = $value;
	}
	public function getExpect(){
		return $this->expect;
	}
	public function getValue(){
		return $this->value;
	}
}
