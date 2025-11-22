(function(){
  var mmConfig = window.mmConfig || {};
  var currentFormula = window.currentFormula || 'emc2';
  var formulaBase = window.formulaBase || '/cong-thuc/';
  var tooltip = null;
  var layoutLock = false;

  function loadFormula(formulaName){
    if (formulaName === currentFormula) return;
    window.location.href = formulaBase + formulaName;
  }

  function searchFormulas(searchTerm){
    if (!searchTerm){
      Array.from(document.querySelectorAll('.mm-node')).forEach(function(node){ node.style.display='block'; });
      return;
    }
    var term = String(searchTerm).toLowerCase();
    var visibleNodes = [];
    if (mmConfig && mmConfig.groups){
      Object.keys(mmConfig.groups).forEach(function(group){
        mmConfig.groups[group].forEach(function(item, index){
          var text = String(item.latex||item.text||'').toLowerCase();
          var name = String(item.name||'').toLowerCase();
          var description = String(item.description||'').toLowerCase();
          if (text.includes(term) || name.includes(term) || description.includes(term)){
            visibleNodes.push({group:group,index:index});
          }
        });
      });
    }
    Array.from(document.querySelectorAll('.mm-node')).forEach(function(node){
      var shouldShow = false;
      if (node.id === 'mm-center') shouldShow = true; else {
        var group = node.getAttribute('data-group');
        var nodeIndex = Array.from(node.parentNode.children).indexOf(node) - 1;
        for (var i=0;i<visibleNodes.length;i++){
          var v = visibleNodes[i];
          if (v.group === group && v.index === nodeIndex){ shouldShow = true; break; }
        }
      }
      node.style.display = shouldShow ? 'block' : 'none';
    });
  }

  

  function initializeMindmap(){
    if (window.MindElixir){ renderWithMindElixir(); return; }
    if (window.jsMind){ renderWithJsMind(); return; }
    var mapEl = document.getElementById('mindmap');
    var centerEl = document.getElementById('mm-center');
    Array.from(mapEl.querySelectorAll('.mm-node.child')).forEach(function(node){ node.remove(); });
    centerEl.setAttribute('data-expanded-E','0');
    centerEl.setAttribute('data-expanded-m','0');
    centerEl.setAttribute('data-expanded-c','0');
    renderCenterNode();
    setTimeout(function(){ centerEl.classList.add('show'); }, 100);
  }

  function renderCenterNode(){
    var centerEl = document.getElementById('mm-center');
    var centerLatexRaw = (mmConfig && mmConfig.centerLatex) ? mmConfig.centerLatex : 'E \\; = \\; mc^2';
    var dynamicKeys = Object.keys((mmConfig && mmConfig.colors) || {});
    var centerLatexWrapped = wrapCenterLatex(centerLatexRaw, dynamicKeys);
    try { katex.render(centerLatexWrapped, centerEl, { throwOnError:false, displayMode:false, trust:true, strict:false }); }
    catch(e){
      centerEl.textContent = '';
      try { katex.render(centerLatexRaw, centerEl, { throwOnError:false, displayMode:false }); }
      catch(err){ centerEl.textContent = centerLatexRaw; }
    }
    var colors = (mmConfig && mmConfig.colors) || {};
    Object.keys(colors).forEach(function(k){
      var el = centerEl.querySelector('.key-' + k);
      if (el) {
        el.style.color = colors[k];
      }
    });
    setTimeout(function(){ addCenterClickHandlers(); }, 500);
  }

  function addCenterClickHandlers(){
    var centerEl = document.getElementById('mm-center');
    var branch = (mmConfig && mmConfig.groups) ? mmConfig.groups : {};
    Object.keys(branch||{}).forEach(function(k){
      var el = centerEl.querySelector('.key-' + k);
      if (!el) return;
      el.style.cursor = 'pointer';
      el.addEventListener('click', function(){ toggleBranchGroup(k); });
    });
  }

  function wrapCenterLatex(latex, keys){
    if (!latex || !keys || !keys.length) return latex;
    var out = latex;
    for (var i=0;i<keys.length;i++){
      var key = keys[i];
      var tok = tokenForKey(key);
      if (!tok) continue;
      if (tok.charAt(0) === '\\'){
        var patternCmd = new RegExp(escapeRegex(tok), 'g');
        out = out.replace(patternCmd, function(m){ return '\\htmlClass{key-' + key + '}{' + m + '}'; });
      } else {
        if (tok.length === 1){
          var patternSingle = new RegExp('(^|[\\s\\{\\(\-])(' + escapeRegex(tok) + ')(?=$|[\\s\\}\\)])', 'g');
          out = out.replace(patternSingle, function(_, prefix, ch){ return (prefix || '') + '\\htmlClass{key-' + key + '}{' + ch + '}'; });
        } else {
          var patternWord = new RegExp('(^|[^\\a-zA-Z])(' + escapeRegex(tok) + ')(?=[^a-zA-Z]|$)', 'g');
          out = out.replace(patternWord, function(_, prefix, word){ return (prefix || '') + '\\htmlClass{key-' + key + '}{' + word + '}'; });
        }
      }
    }
    return out;
  }

  function escapeRegex(s){ return String(s).replace(/[.*+?^${}()|[\]\\]/g, '\\$&'); }

  function tokenForKey(k){
    if (!k) return '';
    if (k === 'lambda') return '\\lambda';
    if (k === 'omega') return '\\omega';
    if (k === 'hbar') return '\\hbar';
    if (k === 'theta') return '\\theta';
    if (/^theta\d+$/.test(k)) return '\\theta_' + k.replace('theta','');
    if (/^n\d+$/.test(k)) return 'n_' + k.substring(1);
    return k;
  }

  function toggleBranchGroup(key){
    if (!key || !mmConfig || !mmConfig.groups || !mmConfig.groups[key]) return;
    var centerNode = document.getElementById('mm-center');
    var expanded = centerNode.getAttribute('data-expanded-' + key) === '1';
    var mapEl = document.getElementById('mindmap');
    if (expanded){
      layoutLock = true;
      var nodesToRemove = Array.from(mapEl.querySelectorAll('.mm-node.child[data-parent-key="center"][data-group="' + key + '"]'));
      nodesToRemove.forEach(function(node, index){
        setTimeout(function(){
          node.classList.remove('show');
          setTimeout(function(){ if (node.parentNode) node.parentNode.removeChild(node); }, 300);
        }, index*50);
      });
      centerNode.setAttribute('data-expanded-' + key, '0');
      setTimeout(function(){ layoutLock = false; layoutMindMap(); }, nodesToRemove.length*50 + 300);
      return;
    }
    var branch = mmConfig.groups[key];
    layoutLock = true;
    var colInit = ((mmConfig && mmConfig.colors) ? mmConfig.colors[key] : '#111');
    branch.forEach(function(item, index){
      setTimeout(function(){
        var n = document.createElement('div');
        n.className = 'mm-node child';
        n.setAttribute('data-parent-key','center');
        n.setAttribute('data-group', key);
        n.setAttribute('data-angle', String(item.angle));
        n.setAttribute('data-radius', String(item.radius));
        if (item.description) n.setAttribute('data-tooltip', item.description);
        var inner = document.createElement('div');
        try { katex.render(item.latex || item.text || '', inner, { throwOnError:false, displayMode:false }); }
        catch(e){ inner.textContent = item.text || item.latex || ''; }
        n.appendChild(inner);
        n.style.borderColor = colInit;
        n.style.color = colInit;
        inner.style.color = colInit;
        mapEl.appendChild(n);
        
        setTimeout(function(){ n.classList.add('show'); }, 50);
      }, index*100);
    });
    centerNode.setAttribute('data-expanded-' + key, '1');
    setTimeout(function(){ layoutLock = false; layoutMindMap(); }, branch.length*100 + 120);
  }

  function layoutMindMap(){
    if (layoutLock) return;
    if (window.jsMind || window.MindElixir) return;
    var map = document.getElementById('mindmap');
    var center = document.getElementById('mm-center');
    var svg = document.getElementById('mm-links');
    if (!map || !center || !svg || !mmConfig) return;
    var w = map.clientWidth;
    var h = map.clientHeight;
    Array.from(map.querySelectorAll('.mm-node:not(.child)')).forEach(function(n){
      var px = parseFloat(n.getAttribute('data-x')) || 50;
      var py = parseFloat(n.getAttribute('data-y')) || 50;
      n.style.left = (w*px/100) + 'px';
      n.style.top = (h*py/100) + 'px';
    });
    svg.setAttribute('width', w);
    svg.setAttribute('height', h);
    while (svg.firstChild) svg.removeChild(svg.firstChild);
    var mapRect = map.getBoundingClientRect();
    var anchors = {};
    var anchorKeys = Object.keys(mmConfig.colors || {});
    for (var ai=0; ai<anchorKeys.length; ai++){
      var k = anchorKeys[ai];
      var el = center.querySelector('.key-' + k);
      if (!el) continue;
      var r = el.getBoundingClientRect();
      anchors[k] = { x:(r.left - mapRect.left) + r.width/2, y:(r.top - mapRect.top) + r.height/2 };
    }
    var children = Array.from(map.querySelectorAll('.mm-node.child'));
    var groups = {};
    children.forEach(function(n){ var g = n.getAttribute('data-group'); if (!g) return; if (!groups[g]) groups[g] = []; groups[g].push(n); });
    var sector = mmConfig.sectors || {};
    var colors = mmConfig.colors || {};
    var placed = [];
    function box(n){ return { x:n.offsetLeft, y:n.offsetTop, w:n.offsetWidth, h:n.offsetHeight }; }
    function overlap(a,b,margin){ return Math.abs(a.x-b.x) < (a.w+b.w)/2 + margin && Math.abs(a.y-b.y) < (a.h+b.h)/2 + margin; }
    var margin = 10;
    Object.keys(groups).forEach(function(g){
      var arr = groups[g];
      if (!arr || arr.length === 0) return;
      var conf = sector[g] || { start:-90, end:90, base:140 };
      var start = conf.start, end = conf.end, base = conf.base;
      var anchor = anchors[g];
      var ax = anchor ? anchor.x : (center.offsetLeft + center.offsetWidth/2);
      var ay = anchor ? anchor.y : (center.offsetTop + center.offsetHeight/2);
      var col = colors[g] || '#111';
      for (var i=0;i<arr.length;i++){
        var n = arr[i];
        var t = (i+1)/(arr.length+1);
        var angle = start + t*(end-start);
        var radius = base + i*36;
        var attempts = 0; var nx, ny;
        while (attempts < 120){
          nx = ax + Math.cos(angle*Math.PI/180)*radius;
          ny = ay + Math.sin(angle*Math.PI/180)*radius;
          n.style.left = nx + 'px';
          n.style.top = ny + 'px';
          n.style.borderColor = col;
          n.style.color = col;
          var bi = box(n); var collided = false;
          for (var k=0;k<placed.length;k++){ if (overlap(bi, placed[k], margin)) { collided = true; break; } }
          if (!collided) break;
          radius += 10; angle += (attempts % 2 === 0 ? 6 : -6); attempts++;
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

  window.loadFormula = loadFormula;
  window.searchFormulas = searchFormulas;
  window.initializeMindmap = initializeMindmap;

  var ro = new ResizeObserver(function(){ if (!layoutLock) layoutMindMap(); });
  var mapEl = document.getElementById('mindmap');
  if (mapEl) ro.observe(mapEl);
  window.addEventListener('resize', function(){ layoutMindMap(); });
  initializeMindmap();

  function renderWithJsMind(){
    var container = document.getElementById('mindmap');
    if (!container) return;
    container.innerHTML = '';
    var options = {
      container: 'mindmap',
      editable: true,
      theme: 'primary',
      support_html: true,
      view: { engine: 'canvas', line_style: 'curved' }
    };
    var jm = new jsMind(options);
    window.__jm = jm;
    var openedGroupsJM = {};
    function toHTML(latex){
      try { return katex.renderToString(String(latex||''), { throwOnError:false, displayMode:false, trust:true, strict:false }); }
      catch(e){ return String(latex||''); }
    }
    var colors = (mmConfig && mmConfig.colors) || {};
    var groups = (mmConfig && mmConfig.groups) || {};
    var keysCenter = Object.keys(colors);
    var centerRawJM = (mmConfig && mmConfig.centerLatex) ? mmConfig.centerLatex : 'E \\; = \\; mc^2';
    var centerWrappedJM = wrapCenterLatex(centerRawJM, keysCenter);
    var root = { id:'root', topic: toHTML(centerWrappedJM), children: [] };
    // Chỉ hiển thị node gốc ban đầu
    var mind = { meta: { name:'physic.site', version:'1.0' }, format:'node_tree', data: root };
    jm.show(mind);
    jm.set_editable(false);

    // Gắn click vào các key trong node gốc để mở/đóng nhánh tương ứng
    setTimeout(function(){
      var rootEl = container.querySelector('.jsmind-node');
      var keys = Object.keys(colors);
      keys.forEach(function(k){
        var anchors = container.querySelectorAll('.key-' + k);
        anchors.forEach(function(el){
          el.style.cursor = 'pointer';
          el.addEventListener('click', function(e){ e.preventDefault(); e.stopPropagation(); toggleBranchJM(k); });
          el.addEventListener('dblclick', function(e){ e.preventDefault(); e.stopPropagation(); });
        });
      });
      container.addEventListener('dblclick', function(e){ e.preventDefault(); e.stopPropagation(); }, true);
    }, 200);

    function toggleBranchJM(k){
      var col = colors[k] || '#111';
      var grpId = 'grp-' + k;
      if (openedGroupsJM[k]){
        jm.set_editable(true);
        // remove children then group
        var arr = groups[k] || [];
        for (var idx=0; idx<arr.length; idx++){
          var cid = 'g-' + k + '-' + idx;
          jm.remove_node(cid);
        }
        jm.remove_node(grpId);
        jm.set_editable(false);
        openedGroupsJM[k] = false;
        return;
      }
      // create group node under root
      jm.set_editable(true);
      var okGrp = jm.add_node('root', grpId, toHTML(tokenForKey(k)), { 'foreground-color': col, 'line-color': col, 'leading-line-color': col });
      if (!okGrp) { return; }
      // create children
      var arr2 = groups[k] || [];
      for (var i=0; i<arr2.length; i++){
        var cid2 = 'g-' + k + '-' + i;
        var okChild = jm.add_node(grpId, cid2, toHTML(arr2[i].latex || arr2[i].text || ''), { 'foreground-color': col, 'line-color': col, 'leading-line-color': col });
        if (!okChild) { /* skip if failed */ }
      }
      jm.set_editable(false);
      openedGroupsJM[k] = true;
    }
  }
  if (typeof renderWithJsMind === 'function') { window.renderWithJsMind = renderWithJsMind; }

  function renderWithMindElixir(){
    var container = document.getElementById('mindmap');
    if (!container) return;
    container.innerHTML = '';
    if (!container.style.height) container.style.height = '520px';
    var options = { el: '#mindmap', toolBar: false, nodeMenu: false, keypress: false, draggable: false, overflowHidden: false };
    var me = new MindElixir(options);
    window.__me = me;
    function toHTML(latex){
      try { return katex.renderToString(String(latex||''), { throwOnError:false, displayMode:false, trust:true, strict:false }); }
      catch(e){ return String(latex||''); }
    }
    var colors = (mmConfig && mmConfig.colors) || {};
    var groups = (mmConfig && mmConfig.groups) || {};
    var keysCenter = Object.keys(colors);
    var centerRaw = (mmConfig && mmConfig.centerLatex) ? mmConfig.centerLatex : 'E \\; = \\; mc^2';
    var centerWrapped = wrapCenterLatex(centerRaw, keysCenter);
    var data = { nodeData: { id: 'me-root', topic: '', dangerouslySetInnerHTML: toHTML(centerWrapped), expanded: true, children: [] } };
    me.init(data);
    function bindRootAnchors(){
      keysCenter.forEach(function(k){
        Array.from(container.querySelectorAll('.key-' + k)).forEach(function(el){
          el.style.cursor = 'pointer';
          el.addEventListener('click', function(e){ e.preventDefault(); e.stopPropagation(); toggleBranchME(k); });
          el.addEventListener('dblclick', function(e){ e.preventDefault(); e.stopPropagation(); });
        });
      });
      container.addEventListener('dblclick', function(e){ e.preventDefault(); e.stopPropagation(); }, true);
    }
    setTimeout(bindRootAnchors, 200);
    var openedGroupsME = {};
    function toggleBranchME(k){
      var col = colors[k] || '#111';
      var d = me.getData();
      var root = d.nodeData;
      var idx = (root.children||[]).findIndex(function(c){ return c && c.id === ('grp-'+k); });
      if (idx >= 0){
        root.children.splice(idx, 1);
        openedGroupsME[k] = false;
        me.refresh(d);
        setTimeout(bindRootAnchors, 50);
        return;
      }
      var grp = { id: 'grp-'+k, topic: '', dangerouslySetInnerHTML: toHTML(tokenForKey(k)), branchColor: col, expanded: true, children: [] };
      var arr = groups[k] || [];
      for (var i=0;i<arr.length;i++){
        grp.children.push({ id: 'g-'+k+'-'+i, topic: '', dangerouslySetInnerHTML: toHTML(arr[i].latex || arr[i].text || ''), style: { color: col } });
      }
      root.children = root.children || [];
      root.children.push(grp);
      openedGroupsME[k] = true;
      me.refresh(d);
      setTimeout(bindRootAnchors, 50);
    }
  }
  if (typeof renderWithMindElixir === 'function') { window.renderWithMindElixir = renderWithMindElixir; }
})();
