<?php

class Preview extends Controller
{

	public function indexAction()
	{
		$files = glob('/var/motion/*.jpg');
		usort($files, static function ($file1, $file2) {
			$time1 = filemtime($file1);
			$time2 = filemtime($file2);
			return $time1 <=> $time2;
		});
		foreach ($files as $file) {
			$mtime = date('Y-m-d H:i:s', filemtime($file));
			$title = basename($file) . chr(13) . $mtime;
			$content[] = '		
			<a href="' . self::href([
					'action' => 'preview',
					'file' => basename($file),
				]) . '" title="' . $title . '">
			<img src="../../' . basename($file) . '" width="255"/>
			</a>';
		}
		return $content;
	}

	public function previewAction($file)
	{
		$content[] = '<img src="../../' . basename($file) . '">';
		$onlyName = pathinfo($file, PATHINFO_FILENAME);
		$pngFile = $onlyName . '.png';
		$content[] = '<img src="../../' . $pngFile . '">';

		$absFile = '/var/motion/' . $file;
//		$stat = stat($absFile);
//		$stat = array_reverse($stat);
		$stat = [
			'name' => $file,
			'size' => filesize($absFile),
			'mtime' => date('Y-m-d H:i:s', filemtime($absFile)),
		];
		$content[] = getDebug($stat);
		return $content;
	}

}
