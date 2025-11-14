<?php
namespace TJM\WebCrawler\Tests;
use TJM\WebCrawler\Tests\Types\CrawlerTestType;

//-! would prefer to do something locally (eg `php -S`)
class CrawlerHttpHostInPathsTest extends CrawlerTestType{
	protected array $crawlerOpts = [];
	protected array $crawlPaths = ['https://macn.me'];
	protected array $expect = [
		'/'=> [
			'contentType'=> 'text/html',
			'status'=> 200,
		],
	];
}
