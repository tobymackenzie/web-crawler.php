<?php
namespace TJM\WebCrawler;
use Closure;

/*
Makes requests as call to closure function.  Must return a Response object.
*/

class ClosureClient implements ClientInterface{
	protected Closure $closure;

	public function __construct(Closure $closure){
		$this->closure = $closure;
	}
	public function request(string $path): Response{
		return call_user_func($this->closure, $path);
	}
}

