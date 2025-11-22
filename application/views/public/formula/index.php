<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Mindmap Công Thức Vật Lý</title>
    <link rel="stylesheet" href="/assets/css/common.css">
    <link rel="stylesheet" href="/assets/css/formula.css">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@700;800&family=STIX+Two+Text:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/mind-elixir/dist/style.css">
</head>

<body>
    <canvas id="physics-bg"></canvas>
    <div id="mindmap">
        <svg id="mm-links"></svg>
        <div class="mm-node center" id="mm-center" data-key="center" data-x="50" data-y="50"></div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/mind-elixir/dist/MindElixir.js"></script>
    <script>
        window.mmConfig = <?php echo json_encode(isset($mmConfig) ? $mmConfig : [], JSON_UNESCAPED_UNICODE); ?>;
        window.currentFormula = <?php echo json_encode(isset($slug) ? $slug : 'emc2'); ?>;
        window.formulaBase = <?php echo json_encode('/cong-thuc/'); ?>;
    </script>
    <script src="/assets/js/formula.js?v=3"></script>
    <script src="/assets/js/physics-bg.js"></script>
</body>

</html>
