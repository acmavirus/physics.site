<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Mindmap Công Thức Vật Lý</title>
    <style>
        /* Thêm styles cho dropdown và controls */
        .controls {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.95);
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
        }
        
        .formula-selector {
            margin-bottom: 10px;
        }
        
        .formula-selector select {
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background: white;
            font-size: 14px;
            min-width: 200px;
        }
        
        .search-box {
            margin-bottom: 10px;
        }
        
        .search-box input {
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 14px;
            width: 200px;
        }
        
        .tooltip {
            position: fixed;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            pointer-events: none;
            z-index: 1001;
            opacity: 0;
            transition: opacity 0.3s ease;
            max-width: 300px;
        }
        
        .tooltip.show {
            opacity: 1;
        }
        
        /* Animation cho nodes */
        .mm-node {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            opacity: 0;
            transform: translate(-50%, -50%) scale(0.8);
        }
        
        .mm-node.show {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }
        
        .mm-node.child {
            transition-delay: 0.1s;
        }
        
        /* Hiệu ứng hover */
        .mm-node:hover {
            transform: translate(-50%, -50%) scale(1.05);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            z-index: 10;
        }
        
        .mm-chip:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        
        /* Loading animation */
        .loading {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1002;
        }
        
        .loading::after {
            content: '';
            display: block;
            width: 40px;
            height: 40px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
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

        #mindmap {
            position: relative;
            z-index: 1;
            width: 92vw;
            max-width: 1200px;
            height: 80vh;
        }

        #mm-links {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
        }

        .mm-node {
            position: absolute;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 12px 16px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            transform: translate(-50%, -50%);
            font-family: 'Noto Sans', 'STIX Two Text', system-ui, -apple-system, Segoe UI, Roboto, Helvetica Neue, Arial;
        }

        .mm-node.center {
            font-weight: 800;
            font-size: 22px
        }

        .mm-node {
            font-size: 16px
        }

        .mm-actions {
            margin-top: 8px;
            display: flex;
            gap: 8px
        }

        .mm-chip {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 999px;
            border: 1px solid #e5e7eb;
            background: #fff;
            font-weight: 700;
            cursor: pointer
        }

        .key-E {
            color: #e63946
        }

        .key-m {
            color: #1d4ed8
        }

        .key-c {
            color: #16a34a
        }
    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans:wght@700;800&family=STIX+Two+Text:wght@700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.css">
</head>

