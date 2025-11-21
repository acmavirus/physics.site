<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Formula extends CI_Controller
{
	public function index($slug = null)
	{
		if ($slug === null) {
			show_404();
		}

		$data['slug'] = $slug;
		$this->load->view('public/formula/index', $data);
	}
}
