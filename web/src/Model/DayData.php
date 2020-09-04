<?php

class DayData implements ArrayAccess
{

	public $day;
	public $data;
	/** @var \Predis\Client */
	protected $redis;

	public function __construct($day, $redis)
	{
		$this->day = $day;
		$this->redis = $redis;
		$this->read();
	}

	public function read()
	{
		$json = $this->redis->get($this->day);
		$this->data = json_decode($json, true);
	}

	public function save()
	{
		$dataWithout0 = array_filter($this->data);
		$json = json_encode($dataWithout0, JSON_THROW_ON_ERROR);
		echo $json, PHP_EOL;
		$this->redis->set($this->day, $json);
	}

	public function offsetExists($offset)
	{
		return isset($this->data[$offset]);
	}

	public function offsetGet($offset)
	{
		return $this->data[$offset];
	}

	public function offsetSet($offset, $value)
	{
		$this->data[$offset] = $value;
	}

	public function offsetUnset($offset)
	{
		unset($this->data[$offset]);
	}
}
