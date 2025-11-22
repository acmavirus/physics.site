<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Formula extends CI_Controller
{
    public function index($slug = null)
    {
        if ($slug === null) {
            show_404();
        }

        $path = APPPATH . 'data/mindmap/formula_mindmap_' . $slug . '.json';
        $fallback = APPPATH . 'data/mindmap/formula_mindmap_emc2.json';
        if (!file_exists($path)) {
            $alt = APPPATH . 'data/formula_mindmap_' . $slug . '.json';
            if (file_exists($alt)) { $path = $alt; }
        }
        if (!file_exists($fallback)) {
            $altFallback = APPPATH . 'data/formula_mindmap_emc2.json';
            if (file_exists($altFallback)) { $fallback = $altFallback; }
        }

        $cfgData = @file_exists($path) ? @file_get_contents($path) : @file_get_contents($fallback);
        $cfg = json_decode($cfgData ?: '', true);
        if (!is_array($cfg)) {
            $cfgData = @file_get_contents($fallback);
            $cfg = json_decode($cfgData ?: '', true);
            if (!is_array($cfg)) {
                $cfg = [];
            }
        }

        $files = glob(APPPATH . 'data/mindmap/formula_mindmap_*.json');
        $available = [];
        foreach ($files as $f) {
            $base = basename($f, '.json');
            $slugItem = substr($base, strlen('formula_mindmap_'));
            if ($slugItem) {
                $available[] = [
                    'slug' => $slugItem,
                    'label' => $slugItem
                ];
            }
        }

        $data['slug'] = $slug;
        $data['mmConfig'] = $cfg;
        $data['availableFormulas'] = $available;
        $this->load->view('public/formula/index', $data);
    }
}
