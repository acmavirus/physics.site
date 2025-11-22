(function(){
  var container = document.getElementById('wordcloud');
  var phpWords = window.phpWords || null;
  var phpText = window.phpText || null;
  var categoryBase = window.categoryBase || '/danh-muc/';

  function parseWords(raw){
    if (Array.isArray(raw)) return raw.map(function(w){
      if (typeof w === 'string') return { text: w, size: 1 + Math.random() };
      if (w && typeof w === 'object') return { text: w.text || w.word || '', size: w.size || w.weight || (1 + Math.random()), slug: w.slug };
      return { text: String(w), size: 1 + Math.random() };
    });
    return [
      { text:'Vật lý', size:9 }, { text:'Cơ học', size:7 }, { text:'Điện từ', size:6 }, { text:'Quang học', size:5.5 },
      { text:'Nhiệt học', size:5 }, { text:'Lượng tử', size:6.5 }, { text:'Tương đối', size:4.5 }, { text:'Năng lượng', size:4 },
      { text:'Trường', size:3.5 }, { text:'Lực', size:3 }, { text:'Khối lượng', size:5 }, { text:'Động lượng', size:4 },
      { text:'Dao động', size:3.5 }, { text:'Sóng', size:5.5 }, { text:'Tần số', size:3 }, { text:'Vận tốc', size:4.5 }
    ];
  }

  function buildWordsFromText(text){
    if (!text || typeof text !== 'string') return null;
    var sw = new Set("i,me,my,myself,we,us,our,ours,ourselves,you,your,yours,yourself,yourselves,he,him,his,himself,she,her,hers,herself,it,its,itself,they,them,their,theirs,themselves,what,which,who,whom,whose,this,that,these,those,am,is,are,was,were,be,been,being,have,has,had,having,do,does,did,doing,will,would,should,can,could,ought,i'm,you're,he's,she's,it's,we're,they're,i've,you've,we've,they've,i'd,you'd,he'd,she'd,we'd,they'd,i'll,you'll,he'll,she'll,we'll,they'll,isn't,aren't,wasn't,weren't,hasn't,haven't,hadn't,doesn't,don't,didn't,won't,wouldn't,shan't,shouldn't,can't,cannot,couldn't,mustn't,let's,that's,who's,what's,here's,there's,when's,where's,why's,how's,a,an,the,and,but,if,or,because,as,until,while,of,at,by,for,with,about,against,between,into,through,during,before,after,above,below,to,from,up,upon,down,in,out,on,off,over,under,again,further,then,once,here,there,when,where,why,how,all,any,both,each,few,more,most,other,some,such,no,nor,not,only,own,same,so,than,too,very,say,says,said,shall".split(","));
    var vn = new Set(["và","của","là","một","những","các","trong","trên","dưới","với","để","khi","đã","đang","sẽ","không","có","như","cũng","nhưng","thì","vì","tại","từ","cho","này","kia","đó","hay"]);
    var tokens = (String(text).match(/[\p{L}\p{N}]+/gu) || []).map(function(t){ return t.toLowerCase(); });
    var counts = new Map();
    for (var i=0;i<tokens.length;i++){
      var t = tokens[i]; if (t.length<2) continue; if (sw.has(t) || vn.has(t)) continue; counts.set(t, (counts.get(t)||0)+1);
    }
    var arr = Array.from(counts.entries()).map(function(d){ return { text:d[0], value:d[1] }; });
    arr.sort(function(a,b){ return b.value - a.value });
    arr = arr.slice(0,200);
    var values = arr.map(function(d){ return d.value; });
    var s = d3.scaleSqrt().domain([d3.min(values), d3.max(values)]).range([12,90]);
    return arr.map(function(d){ return { text:d.text, size:s(d.value) }; });
  }

  var words = phpWords ? parseWords(phpWords) : buildWordsFromText(phpText);
  if (!words) words = parseWords(null);
  var baseSizes = words.map(function(d){ return d.size || 1 });
  var scale = d3.scaleSqrt().domain([d3.min(baseSizes), d3.max(baseSizes)]).range([12,90]);
  words = words.map(function(d){ return { text:d.text, size: scale(d.size||1), slug:d.slug }; });

  function slugify(str){
    return String(str).normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
  }

  function render(){
    var width = container.clientWidth; var height = container.clientHeight;
    container.innerHTML = '';
    var svg = d3.select(container).append('svg').attr('width', width).attr('height', height);
    d3.layout.cloud().size([width, height]).words(words).padding(1).rotate(function(){ return 0 }).font('Noto Sans,STIX Two Text,system-ui,-apple-system,Segoe UI,Roboto,Helvetica Neue,Arial').fontSize(function(d){ return d.size }).spiral('rectangular').on('end', function(layoutWords){
      var g = svg.append('g').attr('transform', 'translate(' + width/2 + ',' + height/2 + ')');
      var t = function(d){ return 'translate(' + d.x + ',' + d.y + ') rotate(' + d.rotate + ')'; };
      var activeNode = null; var activeDatum = null;
      g.selectAll('text').data(layoutWords).enter().append('text').attr('text-anchor', 'middle').style('font-size', function(d){ return d.size + 'px' }).style('font-family', 'Noto Sans,STIX Two Text,system-ui,-apple-system,Segoe UI,Roboto,Helvetica Neue,Arial').style('font-weight', '800').style('fill', function(){ return '#000' }).style('cursor', 'pointer').style('paint-order', 'stroke fill').attr('transform', function(d){ return t(d) }).text(function(d){ return d.text }).on('mouseover', function(event, d){
        if (activeNode && activeNode !== this){ d3.select(activeNode).interrupt().transition().duration(80).attr('transform', t(activeDatum)); }
        activeNode = this; activeDatum = d; var sel = d3.select(this); sel.raise(); sel.interrupt(); sel.style('fill', 'rgba(247, 139, 139, 1)').style('stroke-width', '2px'); sel.transition().duration(220).ease(d3.easeCubicOut).attr('transform', t(d) + ' scale(1.12)');
      }).on('mouseout', function(event, d){ if (this !== activeNode) return; var sel = d3.select(this); sel.interrupt(); sel.style('fill', '#000').style('stroke-width', '0px'); sel.transition().duration(200).ease(d3.easeCubicOut).attr('transform', t(d)); activeNode = null; activeDatum = null; }).on('click', function(event, d){ var slug = d.slug || slugify(d.text); window.location.href = categoryBase + slug; });
    }).start();
  }

  render();
  var ro = new ResizeObserver(function(){ render(); });
  ro.observe(container);
})();
