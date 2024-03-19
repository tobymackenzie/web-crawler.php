<?php
namespace TJM\WebCrawler\Tests;
use TJM\Dev\Test\ExpectArgs;
use TJM\Dev\Test\TestCase;
use TJM\WebCrawler\Crawler;

class CrawlerTest extends TestCase{
	public function testIsInternalPath(){
		return $this->doReflectionMethodTest(Crawler::class, 'isInternalPath', [
			'/'=> true,
			'/foo/bar'=> true,
			'bar'=> true,
			'bar?foo=asdf'=> true,
			'foo/bar?foo=asdf'=> true,
			'../foo/bar?foo=asdf'=> true,
			'//example.com/asdf'=> true,
			'http://example.com/foo?bar'=> true,
			'http://macn.me/foo?bar'=> false,
		], [['host'=> 'example.com']]);
	}
	public function testIsPathRelativePath(){
		return $this->doReflectionMethodTest(Crawler::class, 'isPathRelativePath', [
			'/'=> false,
			'/foo/bar'=> false,
			'bar'=> true,
			'bar?foo=asdf'=> true,
			'foo/bar?foo=asdf'=> true,
			'../bar?foo=asdf'=> true,
			'//example.com/asdf'=> false,
			'http://example.com/foo?bar'=> false,
			'http://macn.me/foo?bar'=> false,
		]);
	}
	public function testNormalizePaths(){
		return $this->doReflectionMethodTest(Crawler::class, 'normalizePath', [
			''=> '/',
			'/'=> '/',
			'/foo.html'=> '/foo.html',
			'/foo/bar'=> '/foo/bar',
			'/foo/bar.html'=> '/foo/bar.html',
			'/foo/bar?biz'=> '/foo/bar?biz',
			'/dir/foo'=> new ExpectArgs(
				['foo', 'dir'],
				'/dir/foo'
			),
		]);
	}
}
