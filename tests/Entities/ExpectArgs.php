<?php
namespace TJM\WebCrawler\Tests\Entities;

class ExpectArgs{
	protected array $args = [];
	protected $expect;
	public function __construct(array $args, $expect){
		$this->args = $args;
		$this->expect = $expect;
	}
	public function getArgs(){
		return $this->args;
	}
	public function getExpect(){
		return $this->expect;
	}
}
