<?php
namespace TJM\WebCrawler\Tests;
use PHPUnit\Framework\TestCase;
use TJM\WebCrawler\Tests\Server;

class ServerTest extends TestCase{
	protected ?Server $server = null;
	public function setUp(): void{
		$this->server = new Server(__DIR__ . '/resources/www');
	}
	public function testRequests(){
		foreach([
			//--root path index
			'/'=> [
				'status'=> 200,
				'type'=> 'text/html',
			],
			//--dir path index
			'/dir'=> [
				'status'=> 200,
				'type'=> 'text/html',
			],
			//--text file
			'/text.txt'=> [
				'status'=> 200,
				'type'=> 'text/plain',
			],
			//--404
			'/404'=> [
				'status'=> 404,
				'type'=> 'text/plain',
			],
			//--dir 404
			'/dir/404'=> [
				'status'=> 404,
				'type'=> 'text/plain',
			],
		] as $path=> $expect){
			$response = $this->server->getResponse($path);
			$this->assertEquals($expect['status'], (int) substr($response, 9, 3));
		}
	}
}
