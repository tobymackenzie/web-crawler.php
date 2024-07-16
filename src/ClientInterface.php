<?php
namespace TJM\WebCrawler;

interface ClientInterface{
	public function request(string $path): Response;
}

