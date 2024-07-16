<?php
namespace TJM\WebCrawler;

/*
Makes requests over http/s using CURL.
*/

class HttpClient implements ClientInterface{
	protected ?string $host = null;
	protected string $scheme = 'http';

	public function __construct($host = null, $scheme = null){
		if($host){
			$this->host = $host;
		}
		if($scheme){
			$this->scheme = $scheme;
		}
	}
	public function request(string $path): Response{
		if(preg_match('!^[\w\+]+://!', $path, $matches)){
			$url = $path;
		}else{
			$url = $this->scheme . '://' . $this->host . $path;
		}
		$ch = curl_init($url);
		curl_setopt_array($ch, [
			CURLOPT_CONNECTTIMEOUT=> 0,
			CURLOPT_HEADER=> true,
			CURLOPT_RETURNTRANSFER=> true,
			CURLOPT_USERAGENT=> 'TJMWebCrawlerBot',
		]);
		$response = curl_exec($ch);
		$data = curl_getinfo($ch);
		$headers = substr($response, 0, $data['header_size']);
		$content = substr($response, $data['header_size']);
		return new Response($content, $data['http_code'], $headers);
	}
}

