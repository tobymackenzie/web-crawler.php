<?php
namespace TJM\WebCrawler;

interface PathMatchInterface{
	public function matches(string $path);
}

