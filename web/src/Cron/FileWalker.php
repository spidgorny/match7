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
		$files = glob('/var/motion/*.jpg');
//		$files = glob(__DIR__ . '/../../../*.jpg');
		$files = array_map(static function ($file) {
			return realpath($file);
		}, $files);
		llog('jpeg files', count($files));
		[$lastRedisTimestamp, $_] = $this->getLastRedisEntry();

		$firstValid = '25-20200823235154-01.jpg';

		$files = array_filter($files, static function ($file) use ($lastRedisTimestamp) {
			return filemtime($file) >= $lastRedisTimestamp;
		});
		llog('filtered files', count($files));
		usort($files, static function ($file1, $file2) {
			$time1 = filemtime($file1);
			$time2 = filemtime($file2);
			return $time1 <=> $time2;
		});
		llog('sorted files', count($files));
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
		[$_, $lastRedisEntry] = $this->getLastRedisEntry();
		if ($meter && $meter >= $lastRedisEntry) {
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
		$timestamp = filemtime($file);
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
			'python3',
			__DIR__ . '/../../../match.py',
			$file,
		];
		echo implode(' ', $cmd), PHP_EOL;
		$p = new Symfony\Component\Process\Process($cmd);
		$p->enableOutput();
		$p->run();
		$error = $p->getErrorOutput();
		$output = $p->getOutput();
		if ($error) {
			throw new Exception($output . PHP_EOL . $error);
		}
		echo '-------', PHP_EOL;
		echo $output, PHP_EOL;
		echo '-------', PHP_EOL;
		$lines = explode(PHP_EOL, $output);
		$lines = array_filter($lines);
		$words = explode(' ', end($lines));
		$meter = end($words);
		return $meter;
	}

	/**
	 * @return array|null
	 * @throws JsonException
	 */
	public function getLastRedisEntry()
	{
		$today = new DateTime();
		foreach (range(0, 100) as $i) {
			$date = $today->sub(new DateInterval('P' . $i . 'D'));
			$data = $this->redis->get($date->format('Y-m-d'));
			if (!$data) {
				continue;
			}
			$data = json_decode($data, false, 512, JSON_THROW_ON_ERROR);
			if ($data) {
				$keys = array_keys((array)$data);
				return [end($keys), end($data)];
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
