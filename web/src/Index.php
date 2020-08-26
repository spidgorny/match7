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
		parent::__construct($config);
	}

}
