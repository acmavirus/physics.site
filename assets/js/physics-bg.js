(function () {
  function drawBackground() {
    var c = document.getElementById("physics-bg");
    if (!c) return;
    var dpr = window.devicePixelRatio || 1;
    var w = window.innerWidth;
    var h = window.innerHeight;
    c.width = w * dpr;
    c.height = h * dpr;
    c.style.width = w + "px";
    c.style.height = h + "px";
    var ctx = c.getContext("2d");
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    ctx.clearRect(0, 0, w, h);
    var grd = ctx.createLinearGradient(0, 0, 0, h);
    grd.addColorStop(0, "#ffffff");
    grd.addColorStop(1, "#f7fafc");
    ctx.fillStyle = grd;
    ctx.fillRect(0, 0, w, h);
    ctx.strokeStyle = "rgba(0,0,0,0.06)";
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
    var eq = [
      "E = mc²",
      "F = ma",
      "Δx·Δp ≥ ℏ/2",
      "v = s/t",
      "λ = c/f",
      "a = dv/dt",
      "∫ F·ds",
      "Σ F = 0",
    ];
    ctx.fillStyle = "rgba(0,0,0,0.12)";
    ctx.font = "700 28px STIX Two Text, Noto Sans, system-ui";
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
  drawBackground();
  var ro = new ResizeObserver(function () {
    drawBackground();
  });
  ro.observe(document.body);
  window.addEventListener("resize", drawBackground);
})();
