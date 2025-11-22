<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tools extends CI_Controller
{
    public function build_mindmaps()
    {
        $srcDir = APPPATH . 'data/formulas/';
        $dstDir = APPPATH . 'data/mindmap/';
        if (!is_dir($srcDir)) { show_error('Source directory not found: ' . $srcDir, 500); return; }
        if (!is_dir($dstDir)) { @mkdir($dstDir, 0777, true); }

        $palette = [ 'E' => '#e63946', 'm' => '#1d4ed8', 'c' => '#16a34a' ];
        $sectors = [
            'E' => [ 'start' => -130, 'end' => -30, 'base' => 140 ],
            'm' => [ 'start' => -10, 'end' => 70, 'base' => 140 ],
            'c' => [ 'start' => 110, 'end' => 200, 'base' => 140 ],
        ];

        $report = [ 'total_items' => 0, 'written' => 0, 'skipped' => 0, 'errors' => [] ];
        $files = glob($srcDir . '*.json');
        foreach ($files as $file) {
            $json = @file_get_contents($file);
            if ($json !== false) {
                $json = preg_replace('/^\xEF\xBB\xBF/', '', $json);
            }
            $arr = json_decode($json ?: '', true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
            if (!is_array($arr)) { $report['errors'][] = 'Invalid JSON in ' . basename($file); continue; }
            foreach ($arr as $item) {
                $report['total_items']++;
                $name = '';
                $latex = '';
                $slug = '';
                if (is_string($item)) { $name = $item; }
                elseif (is_array($item)) { $name = isset($item['name']) ? $item['name'] : (isset($item['text']) ? $item['text'] : ''); $latex = isset($item['latex']) ? $item['latex'] : ''; $slug = isset($item['slug']) ? $item['slug'] : ''; }
                $text = $latex !== '' ? $latex : $name;
                if ($text === '') { $report['skipped']++; continue; }
                if ($slug === '') { $slug = $this->slugify($name !== '' ? $name : $latex); }
                if ($slug === '') { $report['skipped']++; continue; }

                $mm = [
                    'centerLatex' => $text,
                    'colors' => $palette,
                    'sectors' => $sectors,
                    'groups' => [
                        'E' => [ [ 'latex' => $text, 'angle' => -40, 'radius' => 140 ], [ 'latex' => $text, 'angle' => 0, 'radius' => 160 ] ],
                        'm' => [ [ 'latex' => $text, 'angle' => 0, 'radius' => 160 ] ],
                        'c' => [ [ 'latex' => $text, 'angle' => 40, 'radius' => 140 ] ],
                    ],
                ];

                $outPath = $dstDir . 'formula_mindmap_' . $slug . '.json';
                if (file_exists($outPath)) { $report['skipped']++; continue; }
                $ok = @file_put_contents($outPath, json_encode($mm, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
                if ($ok === false) { $report['errors'][] = 'Write failed: ' . $outPath; continue; }
                $report['written']++;
            }
        }

        $this->output->set_content_type('application/json')->set_output(json_encode($report, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    private function slugify($str)
    {
        if (!is_string($str)) { return ''; }
        $str = trim($str);
        if ($str === '') return '';
        $hasIntl = function_exists('transliterator_transliterate');
        if ($hasIntl) { $str = transliterator_transliterate('Any-Latin; Latin-ASCII', $str); }
        else { $str = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $str); }
        $str = strtolower($str);
        $str = preg_replace('/[^a-z0-9]+/i', '-', $str);
        $str = preg_replace('/^-+|-+$/', '', $str);
        return $str ?: '';
    }
}
