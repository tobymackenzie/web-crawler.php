<?php
namespace TJM\WebCrawler\Tests;
use DateTime;
use TJM\Dev\Test\ExpectArgs;
use TJM\Dev\Test\TestCase;
use TJM\WebCrawler\ClosureClient;
use TJM\WebCrawler\CliClient;
use TJM\WebCrawler\HttpClient;
use TJM\WebCrawler\Response;

class ClientTest extends TestCase{
	public function testClosureClient(){
		$date = new DateTime();
		$message = 'Hello world!';
		$client = new ClosureClient(function($path) use($date, $message){
			return new Response($path . ': ' . $message, 200, [
				'DT'=> $date->format('Ymd His'),
			]);
		});
		$response = $client->request('/foo');
		$this->assertEquals('/foo: ' . $message, $response->getContent());
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals($date->format('Ymd His'), $response->headers['DT'] ?? null);
		$this->assertEquals('text/plain', $response->getContentType());
	}
	public function testCliClient(){
		$client = new CliClient('php ' . __DIR__ . '/resources/www/index.php');
		$response = $client->request('/text.txt');
		$this->assertEquals("Hello world\n\n\n", $response->getContent());
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals('text/plain', $response->getContentType());
	}
	public function testHttpClient(){
		$client = new HttpClient('example.com', 'http');
		$response = $client->request('/');
		$this->assertStringContainsString('</html>', $response->getContent());
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertStringContainsString('text/html', $response->getContentType());
	}
}
