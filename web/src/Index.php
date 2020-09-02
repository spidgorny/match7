<?php

class Index extends IndexBase
{

	public function __construct(ConfigInterface $config)
	{
		$this->csp['script-src'][] = 'cdn.jsdelivr.net';
		$this->csp['default-src'][] = 'stackpath.bootstrapcdn.com';
		$this->csp['script-src'][] = 'stackpath.bootstrapcdn.com';
		$this->csp['script-src'][] = 'code.jquery.com';
		$this->csp['img-src'][] = 'cdn2.iconfinder.com';
		$this->csp['img-src'][] = '192.168.1.207';
		$this->csp['img-src'][] = '192.168.1.207:8080';
		$this->csp['img-src'][] = '192.168.1.207:8081';
		$this->csp['img-src'][] = 'localhost:8081';
		parent::__construct($config);
	}

}
