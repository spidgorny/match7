<?php

class FileWalker
{

	protected $redis;

	public function __construct(\Predis\Client $redis)
	{
		$this->redis = $redis;
	}

	public function __invoke()
	{
		$files = [__DIR__ . '/../../source.jpg'];
		foreach ($files as $file) {
			$newFile = $this->denoise($file);
			$meter = $this->recognize($newFile);
			echo $meter, PHP_EOL;
		}
	}

	public function denoise($file)
	{
		// will run the imagemagick
		// return 'output.png';
		return $file;
	}

	public function recognize($file)
	{
		$cmd = [
			'python',
			__DIR__.'/../../match.py',
			$file,
		];
		echo implode(' ', $cmd), PHP_EOL;
		$p = new Symfony\Component\Process\Process($cmd);
		$p->enableOutput();
		$p->run();
		$error = $p->getErrorOutput();
		$meter = $p->getOutput();
		if ($error) {
			throw new Exception($meter . PHP_EOL . $error);
		}
		$lines = explode(PHP_EOL, $meter);
		$lines = array_filter($lines);
		$words = explode(' ', end($lines));
		$meter = end($words);
		return $meter;
	}

	public function set()
	{
		$this->redis->set('foo', 'bar');
		$value = $this->redis->get('foo');

		echo 'value: ', $value;
	}

}
