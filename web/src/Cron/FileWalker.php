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
//		$files = [__DIR__ . '/../../../source.jpg'];
//		$files = scandir('/var/motion/');
		$files = glob(__DIR__ . '/../../../*.jpg');
		$files = array_map(static function ($file) {
			return realpath($file);
		}, $files);
		llog($files);
		exit;
		foreach ($files as $file) {
			$this->processFile($file);
		}
	}

	public function processFile($file)
	{
		$timestamp = $this->getTimestamp($file);
		$newFile = $this->denoise($file);
		$meter = $this->recognize($newFile);
		echo $timestamp->format('Y-m-d H:i:s'), ': ', $meter, PHP_EOL;
		$lastRedisEntry = $this->getLastRedisEntry();
		if ($meter >= $lastRedisEntry) {
			$this->updateDay($timestamp, $meter);
			echo 'Updated', PHP_EOL;
		} else {
			echo 'Skipping, last redis entry is ', $lastRedisEntry, PHP_EOL;
		}
	}

	/**
	 * @param string $file
	 * @return DateTime
	 * @throws Exception
	 */
	public function getTimestamp($file)
	{
		$timestamp = filectime($file);
		return new DateTime('@' . $timestamp);
	}

	public function denoise($file)
	{
		// will run the imagemagick
		// return 'output.png';
		return $file;
	}

	/**
	 * @param $file
	 * @return mixed|string
	 * @throws Exception
	 */
	public function recognize($file)
	{
		$cmd = [
			'python',
			__DIR__ . '/../../../match.py',
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

	public function getLastRedisEntry()
	{
		$today = new DateTime();
		foreach (range(0, 100) as $i) {
			$date = $today->sub(new DateInterval('P' . $i . 'D'));
			$data = $this->redis->get($date->format('Y-m-d'));
			$data = json_decode($data, false);
			if ($data) {
				return end($data);
			}
		}
		return null;
	}

	public function updateDay(DateTime $timestamp, $meter)
	{
		$date = $timestamp->format('Y-m-d');
		llog($date);
		$day = new DayData($date, $this->redis);
		$time = $timestamp->format('H:i:s');
		llog($time);
		$day[$time] = $meter;
		$day->save();
	}

}
