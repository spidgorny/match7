<?php

class Monthly extends Controller
{

	public $date;
	public $month;
	protected $redis;

	public function __construct(\Predis\Client $redis)
	{
		parent::__construct();
		$this->redis = $redis;
		$getDate = $this->request->getDateFromY_M_D('date');
		$this->date = $getDate ? date('Y-m-d', $getDate) : date('Y-m-d');
		$this->month = substr($this->date, 0, 7);
	}

	public function indexAction()
	{
		$content[] = $this->html->h1($this->month, ['class' => 'title']);
		$lastMonth = date('Y-m', strtotime('-1 month', strtotime($this->date)));
		$lastMonth = $this->getMonth($lastMonth);
		$data = $this->getMonth($this->month);
		$this->index->addJS('https://cdn.jsdelivr.net/npm/chart.js@2.8.0');
		$this->index->addJS('js/chart.js');

		$totalThisMonth = array_sum($data);
		$totalLastMonth = array_sum($lastMonth);

		$view = View::getInstance(__DIR__ . '/../template/Overview.phtml', $this);
		return $view->render([
			'content' => $this->s($content),
			'dataTable' => getDebug($data),
			'jsonData' => json_encode(array_values($data)),
			'jsonLabels' => json_encode(
				($data ? array_keys($data) : []) +
				($lastMonth ? array_keys($lastMonth) : [])),
			'meter' => number_format($data ? end($data) : 0, 2, '.', ' '),
			'current' => '-',
			'currentEUR' => '-',
			'totalYesterday' => number_format($totalLastMonth, 2),
			'totalYesterdayEUR' => number_format($totalLastMonth * 0.3, 2),
			'total' => number_format($totalThisMonth, 2),
			'totalEUR' => number_format($totalThisMonth * 0.3, 2),
			'jsonYesterday' => json_encode(array_values($lastMonth)),
			'time' => 'month',
			'time_1' => 'last month',
		]);
	}

	public function getMonth($month)
	{
		$monthData = [];
		foreach (range(1, date('t', strtotime($this->date))) as $day) {
			$date = $this->month . '-' . $day;
			$json = $this->redis->get($date);
			$data = json_decode($json, false);
			$monthData[$day] = $data ? end($data) - first($data) : 0;
		}
		return $monthData;
	}

}
