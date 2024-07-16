<?php
namespace TJM\WebCrawler\Tests\Types;
use PHPUnit\Framework\TestCase;
use TJM\WebCrawler\Crawler;

class CrawlerTestType extends TestCase{
	protected ?Crawler $crawler;
	protected array $crawlPaths = [];
	protected array $crawlerOpts = [];
	protected array $expect = [];
	protected int $expectCount = 0;
	public function setUp(): void{
		if(empty($this->crawler)){
			$this->crawler = new Crawler($this->getCrawlerOpts());
			if($this->crawlPaths){
				$this->crawler->crawl($this->crawlPaths);
			}
		}
	}
	protected function getCrawlerOpts(): array{
		return $this->crawlerOpts;
	}
	public function testExpectedVisitedCount(){
		$this->assertEquals(count($this->expect), count($this->crawler->getVisitedPaths()));
	}
	protected function doTestForField(string $field, string $getter){
		foreach($this->expect as $path=> $expect){
			$expect = $this->expect[$path][$field] ?? null;
			if(isset($expect)){
				$this->assertEquals($expect, $this->crawler->getResponse($path)->$getter(), "Path {$path} should have {$field} {$expect}");
			}
		}
	}
	public function testExpectedResponseContentType(){
		$this->doTestForField('contentType', 'getContentType');
	}
	public function testExpectedResponseStatuses(){
		$this->doTestForField('status', 'getStatusCode');
	}
}
