<?php

class TPopup extends TMain {
	var $name = 'popup';
	var $template = 'popup';

	// Максимальный размер окна
	var $max_window_width = 700;
	var $max_window_height = 500;

	// Добавление размера к картике
	var $add_width = 9;
	var $add_height = 70;

	function setup() {
		TTree::setup();
		$this->elements = array('gallery');
	}

	function show() {
		$this->config_load(lang().'.conf');
		
		require_once('elems.php');
		
		$file = $_GET['file'];
		$alt = $_GET['alt'];
		$name = $_GET['name'];
		$num = $_GET['num'];
		$pid = $_GET['pid'];
		$this->assign('base_href', $this->base_href);
		$this->assign('title', $name);
		$this->assign('lang', $this->lang);
		if (is_file($file)) {
			$this->assign('img', $file);
			$this->assign('alt', $alt);
			$this->assign('resize', $this->GetResize($file));
		}
		elseif(!empty($pid) && !empty($num))
		{
			$this->assign('pid', $pid);
			$this->assign('num', ($num));
		}
	}

	function GetResize($file) {
		$img_size = GetImageSize($file);
		$width = $img_size[0] + $this->add_width;
		$height = $img_size[1] + $this->add_height;

		if ($width > $this->max_window_width)
			$width = $this->max_window_width;
		if ($height > $this->max_window_height)
			$height = $this->max_window_height;

		return array(
			'width' => $width,
			'height' => $height
		);
	}

}
// end of class TPopup

?>