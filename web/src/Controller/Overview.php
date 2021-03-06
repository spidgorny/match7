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

	/**
	 * @return MarkdownView|string|string[]|View
	 * @throws JsonException
	 */
	public function indexAction()
	{
		$content[] = $this->html->h1($this->date, ['class' => 'title']);
		$yesterdayData = $this->getYesterday();
		$json = $this->redis->get($this->date);
//		$content[] = $this->html->div($json);
		$data = json_decode($json, true);
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

		$dataAndDiff = [];
		if ($data) {
			foreach ($data as $key => $val) {
				$dataAndDiff[] = [
					'key' => $key,
					'value' => $val,
					'diff' => $diff[$key],
				];
			}
		}

		$view = View::getInstance(__DIR__ . '/../../template/Overview.phtml', $this);
		return $view->render([
			'title' => $this->date,
			'content' => $this->s($content),
			'dataTable' => new slTable($dataAndDiff, [
				'class' => 'table',
			]),
			'jsonData' => json_encode(
				array_values($diff), JSON_THROW_ON_ERROR),
			'jsonLabels' => json_encode(
				($data ? array_keys($data) : []) +
				($yesterdayData ? array_keys($yesterdayData) : [])),
			'meter' => number_format($data ? end($data) : 0, 1, '.', ' '),
			'current' => number_format(end($diff), 2),
			'currentEUR' => number_format(end($diff) * 0.3, 2),
			'totalYesterday' => number_format($yesterdayConsumption, 2),
			'totalYesterdayEUR' => number_format($yesterdayConsumption * 0.3, 2),
			'total' => number_format($totalToday, 2),
			'totalEUR' => number_format($totalToday * 0.3, 2),
			'jsonYesterday' => json_encode($diffYesterday, JSON_THROW_ON_ERROR),
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
		foreach ($data as $key => $el) {
			$diff[$key] = $el - $prev;
			$prev = $el;
		}
		return $diff;
	}

	public function saveTestAction()
	{
		$base = 71458;
		$yesterday = $this->getYesterday();
		if ($yesterday) {
			$base = end($yesterday);
		}
		$dictionary = [];
		foreach (range(0, 24) as $a) {
			$value = $base + ($a + mt_rand(-4, 4)) / 24;
			$dictionary[$a . ':00:00'] = $value;
			$base = $value;
		}
		$this->redis->set($this->date, json_encode($dictionary));
		return $this->request->goBack();
	}

	public function getYesterday()
	{
		$yesterday = date('Y-m-d', strtotime('-1 day', strtotime($this->date)));
		$yesterdayJson = $this->redis->get($yesterday);
		$yesterdayData = json_decode($yesterdayJson, true);
		return $yesterdayData;
	}

}
