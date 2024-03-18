<?php
namespace TJM\WebCrawler\Tests\Types;
use ReflectionMethod;

class ReflectionTestType extends TestType{
	protected array $expect;
	protected string $method;
	protected string $objectClass;
	public function testReflection(){
		return $this->doReflectionTest($this->objectClass, $this->method, $this->expect);
	}
}
