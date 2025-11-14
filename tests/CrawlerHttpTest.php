<?php
namespace TJM\WebCrawler\Tests;
use TJM\WebCrawler\Tests\Types\CrawlerTestType;

//-! would prefer to do something locally (eg `php -S`)
class CrawlerHttpTest extends CrawlerTestType{
	protected array $crawlerOpts = [
		'host'=> 'macn.me',
		'scheme'=> 'https',
	];
	protected array $crawlPaths = ['/'];
	protected array $expect = [
		'/'=> [
			'contentType'=> 'text/html',
			'status'=> 200,
		],
	];
}
