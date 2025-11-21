<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Danh mục công thức</title>
    <style>
        html,
        body {
            height: 100%
        }

        body {
            margin: 0;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center
        }

        #wordcloud { width: 95vw; height: 78vh }
    </style>
    <style>
        #physics-bg {
            position: fixed;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            z-index: 0
        }

        #wordcloud {
            position: relative;
            z-index: 1
        }
    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@700;800&family=STIX+Two+Text:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
</head>

<body>
    <canvas id="physics-bg"></canvas>
    <div id="wordcloud"></div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/d3/7.9.0/d3.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/d3-cloud/1.2.5/d3.layout.cloud.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js"></script>
    <?php
    $baseDir = 'application/data/formulas/';
    $catPath = $baseDir . $slug . '.json';
    if (file_exists($catPath)) {
        $json = file_get_contents($catPath);
        $arr = json_decode($json, true);
        if (is_array($arr)) {
            $words = $arr;
        }
    }
    ?>
    <script>
        (function() {
            var container = document.getElementById('wordcloud');

            function parseFormulaItems(raw) {
                if (!Array.isArray(raw)) return [];
                return raw.map(function(w) {
                    if (typeof w === 'string') return {
                        name: w,
                        latex: '',
                        slug: ''
                    };
                    return {
                        name: w.name || w.text || '',
                        latex: w.latex || '',
                        slug: w.slug || ''
                    };
                });
            }

            function buildWordsFromText(text) {
                if (!text || typeof text !== 'string') return null;
                var sw = new Set("i,me,my,myself,we,us,our,ours,ourselves,you,your,yours,yourself,yourselves,he,him,his,himself,she,her,hers,herself,it,its,itself,they,them,their,theirs,themselves,what,which,who,whom,whose,this,that,these,those,am,is,are,was,were,be,been,being,have,has,had,having,do,does,did,doing,will,would,should,can,could,ought,i'm,you're,he's,she's,it's,we're,they're,i've,you've,we've,they've,i'd,you'd,he'd,she'd,we'd,they'd,i'll,you'll,he'll,she'll,we'll,they'll,isn't,aren't,wasn't,weren't,hasn't,haven't,hadn't,doesn't,don't,didn't,won't,wouldn't,shan't,shouldn't,can't,cannot,couldn't,mustn't,let's,that's,who's,what's,here's,there's,when's,where's,why's,how's,a,an,the,and,but,if,or,because,as,until,while,of,at,by,for,with,about,against,between,into,through,during,before,after,above,below,to,from,up,upon,down,in,out,on,off,over,under,again,further,then,once,here,there,when,where,why,how,all,any,both,each,few,more,most,other,some,such,no,nor,not,only,own,same,so,than,too,very,say,says,said,shall".split(","));
                var vn = new Set(["và", "của", "là", "một", "những", "các", "trong", "trên", "dưới", "với", "để", "khi", "đã", "đang", "sẽ", "không", "có", "như", "cũng", "nhưng", "thì", "vì", "tại", "từ", "cho", "này", "kia", "đó", "hay"]);
                var tokens = (text.match(/[\p{L}\p{N}]+/gu) || []).map(function(t) {
                    return t.toLowerCase()
                });
                var counts = new Map();
                for (var i = 0; i < tokens.length; i++) {
                    var t = tokens[i];
                    if (t.length < 2) continue;
                    if (sw.has(t) || vn.has(t)) continue;
                    counts.set(t, (counts.get(t) || 0) + 1);
                }
                var arr = Array.from(counts.entries()).map(function(d) {
                    return {
                        text: d[0],
                        value: d[1]
                    }
                });
                arr.sort(function(a, b) {
                    return b.value - a.value
                });
                arr = arr.slice(0, 200);
                var values = arr.map(function(d) {
                    return d.value
                });
                var s = d3.scaleSqrt().domain([d3.min(values), d3.max(values)]).range([12, 90]);
                return arr.map(function(d) {
                    return {
                        text: d.text,
                        size: s(d.value)
                    }
                });
            }
            var phpItems = <?php echo isset($words) ? json_encode($words, JSON_UNESCAPED_UNICODE) : '[]'; ?>;
            var items = parseFormulaItems(phpItems);
            var baseSizes = items.map(function(it){ return ((it.latex||it.name||'').length)||1 });
            var scale = d3.scaleSqrt().domain([d3.min(baseSizes), d3.max(baseSizes)]).range([14, 56]);
            var MIN_SIZE = 14, MAX_SIZE = 56;
            var words = items.map(function(it){
                var t = it.latex || it.name || '';
                var sz = scale(t.length||1);
                sz = Math.max(MIN_SIZE, Math.min(MAX_SIZE, sz));
                return { text: t, size: sz, latex: it.latex||'', slug: it.slug||'' };
            });
            var detailBase = <?php echo json_encode(isset($formula_base) ? $formula_base : '/cong-thuc/'); ?>;

            function slugify(str) {
                return String(str).normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
            }

            function drawBackground() {
                var c = document.getElementById('physics-bg');
                if (!c) return;
                var dpr = window.devicePixelRatio || 1;
                var w = window.innerWidth;
                var h = window.innerHeight;
                c.width = w * dpr;
                c.height = h * dpr;
                c.style.width = w + 'px';
                c.style.height = h + 'px';
                var ctx = c.getContext('2d');
                ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
                ctx.clearRect(0, 0, w, h);
                var grd = ctx.createLinearGradient(0, 0, 0, h);
                grd.addColorStop(0, '#ffffff');
                grd.addColorStop(1, '#f7fafc');
                ctx.fillStyle = grd;
                ctx.fillRect(0, 0, w, h);
                ctx.strokeStyle = 'rgba(0,0,0,0.06)';
                ctx.lineWidth = 1;
                var step = 40;
                for (var x = 0; x < w; x += step) {
                    ctx.beginPath();
                    ctx.moveTo(x, 0);
                    ctx.lineTo(x, h);
                    ctx.stroke();
                }
                for (var y = 0; y < h; y += step) {
                    ctx.beginPath();
                    ctx.moveTo(0, y);
                    ctx.lineTo(w, y);
                    ctx.stroke();
                }
                var eq = ['E = mc²', 'F = ma', 'Δx·Δp ≥ ℏ/2', 'v = s/t', 'λ = c/f', 'a = dv/dt', '∫ F·ds', 'Σ F = 0'];
                ctx.fillStyle = 'rgba(0,0,0,0.12)';
                ctx.font = '700 28px STIX Two Text, Noto Sans, system-ui';
                for (var i = 0; i < eq.length; i++) {
                    var x0 = Math.random() * w;
                    var y0 = Math.random() * h;
                    ctx.save();
                    ctx.translate(x0, y0);
                    ctx.rotate((Math.random() - 0.5) * 0.2);
                    ctx.fillText(eq[i], 0, 0);
                    ctx.restore();
                }
            }

            function render() {
                var width = container.clientWidth;
                var height = container.clientHeight;
                container.innerHTML = '';
                d3.layout.cloud().size([width, height]).words(words).padding(4).rotate(function(){ return 0 }).font('Noto Sans,STIX Two Text,system-ui,-apple-system,Segoe UI,Roboto,Helvetica Neue,Arial').fontSize(function(d){ return d.size }).spiral('rectangular').on('end', function(layoutWords){
                    var layer = document.createElement('div');
                    layer.style.position = 'absolute';
                    layer.style.left = '0';
                    layer.style.top = '0';
                    layer.style.width = width + 'px';
                    layer.style.height = height + 'px';
                    container.appendChild(layer);
                    var activeNode = null;
                    var activeRotateMap = new WeakMap();
                    layoutWords.forEach(function(d){
                        var node = document.createElement('a');
                        node.style.position = 'absolute';
                        node.style.left = (width/2 + d.x) + 'px';
                        node.style.top = (height/2 + d.y) + 'px';
                        node.style.transform = 'translate(-50%,-50%) rotate('+d.rotate+'deg)';
                        node.style.fontSize = d.size + 'px';
                        node.style.fontFamily = 'Noto Sans, STIX Two Text, system-ui, -apple-system, Segoe UI, Roboto, Helvetica Neue, Arial';
                        node.style.fontWeight = '800';
                        node.style.color = '#000';
                        node.style.cursor = 'pointer';
                        var link = d.slug ? (detailBase + d.slug) : (detailBase + slugify(d.text));
                        node.setAttribute('href', link);
                        node.style.transition = 'transform 220ms cubic-bezier(0.22, 1, 0.36, 1), color 160ms ease-out';
                        activeRotateMap.set(node, d.rotate);
                        node.addEventListener('mouseover', function(){
                            if(activeNode && activeNode !== node){
                                var r = activeRotateMap.get(activeNode) || 0;
                                activeNode.style.transform = 'translate(-50%,-50%) rotate('+r+'deg)';
                                activeNode.style.color = '#000';
                            }
                            activeNode = node;
                            node.style.color = 'rgba(247,139,139,1)';
                            node.style.transform = 'translate(-50%,-50%) rotate('+d.rotate+'deg) scale(1.12)';
                        });
                        node.addEventListener('mouseout', function(){
                            node.style.color = '#000';
                            node.style.transform = 'translate(-50%,-50%) rotate('+d.rotate+'deg)';
                            if(activeNode === node) activeNode = null;
                        });
                        var inner = document.createElement('div');
                        inner.style.whiteSpace = 'nowrap';
                        inner.style.display = 'inline-block';
                        inner.style.lineHeight = '1';
                        var latexText = (d.latex || d.text || '').replace(/\n+/g, ' ');
                        try { katex.render(latexText, inner, { throwOnError: false, displayMode: false }); } catch(e) { inner.textContent = latexText; }
                        node.appendChild(inner);
                        layer.appendChild(node);
                    });
                    (function(){
                        var margin = 6;
                        var nodes = Array.from(layer.children);
                        var cx = width/2, cy = height/2;
                        function box(n){
                            var w = n.offsetWidth, h = n.offsetHeight;
                            var x = parseFloat(n.style.left)||0;
                            var y = parseFloat(n.style.top)||0;
                            return {x:x,y:y,w:w,h:h};
                        }
                        function overlap(a,b){
                            return Math.abs(a.x-b.x) < (a.w+b.w)/2 + margin && Math.abs(a.y-b.y) < (a.h+b.h)/2 + margin;
                        }
                        for (var i=0;i<nodes.length;i++){
                            var ni = nodes[i];
                            var bi = box(ni);
                            var attempts=0;
                            while (attempts<160){
                                var collided=false;
                                for (var j=0;j<i;j++){
                                    var bj = box(nodes[j]);
                                    if (overlap(bi,bj)){
                                        collided=true;
                                        var vx = bi.x - cx, vy = bi.y - cy;
                                        var len = Math.max(8, Math.sqrt(vx*vx+vy*vy));
                                        var step = 8;
                                        var nx = bi.x + (vx/len)*step;
                                        var ny = bi.y + (vy/len)*step;
                                        nx = Math.min(width - bi.w/2 - margin, Math.max(bi.w/2 + margin, nx));
                                        ny = Math.min(height - bi.h/2 - margin, Math.max(bi.h/2 + margin, ny));
                                        ni.style.left = nx + 'px';
                                        ni.style.top = ny + 'px';
                                        bi = box(ni);
                                    }
                                }
                                if (!collided) break;
                                attempts++;
                            }
                            if (attempts>=160){
                                ni.style.transform = ni.style.transform + ' scale(0.92)';
                            }
                        }
                    })();
                }).start();
            }
            drawBackground();
            render();
            var ro = new ResizeObserver(function() {
                drawBackground();
                render()
            });
            ro.observe(container);
            window.addEventListener('resize', drawBackground);
        })();
    </script>
</body>

</html>
