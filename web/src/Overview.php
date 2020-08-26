<?php /** @noinspection AdditionOperationOnArraysInspection */

use Predis\Client;

class Overview extends Controller
{

	public $date;
	protected $redis;

	public function __construct(Client $redis)
	{
		parent::__construct();
		$this->redis = $redis;
		$getDate = $this->request->getDateFromY_M_D('date');
		$this->date = $getDate ? date('Y-m-d', $getDate) : date('Y-m-d');
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

		$totalToday = $data ? end($data) - first($data) : 0;

		$view = View::getInstance(__DIR__ . '/../template/Overview.phtml', $this);
		return $view->render([
			'title' => $this->date,
			'content' => $this->s($content),
			'dataTable' => getDebug($diff),
			'jsonData' => json_encode($diff),
			'jsonLabels' => json_encode(
				($data ? array_keys($data) : []) +
				($yesterdayData ? array_keys($yesterdayData) : [])),
			'meter' => number_format($data ? end($data) : 0, 2, '.', ' '),
			'current' => number_format(end($diff), 2),
			'currentEUR' => number_format(end($diff) * 0.3, 2),
			'totalYesterday' => number_format($yesterdayConsumption, 2),
			'totalYesterdayEUR' => number_format($yesterdayConsumption * 0.3, 2),
			'total' => number_format($totalToday, 2),
			'totalEUR' => number_format($totalToday * 0.3, 2),
			'jsonYesterday' => json_encode($diffYesterday),
			'time' => 'today',
			'time_1' => 'yesterday',
		]);
	}

	public function getDiff($data)
	{
		$diff = [];
		if (!$data) {
			return $diff;
		}
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
