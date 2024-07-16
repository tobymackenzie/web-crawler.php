<?php
namespace TJM\WebCrawler;

/*
Makes requests as call to CLI command.
*/

class CliClient implements ClientInterface{
	protected string $command;

	public function __construct(string $command){
		$this->command = $command;
	}
	public function request(string $path): Response{
		try{
			$content = shell_exec($this->command . ' ' . escapeshellarg($path));
			if($content){
				if(preg_match(':^HTTP/.+ ([\d]{3})[\w ]+\r\n(.+)\r\n\r\n(.+)$:s', $content, $matches)){
					$code = (int) $matches[1];
					$headers = $matches[2];
					$content = $matches[3];
				}elseif(stripos($content, '<!doctype') !== false){
					$headers = ['Content-Type'=> 'text/html'];
				}
				$response = new Response($content, $code ?? 200, $headers ?? null);
			}
		}catch(Exception $e){}
		if(empty($response)){
			$response = new Response('', 404);
		}
		return $response;
	}
}

