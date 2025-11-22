(function(){
  var container = document.getElementById('wordcloud');
  var phpItems = window.phpItems || [];
  var detailBase = window.detailBase || '/cong-thuc/';

  function parseFormulaItems(raw){
    if (!Array.isArray(raw)) return [];
    return raw.map(function(w){
      if (typeof w === 'string') return { name:w, latex:'', slug:'' };
      return { name: w.name || w.text || '', latex: w.latex || '', slug: w.slug || '' };
    });
  }

  var items = parseFormulaItems(phpItems);
  var baseSizes = items.map(function(it){ return ((it.latex||it.name||'').length)||1 });
  var scale = d3.scaleSqrt().domain([d3.min(baseSizes), d3.max(baseSizes)]).range([14, 56]);
  var MIN_SIZE = 14, MAX_SIZE = 56;
  var words = items.map(function(it){ var t = it.latex || it.name || ''; var sz = scale(t.length||1); sz = Math.max(MIN_SIZE, Math.min(MAX_SIZE, sz)); return { text:t, size:sz, latex:it.latex||'', slug:it.slug||'' }; });

  function slugify(str){
    return String(str).normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
  }

  function render(){
    var width = container.clientWidth;
    var height = container.clientHeight;
    container.innerHTML = '';
    d3.layout.cloud().size([width, height]).words(words).padding(4).rotate(function(){ return 0 }).font('Noto Sans,STIX Two Text,system-ui,-apple-system,Segoe UI,Roboto,Helvetica Neue,Arial').fontSize(function(d){ return d.size }).spiral('rectangular').on('end', function(layoutWords){
      var layer = document.createElement('div');
      layer.style.position = 'absolute'; layer.style.left='0'; layer.style.top='0'; layer.style.width = width + 'px'; layer.style.height = height + 'px';
      container.appendChild(layer);
      var activeNode = null; var activeRotateMap = new WeakMap();
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
          if(activeNode && activeNode !== node){ var r = activeRotateMap.get(activeNode) || 0; activeNode.style.transform = 'translate(-50%,-50%) rotate('+r+'deg)'; activeNode.style.color = '#000'; }
          activeNode = node; node.style.color = 'rgba(247,139,139,1)'; node.style.transform = 'translate(-50%,-50%) rotate('+d.rotate+'deg) scale(1.12)';
        });
        node.addEventListener('mouseout', function(){ node.style.color = '#000'; node.style.transform = 'translate(-50%,-50%) rotate('+d.rotate+'deg)'; if(activeNode === node) activeNode = null; });
        var inner = document.createElement('div');
        inner.style.whiteSpace = 'nowrap'; inner.style.display = 'inline-block'; inner.style.lineHeight = '1';
        var latexText = (d.latex || d.text || '').replace(/\n+/g, ' ');
        try { katex.render(latexText, inner, { throwOnError: false, displayMode: false }); } catch(e) { inner.textContent = latexText; }
        node.appendChild(inner);
        layer.appendChild(node);
      });
      (function(){
        var margin = 6; var nodes = Array.from(layer.children); var cx = width/2, cy = height/2;
        function box(n){ var w=n.offsetWidth,h=n.offsetHeight; var x=parseFloat(n.style.left)||0; var y=parseFloat(n.style.top)||0; return {x:x,y:y,w:w,h:h}; }
        function overlap(a,b){ return Math.abs(a.x-b.x) < (a.w+b.w)/2 + margin && Math.abs(a.y-b.y) < (a.h+b.h)/2 + margin; }
        for (var i=0;i<nodes.length;i++){
          var ni = nodes[i]; var bi = box(ni); var attempts=0;
          while (attempts<160){
            var collided=false;
            for (var j=0;j<i;j++){ var bj = box(nodes[j]); if (overlap(bi,bj)){ collided=true; var vx=bi.x-cx, vy=bi.y-cy; var len=Math.max(8, Math.sqrt(vx*vx+vy*vy)); var step=8; var nx=bi.x+(vx/len)*step; var ny=bi.y+(vy/len)*step; nx = Math.min(width - bi.w/2 - margin, Math.max(bi.w/2 + margin, nx)); ny = Math.min(height - bi.h/2 - margin, Math.max(bi.h/2 + margin, ny)); ni.style.left = nx + 'px'; ni.style.top = ny + 'px'; bi = box(ni); }
            }
            if (!collided) break; attempts++;
          }
          if (attempts>=160){ ni.style.transform = ni.style.transform + ' scale(0.92)'; }
        }
      })();
    }).start();
  }

  render();
  var ro = new ResizeObserver(function(){ render(); });
  ro.observe(container);
})();
