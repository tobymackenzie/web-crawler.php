<?php
namespace TJM\WebCrawler;
use DOMDocument;
use Exception;

class Crawler{
	//--cli: cli path.  If set, will call cli path like `{$path} {$entry} {$query}`
	protected ?string $cli = null;
	//--delay: delay between requests.  By default, will add 1 second delay for HTTP requests, none for CLI.
	protected ?int $delay = null;
	//--delayUnit: seconds or microseconds
	const DELAY_SECONDS = 0;
	const DELAY_MICROSECONDS = 1;
	protected int $delayUnit = self::DELAY_SECONDS;
	//--host: domain of set, needed to include absolute URLs in crawl, and to build paths for http crawls
	protected ?string $host = null;
	//--ignore: paths to ignore on both get and write. Can be string (with leading "/") for exact match, or a PathRegex / PathMatchInterface object to match against
	protected array $ignore = [];
	//--scheme: http or https
	protected ?string $scheme = null;
	//--store: whether to store resulting response
	protected bool $store = true;

	//==paths
	//--unvisited: paths to visit during crawl
	protected array $unvisited = [];
	//--visited: paths already visited as keys
	protected array $visited = [];


	public function __construct(array $conf = null){
		if($conf){
			foreach($conf as $key=> $value){
				$this->$key = $value;
			}
		}
	}

	//==crawl
	public function crawl(array $paths = null, bool $follow = true, $callback = null){
		if($paths){
			foreach($paths as $path){
				$this->crawlPath($path, $callback);
				$this->delay();
			}
		}
		if($follow){
			$this->crawlUnvisited($callback);
		}
	}
	protected function crawlPath(string $path, callable $callback = null){
		//--create response
		$path = $this->normalizePath($path);
		$response = $this->makeRequest($path);
		if(!empty($response)){
			//-!! should have option to store or not store 3xx, etc
			//-!! maybe should have separate arrays for different codes?
			$this->visited[$path] = $this->store ? $response : $response->getStatusCode();
			if($callback){
				$callback($path, $response);
			}
		}

		//--crawl for more links
		$this->crawlResponseContent($response, $path);

		//--remove from unvisited
		$key = array_search($path, $this->unvisited, true);
		if($key !== false){
			unset($this->unvisited[$key]);
		}

		//--do callback
		if(!empty($response) && $callback){
			$callback($path, $response);
		}

		return $response;
	}
	protected function crawlResponseContent(Response $response, string $path){
		$result = false;
		$content = $response->getContent();
		if($content && ($response->getContentType() === 'text/html' || strpos($content, 'href=') !== false)){
			//--disable malformed dom errors
			libxml_use_internal_errors(true);
			//--load content as html
			$doc = new DOMDocument();
			$doc->loadHTML($content);
			libxml_clear_errors();
			//--loop through a
			foreach($doc->getElementsByTagName('a') as $el){
				if($el->hasAttribute('href')){
					$href = $el->getAttribute('href');
					$this->addUnvisitedPath($href, $path);
				}
			}
		}
		return $result;
	}
	protected function crawlUnvisited($callback = null){
		while(count($this->unvisited)){
			$this->crawlPath(array_pop($this->unvisited));
			$this->delay();
		}
	}
	protected function delay(){
		//--delay http requests by a second by default to reduce server load and prevent
		if($this->delay === null){
			$this->delay = empty($this->cli) ? 1 : false;
		}
		if($this->delay){
			if($this->delayUnit === self::DELAY_SECONDS){
				sleep($this->delay);
			}else{
				usleep($this->delay);
			}
		}
	}

	//==http
	protected function makeRequest($path){
		if(!isset($this->visited[$path]) && !$this->ignorePath($path)){
			if($this->cli){
				try{
					$content = shell_exec($this->cli . ' ' . escapeshellarg($path));
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
			}else{
				$url = $this->scheme . '://' . $this->host . $path;
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
				$response = new Response($content, $data['http_code'], $headers);
			}
		}else{
			$response = false;
		}
		return $response;
	}

	//==paths
	protected function addUnvisitedPath(string $path, string $parentPath){
		if(substr($path, 0, 1) === '#'){
			return false;
		}
		if($this->isInternalPath($path)){
			$path = $this->normalizePath($path, $parentPath);
			if(!isset($this->visited[$path]) && !in_array($path, $this->visited)){
				$this->unvisited[] = $path;
				return true;
			}
		}
		return false;
	}
	public function getResponse($path){
		if($this->store){
			if(!isset($this->visited[$path])){
				$this->crawlPath($path);
			}
			return $this->visited[$path];
		}else{
			return $this->crawlPath($path);
		}
	}
	public function getVisitedPaths(){
		return array_keys($this->visited);
	}
	protected function ignorePath(string $path){
		foreach($this->ignore as $check){
			if($check instanceof PathMatchInterface){
				if($check->matches($path)){
					return true;
				}
			}elseif($check === $path){
				return true;
			}
		}
		return false;
	}
	protected function isInternalPath(string $path){
		if(substr($path, 0, 1) === '/' && substr($path, 1, 1) !== '/'){
			return true;
		}
		$url = parse_url($path);
		if(empty($url['host']) || $url['host'] === $this->host){
			return true;
		}
		return false;
	}
	protected function isPathRelativePath(string $path){
		//--is relative if we have no host and don't start with '/'
		$url = parse_url($path);
		$char1 = substr($path, 0, 1);
		return !isset($url['host']) && isset($url['path']) && $char1 !== '/' && $char1 !== '#';
	}
	protected function normalizePath(string $path, string $parentPath = null){
		//-! should resolve ".." parts
		if($this->isPathRelativePath($path)){
			$path = $parentPath . '/' . $path;
		}
		$url = parse_url($path);
		$normalized = $url['path'] ?? '/';
		if(substr($normalized, 0, 1) !== '/'){
			$normalized = '/' . $path;
		}
		if(!empty($url['query'])){
			$normalized .= '?' . $url['query'];
		}
		if(empty($this->host) && isset($url['host'])){
			$this->host = $url['host'];
			if(isset($url['port'])){
				$this->host .= ':' . $url['port'];
			}
		}
		if(empty($this->scheme) && isset($url['scheme'])){
			$this->scheme = $url['scheme'];
		}
		return $normalized;
	}
}

