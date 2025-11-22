<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Category extends CI_Controller
{
	public function index($slug = null)
	{
		if ($slug === null) {
			show_404();
		}

		$baseDir = APPPATH . 'data/formulas/';
		$catPath = $baseDir . $slug . '.json';
		$words = [];
		if (file_exists($catPath)) {
			$json = file_get_contents($catPath);
			$arr = json_decode($json, true);
			if (is_array($arr)) {
				$words = $arr;
			}
		}

		$data['slug'] = $slug;
		$data['words'] = $words;
		$data['formula_base'] = '/cong-thuc/';
		$this->load->view('public/category/index', $data);
	}
}
