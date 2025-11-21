<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once FCPATH . 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(FCPATH);
$dotenv->load();