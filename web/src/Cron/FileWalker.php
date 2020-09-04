<?php

class FileWalker
{

	protected $redis;
	protected $storage = '/var/motion/';

	public function __construct(\Predis\Client $redis = null)
	{
		$this->redis = $redis;
	}

	public function __invoke()
	{
//		$files = [__DIR__ . '/../../../source.jpg'];
		$files = glob($this->storage . '*.jpg');
//		$files = glob(__DIR__ . '/../../../*.jpg');
		$files = array_map(static function ($file) {
			return realpath($file);
		}, $files);
		llog('jpeg files', count($files));
		[$lastRedisTimestamp, $_] = $this->getLastRedisEntry();

		$firstValidTime = null;
		$firstValid = trim(@file_get_contents('FileWalkerState.txt'));
		if ($firstValid) {
			$firstValidTime = filemtime($this->storage . $firstValid);
		}

		$files = array_filter($files, static function ($file) use ($firstValidTime, $lastRedisTimestamp) {
			if ($firstValidTime && filemtime($file) < $firstValidTime) {
				return false;
			}
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
			file_put_contents('FileWalkerState.txt', basename($file));
		}
	}

	public function processFile($file)
	{
		echo 'Start: ', $file, PHP_EOL;
		$timestamp = $this->getTimestamp($file);
		$newFile = $this->denoise($file);
		echo 'Denoise: ', $newFile, PHP_EOL;
		$meter = $this->recognize($newFile);
		echo $timestamp->format('Y-m-d H:i:s'), ': ', $meter, PHP_EOL;
		[$_, $lastRedisEntry] = $this->getLastRedisEntry();
//		if ($meter && $meter >= $lastRedisEntry) {
		if ($meter) {
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
		$tz = new DateTimeZone('Europe/Berlin');
		$date = new DateTime('@' . $timestamp);
		$date->setTimezone($tz);
		return $date;
	}

	public function denoise($file)
	{
		$onlyName = pathinfo($file, PATHINFO_FILENAME);
		$outputFile = '/var/motion/' . $onlyName . '.png';
		if (is_file($outputFile)) {
			return $outputFile;
		}
		$cmd = 'convert ' . $file . ' -rotate 2 -auto-level -auto-gamma -noise 5 -median 5 -unsharp 5 -normalize ' . $outputFile;
		$content[] = $cmd;
		exec($cmd, $output);
		return $outputFile;
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
