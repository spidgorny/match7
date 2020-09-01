<?php

class Match extends Controller
{

	public function __construct()
	{
		parent::__construct();
	}

	public function indexAction()
	{
		$img = 'http://192.168.1.207:8081';
		$content[] = '<img src="' . $img . '" />';
		$lastSnap = 'http://192.168.1.207/lastsnap.jpg';
		$content[] = '<img src="' . $lastSnap . '" />';
		$content[] = $this->getActionButton('Denoise', 'denoise');
		$denoise = 'http://192.168.1.207/output.png';
		$content[] = '<img src="' . $denoise . '" />';
		return $content;
	}

	public function denoiseAction()
	{
		$cmd = 'convert /var/motion/lastsnap.jpg -rotate 2 -auto-level -auto-gamma -noise 5 -median 5 -unsharp 5 -normalize /var/motion/output.png';
		$content[] = $cmd;
		exec($cmd, $output);
		$content[] = $output;
		return $this->request->goBack();
	}

}