<body>
    <!-- Loading indicator -->
    <div class="loading" id="loading" style="display: none;"></div>
    
    <!-- Controls panel -->
    <div class="controls">
        <div class="formula-selector">
            <select id="formulaSelect" onchange="loadFormula(this.value)">
                <option value="emc2">E = mc² (Năng lượng - Khối lượng)</option>
                <option value="newton2">F = ma (Định luật Newton 2)</option>
                <option value="dao_dong">Dao động điều hòa</option>
                <option value="dien_tu">Điện từ học</option>
                <option value="dong_luong">Động lượng</option>
                <option value="nhiet_hoc">Nhiệt học</option>
                <option value="quang_hoc">Quang học</option>
                <option value="song">Sóng học</option>
                <option value="planck">E = hf (Năng lượng lượng tử)</option>
            </select>
        </div>
        <div class="search-box">
            <input type="text" id="searchInput" placeholder="Tìm kiếm công thức..." onkeyup="searchFormulas(this.value)">
        </div>
    </div>
    
    <!-- Tooltip -->
    <div class="tooltip" id="tooltip"></div>
    
    <canvas id="physics-bg"></canvas>
    <div id="mindmap">
        <svg id="mm-links"></svg>
        <div class="mm-node center" id="mm-center" data-key="center" data-x="50" data-y="50"></div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/katex.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/katex@0.16.9/dist/contrib/auto-render.min.js"></script>
    <script>
        // Global variables
        var mmConfig = null;
        var currentFormula = 'emc2';
        var allFormulas = {};
        var tooltip = null;
        
        // Load formula configuration from PHP
        function loadFormulaConfig(formulaName) {
            return new Promise(function(resolve, reject) {
                // Show loading
                document.getElementById('loading').style.display = 'block';
                
                // Use AJAX to load formula config
                var xhr = new XMLHttpRequest();
                xhr.open('GET', '/application/data/formula_mindmap_' + formulaName + '.json', true);
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4) {
                        document.getElementById('loading').style.display = 'none';
                        if (xhr.status === 200) {
                            try {
                                var config = JSON.parse(xhr.responseText);
                                resolve(config);
                            } catch (e) {
                                reject('Invalid JSON format');
                            }
                        } else {
                            // Load default config if file not found
                            resolve(getDefaultConfig(formulaName));
                        }
                    }
                };
                xhr.send();
            });
        }
        
        // Get default configuration for different formulas
        function getDefaultConfig(formulaName) {
            var configs = {
                emc2: {
                    centerLatex: 'E \; = \; mc^2',
                    colors: { E: '#e63946', m: '#1d4ed8', c: '#16a34a' },
                    sectors: { E: { start: -130, end: -30, base: 140 }, m: { start: -10, end: 70, base: 140 }, c: { start: 110, end: 200, base: 140 } },
                    groups: {
                        E: [{ latex: 'J', angle: -40, radius: 140 }, { latex: 'E_0 = mc^2', angle: 0, radius: 160 }, { latex: 'E \approx 9\times 10^{16}\,\text{J}', angle: 40, radius: 140 }],
                        m: [{ latex: 'kg', angle: -40, radius: 140 }, { latex: 'm', angle: 0, radius: 160 }, { latex: 'p = m v', angle: 40, radius: 140 }],
                        c: [{ latex: 'c \approx 3\times 10^{8}\,\text{m/s}', angle: -40, radius: 140 }, { latex: 'c \text{ trong chân không}', angle: 0, radius: 160 }, { latex: 'E = h f', angle: 40, radius: 140 }]
                    }
                },
                newton2: {
                    centerLatex: 'F \; = \; ma',
                    colors: { F: '#dc2626', m: '#2563eb', a: '#059669' },
                    sectors: { F: { start: -120, end: -20, base: 140 }, m: { start: -10, end: 80, base: 140 }, a: { start: 100, end: 190, base: 140 } },
                    groups: {
                        F: [{ latex: 'N', angle: -40, radius: 140 }, { latex: '\vec{F} = m\vec{a}', angle: 0, radius: 160 }, { latex: '\sum F = ma', angle: 40, radius: 140 }],
                        m: [{ latex: 'kg', angle: -40, radius: 140 }, { latex: 'm = \frac{F}{a}', angle: 0, radius: 160 }, { latex: '\rho = \frac{m}{V}', angle: 40, radius: 140 }],
                        a: [{ latex: 'm/s^2', angle: -40, radius: 140 }, { latex: 'a = \frac{F}{m}', angle: 0, radius: 160 }, { latex: '\vec{a} = \frac{d\vec{v}}{dt}', angle: 40, radius: 140 }]
                    }
                }
            };
            return configs[formulaName] || configs.emc2;
        }
        
        // Load formula function
        function loadFormula(formulaName) {
            if (formulaName === currentFormula) return;
            
            currentFormula = formulaName;
            loadFormulaConfig(formulaName).then(function(config) {
                mmConfig = config;
                initializeMindmap();
            }).catch(function(error) {
                console.error('Error loading formula:', error);
                mmConfig = getDefaultConfig(formulaName);
                initializeMindmap();
            });
        }
        
        // Search function
        function searchFormulas(searchTerm) {
            if (!searchTerm) {
                // Show all nodes
                document.querySelectorAll('.mm-node').forEach(function(node) {
                    node.style.display = 'block';
                });
                return;
            }
            
            var term = searchTerm.toLowerCase();
            var visibleNodes = [];
            
            // Search in current formula data
            if (mmConfig && mmConfig.groups) {
                Object.keys(mmConfig.groups).forEach(function(group) {
                    mmConfig.groups[group].forEach(function(item, index) {
                        var text = (item.latex || item.text || '').toLowerCase();
                        var name = (item.name || '').toLowerCase();
                        var description = (item.description || '').toLowerCase();
                        
                        if (text.includes(term) || name.includes(term) || description.includes(term)) {
                            visibleNodes.push({ group: group, index: index });
                        }
                    });
                });
            }
            
            // Hide/show nodes based on search results
            document.querySelectorAll('.mm-node').forEach(function(node) {
                var shouldShow = false;
                
                if (node.id === 'mm-center') {
                    shouldShow = true; // Always show center
                } else {
                    var group = node.getAttribute('data-group');
                    var nodeIndex = Array.from(node.parentNode.children).indexOf(node) - 1; // -1 for center node
                    
                    visibleNodes.forEach(function(visible) {
                        if (visible.group === group && visible.index === nodeIndex) {
                            shouldShow = true;
                        }
                    });
                }
                
                node.style.display = shouldShow ? 'block' : 'none';
            });
        }
        
        // Tooltip functions
        function showTooltip(element, text) {
            if (!tooltip) {
                tooltip = document.getElementById('tooltip');
            }
            
            tooltip.textContent = text;
            tooltip.classList.add('show');
            
            // Position tooltip near the element
            var rect = element.getBoundingClientRect();
            tooltip.style.left = (rect.right + 10) + 'px';
            tooltip.style.top = rect.top + 'px';
        }
        
        function hideTooltip() {
            if (tooltip) {
                tooltip.classList.remove('show');
            }
        }
        
        // Initialize mindmap
        function initializeMindmap() {
            // Clear existing nodes except center
            var mapEl = document.getElementById('mindmap');
            var centerEl = document.getElementById('mm-center');
            
            // Remove all child nodes
            Array.from(mapEl.querySelectorAll('.mm-node.child')).forEach(function(node) {
                node.remove();
            });
            
            // Reset center node
            centerEl.setAttribute('data-expanded-E', '0');
            centerEl.setAttribute('data-expanded-m', '0');
            centerEl.setAttribute('data-expanded-c', '0');
            
            // Re-render center
            renderCenterNode();
            
            // Add animation to center node
            setTimeout(function() {
                centerEl.classList.add('show');
            }, 100);
        }
        
        // Render center node
        function renderCenterNode() {
            var centerEl = document.getElementById('mm-center');
            var centerLatexRaw = (mmConfig && mmConfig.centerLatex) ? mmConfig.centerLatex : 'E \\; = \\; mc^2';
            var dynamicKeys = Object.keys((mmConfig && mmConfig.colors) || {});
            var centerLatexWrapped = wrapCenterLatex(centerLatexRaw, dynamicKeys);
            
            try {
                katex.render(centerLatexWrapped, centerEl, {
                    throwOnError: false,
                    displayMode: false,
                    trust: true,
                    strict: false
                });
            } catch (e) {
                centerEl.textContent = '';
                try {
                    katex.render(centerLatexRaw, centerEl, { throwOnError: false, displayMode: false });
                } catch (err) {
                    centerEl.textContent = centerLatexRaw;
                }
            }
            
            // Add click handlers for center elements
            setTimeout(function() {
                addCenterClickHandlers();
            }, 500);
        }
        
        // Add click handlers to center elements
        function addCenterClickHandlers() {
            var centerEl = document.getElementById('mm-center');
            var branch = (mmConfig && mmConfig.groups) ? mmConfig.groups : {};
            
            Object.keys(branch || {}).forEach(function(k) {
                var el = centerEl.querySelector('.key-' + k);
                if (el) {
                    el.style.cursor = 'pointer';
                    el.addEventListener('click', function() {
                        toggleBranchGroup(k);
                    });
                    
                    // Add tooltip
                    el.addEventListener('mouseenter', function() {
                        var description = getDescriptionForKey(k);
                        if (description) {
                            showTooltip(el, description);
                        }
                    });
                    
                    el.addEventListener('mouseleave', hideTooltip);
                }
            });
        }
        
        // Get description for key
        function getDescriptionForKey(key) {
            var descriptions = {
                E: 'Năng lượng - Đại lượng đặc trưng cho khả năng thực hiện công',
                m: 'Khối lượng - Đại lượng đặc trưng cho lượng vật chất',
                c: 'Tốc độ ánh sáng - Hằng số vật lý cơ bản',
                F: 'Lực - Tác dụng làm thay đổi trạng thái chuyển động của vật',
                a: 'Gia tốc - Tốc độ thay đổi của vận tốc'
            };
            return descriptions[key] || '';
        }
        
        // Original function with modifications
        function wrapCenterLatex(latex, keys) {
            if (!latex || !keys || !keys.length) return latex;
            var out = latex;
            for (var i = 0; i < keys.length; i++) {
                var key = keys[i];
                var tok = tokenForKey(key);
                if (!tok) continue;
                var pattern = new RegExp(escapeRegex(tok), 'g');
                out = out.replace(pattern, function(m) {
                    return '\\htmlClass{key-' + key + '}{' + m + '}';
                });
            }
            return out;
        }
        
        function escapeRegex(s) {
            return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }
        
        function tokenForKey(k) {
            if (!k) return '';
            if (k === 'lambda') return '\\lambda';
            if (k === 'omega') return '\\omega';
            if (k === 'hbar') return '\\hbar';
            if (k === 'theta') return '\\theta';
            if (/^theta\d+$/.test(k)) return '\\theta_' + k.replace('theta', '');
            if (/^n\d+$/.test(k)) return 'n_' + k.substring(1);
            return k;
        }
        
        // Modified toggleBranchGroup with animation
        function toggleBranchGroup(key) {
            if (!key || !mmConfig || !mmConfig.groups || !mmConfig.groups[key]) return;
            
            var centerNode = document.getElementById('mm-center');
            var expanded = centerNode.getAttribute('data-expanded-' + key) === '1';
            var mapEl = document.getElementById('mindmap');
            
            if (expanded) {
                // Hide nodes with animation
                var nodesToRemove = Array.from(mapEl.querySelectorAll('.mm-node.child[data-parent-key="center"][data-group="' + key + '"]'));
                nodesToRemove.forEach(function(node, index) {
                    setTimeout(function() {
                        node.classList.remove('show');
                        setTimeout(function() {
                            if (node.parentNode) {
                                node.parentNode.removeChild(node);
                            }
                        }, 300);
                    }, index * 50);
                });
                
                centerNode.setAttribute('data-expanded-' + key, '0');
                setTimeout(function() {
                    layoutMindMap();
                }, nodesToRemove.length * 50 + 300);
                return;
            }
            
            // Show nodes with animation
            var branch = mmConfig.groups[key];
            branch.forEach(function(item, index) {
                setTimeout(function() {
                    var n = document.createElement('div');
                    n.className = 'mm-node child';
                    n.setAttribute('data-parent-key', 'center');
                    n.setAttribute('data-group', key);
                    n.setAttribute('data-angle', String(item.angle));
                    n.setAttribute('data-radius', String(item.radius));
                    
                    // Add tooltip data
                    if (item.description) {
                        n.setAttribute('data-tooltip', item.description);
                    }
                    
                    var inner = document.createElement('div');
                    try {
                        katex.render(item.latex || item.text || '', inner, {
                            throwOnError: false,
                            displayMode: false
                        });
                    } catch (e) {
                        inner.textContent = item.text || item.latex || '';
                    }
                    
                    n.appendChild(inner);
                    mapEl.appendChild(n);
                    
                    // Add hover events for tooltip
                    if (item.description) {
                        n.addEventListener('mouseenter', function() {
                            showTooltip(n, item.description);
                        });
                        n.addEventListener('mouseleave', hideTooltip);
                    }
                    
                    // Trigger animation
                    setTimeout(function() {
                        n.classList.add('show');
                    }, 50);
                    
                }, index * 100);
            });
            
            centerNode.setAttribute('data-expanded-' + key, '1');
            setTimeout(function() {
                layoutMindMap();
            }, branch.length * 100 + 100);
        }
        
        // Layout mindmap function
        function layoutMindMap() {
            var map = document.getElementById('mindmap');
            var center = document.getElementById('mm-center');
            var svg = document.getElementById('mm-links');
            
            if (!map || !center || !svg || !mmConfig) return;
            
            var w = map.clientWidth;
            var h = map.clientHeight;
            var nodes = Array.from(map.querySelectorAll('.mm-node:not(.child)'));
            
            nodes.forEach(function(n) {
                var px = parseFloat(n.getAttribute('data-x')) || 50;
                var py = parseFloat(n.getAttribute('data-y')) || 50;
                n.style.left = (w * px / 100) + 'px';
                n.style.top = (h * py / 100) + 'px';
            });
            
            svg.setAttribute('width', w);
            svg.setAttribute('height', h);
            
            while (svg.firstChild) svg.removeChild(svg.firstChild);
            
            var mapRect = map.getBoundingClientRect();
            var anchors = {};
            var anchorKeys = Object.keys(mmConfig.colors || {});
            
            for (var ai = 0; ai < anchorKeys.length; ai++) {
                var k = anchorKeys[ai];
                var el = center.querySelector('.key-' + k);
                if (!el) continue;
                var r = el.getBoundingClientRect();
                anchors[k] = {
                    x: (r.left - mapRect.left) + r.width / 2,
                    y: (r.top - mapRect.top) + r.height / 2
                };
            }
            
            var children = Array.from(map.querySelectorAll('.mm-node.child'));
            var groups = {};
            children.forEach(function(n) {
                var g = n.getAttribute('data-group');
                if (!g) return;
                if (!groups[g]) groups[g] = [];
                groups[g].push(n);
            });
            
            var sector = mmConfig.sectors || {};
            var colors = mmConfig.colors || {};
            var placed = [];

            function box(n) {
                return {
                    x: n.offsetLeft,
                    y: n.offsetTop,
                    w: n.offsetWidth,
                    h: n.offsetHeight
                };
            }

            function overlap(a, b, margin) {
                return Math.abs(a.x - b.x) < (a.w + b.w) / 2 + margin && Math.abs(a.y - b.y) < (a.h + b.h) / 2 + margin;
            }
            
            var margin = 10;
            Object.keys(groups).forEach(function(g) {
                var arr = groups[g];
                if (!arr || arr.length === 0) return;
                var conf = sector[g] || { start: -90, end: 90, base: 140 };
                var start = conf.start, end = conf.end;
                var base = conf.base;
                var anchor = anchors[g];
                var ax = anchor ? anchor.x : (center.offsetLeft + center.offsetWidth / 2);
                var ay = anchor ? anchor.y : (center.offsetTop + center.offsetHeight / 2);
                var col = colors[g] || '#111';
                
                for (var i = 0; i < arr.length; i++) {
                    var n = arr[i];
                    var t = (i + 1) / (arr.length + 1);
                    var angle = start + t * (end - start);
                    var radius = base + i * 36;
                    var attempts = 0;
                    var nx, ny;
                    
                    while (attempts < 120) {
                        nx = ax + Math.cos(angle * Math.PI / 180) * radius;
                        ny = ay + Math.sin(angle * Math.PI / 180) * radius;
                        n.style.left = nx + 'px';
                        n.style.top = ny + 'px';
                        n.style.borderColor = col;
                        n.style.color = col;
                        var bi = box(n);
                        var collided = false;
                        
                        for (var k = 0; k < placed.length; k++) {
                            if (overlap(bi, placed[k], margin)) {
                                collided = true;
                                break;
                            }
                        }
                        
                        if (!collided) break;
                        // push out and slightly rotate to avoid overlap
                        radius += 10;
                        angle += (attempts % 2 === 0 ? 6 : -6);
                        attempts++;
                    }
                    
                    placed.push(box(n));
                    var line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                    line.setAttribute('x1', ax);
                    line.setAttribute('y1', ay);
                    line.setAttribute('x2', nx);
                    line.setAttribute('y2', ny);
                    line.setAttribute('stroke', col);
                    line.setAttribute('stroke-opacity', '0.5');
                    line.setAttribute('stroke-width', '2');
                    svg.appendChild(line);
                }
            });
        }
        
        // Initialize on page load
        (function() {
            // Load initial formula
            loadFormula('emc2');
            
            // Set up resize observer
            var ro = new ResizeObserver(function() {
                layoutMindMap();
            });
            ro.observe(document.body);
            
            window.addEventListener('resize', function() {
                layoutMindMap();
            });
        })();
    </script>
    <script src="/assets/js/physics-bg.js"></script>
</body>

</html>