<?php
use TJM\WebCrawler\Tests\Server;
require_once(__DIR__ . '/../../Server.php');

(new Server(__DIR__))($argv[1] ?? '/');
