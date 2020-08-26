<?php

class Overview extends Controller
{

	public $date;
	protected $redis;

	public function __construct(\Predis\Client $redis)
	{
		parent::__construct();
		$this->redis = $redis;
		$this->date = date('Y-m-d');
	}

	public function indexAction()
	{
		$content[] = $this->html->h1($this->date, ['class' => 'title']);
		$yesterday = date('Y-m-d', strtotime('-1 day', strtotime($this->date)));
		$yesterdayJson = $this->redis->get($yesterday);
		$yesterdayData = json_decode($yesterdayJson, false);
		$json = $this->redis->get($this->date);
//		$content[] = $this->html->div($json);
		$data = json_decode($json, false);
		$diffYesterday = $this->getDiff($yesterdayData);
		$diff = $this->getDiff($data);
		$this->index->addJS('https://cdn.jsdelivr.net/npm/chart.js@2.8.0');
		$this->index->addJS('js/chart.js');
		$content[] = $this->getActionButton('Save Test', 'saveTest');

		$yesterdayConsumption = 0;
		if ($yesterdayData) {
			$yesterdayConsumption = end($yesterdayData) - first($yesterdayData);
		}

		$view = View::getInstance(__DIR__ . '/../template/Overview.phtml', $this);
		return $view->render([
			'content' => $this->s($content),
			'dataTable' => getDebug($diff),
			'jsonData' => json_encode($diff),
			'jsonLabels' => json_encode(array_keys($data)),
			'meter' => number_format(end($data), 2, '.', ' '),
			'current' => number_format(end($diff), 2),
			'currentEUR' => number_format(end($diff) * 0.3, 2),
			'totalYesterday' => number_format($yesterdayConsumption, 2),
			'totalYesterdayEUR' => number_format($yesterdayConsumption * 0.3, 2),
			'total' => number_format(end($data) - first($data), 2),
			'totalEUR' => number_format((end($data) - first($data)) * 0.3, 2),
			'jsonYesterday' => json_encode($diffYesterday),
		]);
	}

	public function getDiff($data)
	{
		$diff = [];
		$prev = first($data);
		foreach ($data as $el) {
			$diff[] = $el - $prev;
			$prev = $el;
		}
		return $diff;
	}

	public function saveTestAction()
	{
		$dictionary = [];
		foreach (range(0, 24) as $a) {
			$dictionary[] = 71458 + $a + mt_rand(-4, 4);
		}
		$this->redis->set($this->date, json_encode($dictionary));
		$content[] = 'ok';
		return $content;
	}

}
