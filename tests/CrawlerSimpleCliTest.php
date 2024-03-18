<?php
namespace TJM\WebCrawler\Tests;
use TJM\WebCrawler\Tests\Types\CrawlerTestType;

class CrawlerSimpleCliTest extends CrawlerTestType{
	protected array $crawlerOpts = [
		'cli'=> 'php ' . __DIR__ . '/resources/www/index.php',
	];
	protected array $crawlPaths = ['/'];
	protected array $expect = [
		'/'=> [
			'contentType'=> 'text/html',
			'status'=> 200,
		],
		'/404'=> [
			'contentType'=> 'text/html',
			'status'=> 404,
		],
		'/dir/text.txt'=> [
			'contentType'=> 'text/html',
			'status'=> 404,
		],
		'/dir'=> [
			'contentType'=> 'text/html',
			'status'=> 200,
		],
		'/dir/sub'=> [
			'contentType'=> 'text/html',
			'status'=> 200,
		],
		'/file'=> [
			'contentType'=> 'text/html',
			'status'=> 200,
		],
		'/text.txt'=> [
			'contentType'=> 'text/plain',
			'status'=> 200,
		],
	];
}
