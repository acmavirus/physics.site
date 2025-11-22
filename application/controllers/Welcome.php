<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Welcome extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/userguide3/general/urls.html
	 */
	public function index()
	{
		$catPath = APPPATH . 'data/physics_categories.json';
		$words = [];
		if (file_exists($catPath)) {
			$json = file_get_contents($catPath);
			$arr = json_decode($json, true);
			if (is_array($arr)) { $words = $arr; }
		}
		$data['words'] = $words;
		$data['category_base'] = '/danh-muc/';
		$this->load->view('public/index', $data);
	}
}
