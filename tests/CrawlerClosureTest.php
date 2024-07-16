<?php
namespace TJM\WebCrawler\Tests;
use TJM\WebCrawler\ClosureClient;
use TJM\WebCrawler\Response;
use TJM\WebCrawler\Tests\Server;

class CrawlerClosureTest extends CrawlerSimpleCliTest{
	protected ?Server $server = null;
	public function getCrawlerOpts(): array{
		$server = $this->server;
		return [
			'client'=> new ClosureClient(function($path) use($server){
				$content = $server->getResponse($path);
				if($content){
					if(preg_match(':^HTTP/.+ ([\d]{3})[\w ]+\r\n(.+)\r\n\r\n(.+)$:s', $content, $matches)){
						$code = (int) $matches[1];
						$headers = $matches[2];
						$content = $matches[3];
					}elseif(stripos($content, '<!doctype') !== false){
						$headers = ['Content-Type'=> 'text/html'];
					}
					return new Response($content, $code ?? 200, $headers);
				}else{
					return new Response('', 404);
				}
			}),
		];
	}
	public function setUp(): void{
		$this->server = new Server(__DIR__ . '/resources/www');
		parent::setUp();
	}
}
