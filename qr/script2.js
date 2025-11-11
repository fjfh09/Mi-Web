const $ = (id) => document.getElementById(id);

// ===== ELEMENTOS =====
const canvas = $("canvas");
const ctx = canvas.getContext("2d");
const thumb = $("thumb");
const tctx = thumb.getContext("2d");

const inputs = {
  text: $("text"),
  size: $("size"),
  margin: $("margin"),
  ec: $("ec"),
  shape: $("shape"),
  shapeScale: $("shapeScale"),
  roundedRadius: $("roundedRadius"),
  bgColor: $("bgColor"),
  bgColorPicker: $("bgColorPicker"),
  bgColorOpacity: $("bgColorOpacity"),
  color1: $("color1"),
  color1Picker: $("color1Picker"),
  color2: $("color2"),
  color2Picker: $("color2Picker"),
  gradientType: $("gradientType"),
  gradientAngle: $("gradientAngle"),
  logoFile: $("logoFile"),
  logoThumb: $("logoThumb"),
  logoSize: $("logoSize"),
  logoBg: $("logoBg"),
  logoBgPicker: $("logoBgPicker"),
  logoBgOpacity: $("logoBgOpacity"),
  genBtn: $("genBtn"),
  dlPng: $("dlPng"),
  dlSvg: $("dlSvg"),
  meta: $("meta"),
  sizeInfo: $("sizeInfo"),
  zoomIn: $("zoomIn"),
  zoomOut: $("zoomOut"),
};

let logoImage = null;
let zoomScale = 1;

// ===== SINCRONIZAR PICKERS =====
function syncColor(input, picker) {
  picker.addEventListener("input", () => (input.value = picker.value));
  input.addEventListener("input", () => {
    if (/^#[0-9A-Fa-f]{6}$/.test(input.value)) picker.value = input.value;
  });
}
syncColor(inputs.bgColor, inputs.bgColorPicker);
syncColor(inputs.color1, inputs.color1Picker);
syncColor(inputs.color2, inputs.color2Picker);
syncColor(inputs.logoBg, inputs.logoBgPicker);

// ===== CARGA LOGO =====
inputs.logoFile.addEventListener("change", (e) => {
  const file = e.target.files[0];
  if (!file) {
    logoImage = null;
    inputs.logoThumb.style.display = "none";
    return;
  }
  const url = URL.createObjectURL(file);
  const img = new Image();
  img.onload = () => {
    logoImage = img;
    inputs.logoThumb.src = url;
    inputs.logoThumb.style.display = "inline-block";
    URL.revokeObjectURL(url);
    renderQR(collectOptions());
  };
  img.src = url;
});

// ===== FUNCIONES AUXILIARES =====
function makeQRMatrix(text, ecLevel) {
  text = text.trim();
  if (!text) return null;
  const EC = ["L", "M", "Q", "H"].includes(ecLevel) ? ecLevel : "M";
  try {
    const qr = qrcode(0, EC);
    qr.addData(text);
    qr.make();
    const size = qr.getModuleCount();
    const matrix = [];
    for (let r = 0; r < size; r++) {
      const row = [];
      for (let c = 0; c < size; c++) row.push(qr.isDark(r, c));
      matrix.push(row);
    }
    return matrix;
  } catch (e) {
    console.error(e);
    return null;
  }
}

function roundedRectPath(ctx, x, y, w, h, r) {
  const rr = Math.min(r, w / 2, h / 2);
  ctx.moveTo(x + rr, y);
  ctx.arcTo(x + w, y, x + w, y + h, rr);
  ctx.arcTo(x + w, y + h, x, y + h, rr);
  ctx.arcTo(x, y + h, x, y, rr);
  ctx.arcTo(x, y, x + w, y, rr);
  ctx.closePath();
}

function createFill(ctx, x, y, w, h, type, c1, c2, angle) {
  if (type === "none" || c1 === c2) {
    ctx.fillStyle = c1;
    return;
  }
  if (type === "linear") {
    const a = (angle * Math.PI) / 180;
    const cx = x + w / 2,
      cy = y + h / 2,
      len = Math.max(w, h);
    const g = ctx.createLinearGradient(
      cx - Math.cos(a) * len,
      cy - Math.sin(a) * len,
      cx + Math.cos(a) * len,
      cy + Math.sin(a) * len
    );
    g.addColorStop(0, c1);
    g.addColorStop(1, c2);
    ctx.fillStyle = g;
  } else {
    const g = ctx.createRadialGradient(
      x + w / 2,
      y + h / 2,
      0,
      x + w / 2,
      y + h / 2,
      Math.max(w, h)
    );
    g.addColorStop(0, c1);
    g.addColorStop(1, c2);
    ctx.fillStyle = g;
  }
}

// ===== RENDER QR =====
function renderQR(options) {
  const matrix = makeQRMatrix(options.text, options.ec);
  if (!matrix) return;

  const modules = matrix.length;
  const margin = Number(options.margin) || 4;
  const totalModules = modules + margin * 2;
  const canvasSize = options.size;
  const dpr = window.devicePixelRatio || 1;

  canvas.width = canvasSize * dpr;
  canvas.height = canvasSize * dpr;
  canvas.style.width = canvasSize + "px";
  canvas.style.height = canvasSize + "px";
  ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
  ctx.imageSmoothingEnabled = false;

  // ===== FONDO QR =====
  ctx.clearRect(0, 0, canvasSize, canvasSize);
  if (options.bgColor && Number(options.bgColorOpacity) > 0) {
    ctx.globalAlpha = Number(options.bgColorOpacity);
    ctx.fillStyle = options.bgColor;
    ctx.fillRect(0, 0, canvasSize, canvasSize);
    ctx.globalAlpha = 1;
  }
  // si bgColorOpacity = 0, canvas queda transparente

  const modulePx = canvasSize / totalModules;
  const drawX = margin * modulePx;

  // ===== MODULOS QR =====
  for (let r = 0; r < modules; r++) {
    for (let c = 0; c < modules; c++) {
      if (!matrix[r][c]) continue;
      const x = drawX + c * modulePx,
        y = drawX + r * modulePx,
        s = modulePx;
      ctx.beginPath();
      const pad = (s - s * options.shapeScale) / 2;
      const cx = x + s / 2,
        cy = y + s / 2;
      const r0 = (s * options.shapeScale) / 2;

      switch (options.shape) {
        case "square":
          ctx.rect(x, y, s, s);
          break;
        case "rounded":
          roundedRectPath(
            ctx,
            x + pad,
            y + pad,
            s * options.shapeScale,
            s * options.shapeScale,
            Number(options.roundedRadius) * s
          );
          break;
        case "circle":
          ctx.arc(cx, cy, r0, 0, Math.PI * 2);
          break;
        case "diamond":
          ctx.moveTo(cx, cy - r0);
          ctx.lineTo(cx + r0, cy);
          ctx.lineTo(cx, cy + r0);
          ctx.lineTo(cx - r0, cy);
          ctx.closePath();
          break;
        case "heart":
    const scale = r0 / 0.7; // ajusta tamaño relativo del corazón
    ctx.moveTo(cx, cy + scale * 0.6);
    ctx.bezierCurveTo(
        cx - scale, cy - scale * 0.2,  // punto de control izquierdo
        cx - scale * 0.6, cy - scale,  // parte superior izquierda
        cx, cy - scale * 0.3           // parte superior centro
    );
    ctx.bezierCurveTo(
        cx + scale * 0.6, cy - scale,  // parte superior derecha
        cx + scale, cy - scale * 0.2,  // punto de control derecho
        cx, cy + scale * 0.6           // punta inferior
    );
    ctx.closePath();
    break;

        case "triangle":
          ctx.moveTo(cx, cy - r0);
          ctx.lineTo(cx + r0, cy + r0);
          ctx.lineTo(cx - r0, cy + r0);
          ctx.closePath();
          break;
        case "star":
          const spikes = 5;
          let rot = (Math.PI / 2) * 3;
          let step = Math.PI / spikes;
          ctx.moveTo(cx, cy - r0);
          for (let i = 0; i < spikes; i++) {
            let x1 = cx + Math.cos(rot) * r0;
            let y1 = cy + Math.sin(rot) * r0;
            ctx.lineTo(x1, y1);
            rot += step;

            x1 = cx + Math.cos(rot) * (r0 / 2);
            y1 = cy + Math.sin(rot) * (r0 / 2);
            ctx.lineTo(x1, y1);
            rot += step;
          }
          ctx.lineTo(cx, cy - r0);
          ctx.closePath();
          break;
        case "dots":
          ctx.arc(cx, cy, r0 / 2, 0, Math.PI * 2);
          break;
      }

      ctx.globalAlpha = Number(options.color1Opacity ?? 1);

// Usa el degradado o color uniforme
createFill(
  ctx,
  drawX,
  drawX,
  modulePx * modules,
  modulePx * modules,
  options.gradientType,
  options.color1,
  options.color2,
  Number(options.gradientAngle)
);

ctx.fill();
ctx.globalAlpha = 1;

    }
  }

  // ===== LOGO =====
  if (logoImage) {
    const logoPct = Math.max(6, Math.min(48, Number(options.logoSize) || 20));
    const logoPxLogical = Math.floor(canvasSize * (logoPct / 100));
    const targetPx = Math.round(logoPxLogical * dpr);

    const tmp = document.createElement("canvas");
    tmp.width = tmp.height = Math.max(1, targetPx);
    const t = tmp.getContext("2d");
    t.imageSmoothingEnabled = true;

    // Fondo logo solo si logoBgOpacity > 0
    if (options.logoBg && Number(options.logoBgOpacity) > 0) {
      t.globalAlpha = Number(options.logoBgOpacity);
      t.fillStyle = options.logoBg;
      t.fillRect(0, 0, tmp.width, tmp.height);
      t.globalAlpha = 1;
    } else {
      t.clearRect(0, 0, tmp.width, tmp.height);
    }

    const iw = logoImage.width,
      ih = logoImage.height;
    const ratio = Math.min(tmp.width / iw, tmp.height / ih);
    const drawW = Math.round(iw * ratio);
    const drawH = Math.round(ih * ratio);
    const ox = Math.round((tmp.width - drawW) / 2);
    const oy = Math.round((tmp.height - drawH) / 2);

    t.drawImage(logoImage, ox, oy, drawW, drawH);

    const dx_log = Math.round((canvasSize - logoPxLogical) / 2);
    const dy_log = dx_log;
    const dx_px = Math.floor(dx_log * dpr);
    const dy_px = Math.floor(dy_log * dpr);

    ctx.drawImage(
      tmp,
      0,
      0,
      tmp.width,
      tmp.height,
      dx_px / dpr,
      dy_px / dpr,
      tmp.width / dpr,
      tmp.height / dpr
    );
  }

  // ===== THUMBNAIL =====
  tctx.clearRect(0, 0, thumb.width, thumb.height);
  tctx.drawImage(canvas, 0, 0, thumb.width, thumb.height);

  // ===== INFO =====
  $(
    "meta"
  ).textContent = `modulos: ${modules} | margen: ${margin} | tamano: ${canvasSize}px | shape: ${options.shape}`;
  $(
    "sizeInfo"
  ).textContent = `canvas ${canvasSize} x ${canvasSize}px (dpr ${dpr})`;
}

// ===== OPCIONES =====
function collectOptions() {
  const size = Math.min(1000, Number(inputs.size.value) || 800);
  const logoSize = Math.min(
    30,
    Math.max(6, Number(inputs.logoSize.value) || 20)
  );
  const shapeScale = Math.min(
    1,
    Math.max(0.6, Number(inputs.shapeScale.value) || 0.7)
  );
  const roundedRadius = Math.min(
    0.5,
    Math.max(0, Number(inputs.roundedRadius.value) || 0.15)
  );

  inputs.size.value = size;
  inputs.logoSize.value = logoSize;
  inputs.shapeScale.value = shapeScale;
  inputs.roundedRadius.value = roundedRadius;

  return {
    text: inputs.text.value || "",
    size,
    margin: Number(inputs.margin.value) || 4,
    ec: inputs.ec.value || "M",
    shape: inputs.shape.value || "square",
    shapeScale,
    roundedRadius,
    bgColor: inputs.bgColor.value || "#ffffff",
    bgColorOpacity: parseFloat(inputs.bgColorOpacity?.value) || 0,
    color1: inputs.color1.value || "#000000",
    color2: inputs.color2.value || "#000000",
    gradientType: inputs.gradientType.value || "none",
    gradientAngle: Number(inputs.gradientAngle.value) || 0,
    logoSize,
    logoBg: inputs.logoBg.value || "#ffffff",
    logoBgOpacity: parseFloat(inputs.logoBgOpacity?.value) || 0,
  };
}

// ===== DESCARGA =====
function downloadURI(uri, name) {
  const a = document.createElement("a");
  a.href = uri;
  a.download = name;
  document.body.appendChild(a);
  a.click();
  a.remove();
}

inputs.genBtn.addEventListener("click", () => renderQR(collectOptions()));
inputs.dlPng.addEventListener("click", () => {
  renderQR(collectOptions());
  downloadURI(canvas.toDataURL("image/png"), "qr.png");
});
inputs.dlSvg.addEventListener("click", () => {
  const opts = collectOptions();
  const matrix = makeQRMatrix(opts.text, opts.ec);
  if (!matrix) return alert("Error generando QR");
  const size = opts.size;
  const margin = opts.margin;
  const totalModules = matrix.length + margin * 2;
  const modulePx = size / totalModules;

  let svg = `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}">`;
  if (opts.bgColorOpacity > 0)
    svg += `<rect width="100%" height="100%" fill="${opts.bgColor}" fill-opacity="${opts.bgColorOpacity}"/>`;
  svg += `<g fill="${opts.color1}">`;
  for (let r = 0; r < matrix.length; r++) {
    for (let c = 0; c < matrix.length; c++) {
      if (matrix[r][c]) {
        const x = (c + margin) * modulePx,
          y = (r + margin) * modulePx;
        svg += `<rect x="${x}" y="${y}" width="${modulePx}" height="${modulePx}"/>`;
      }
    }
  }
  svg += `</g></svg>`;
  downloadURI(
    "data:image/svg+xml;charset=utf-8," + encodeURIComponent(svg),
    "qr.svg"
  );
});

// ===== ZOOM =====
inputs.zoomIn.addEventListener("click", () => {
  zoomScale = Math.min(1, zoomScale + 0.1);
  canvas.style.transform = `scale(${zoomScale})`;
});
inputs.zoomOut.addEventListener("click", () => {
  zoomScale = Math.max(0.6, zoomScale - 0.1);
  canvas.style.transform = `scale(${zoomScale})`;
});

// ===== TECLAS RAPIDAS =====
inputs.text.addEventListener("keydown", (e) => {
  if ((e.ctrlKey || e.metaKey) && e.key === "Enter") renderQR(collectOptions());
});

// ===== AUTOSAVE =====
const STORAGE_KEY = "qr_avanzado_v1";

function saveState() {
  const opts = collectOptions();
  localStorage.setItem(STORAGE_KEY, JSON.stringify(opts));
}

function loadState() {
  try {
    const s = JSON.parse(localStorage.getItem(STORAGE_KEY) || "{}");
    for (const key in s) {
      if (inputs[key]) {
        if (
          [
            "bgColorOpacity",
            "logoBgOpacity",
            "size",
            "margin",
            "logoSize",
            "shapeScale",
            "roundedRadius",
            "gradientAngle",
          ].includes(key)
        ) {
          inputs[key].value = parseFloat(s[key]);
        } else {
          inputs[key].value = s[key];
        }
      }
    }
    if (s.bgColorOpacity === undefined) inputs.bgColorOpacity.value = 1;
    if (s.logoBgOpacity === undefined) inputs.logoBgOpacity.value = 1;
  } catch (e) {
    console.error(e);
  }
}
loadState();

const defaultValues = {
  text: "https://example.com",
  size: 500,
  margin: 4,
  ec: "Q",
  shape: "circle",
  shapeScale: 0.95,
  roundedRadius: 0.15,
  bgColor: "#ffffff",
  bgColorPicker: "#ffffff",
  bgColorOpacity: 1,
  color1: "#111827",
  color1Picker: "#111827",
  color2: "#111827",
  color2Picker: "#111827",
  gradientType: "none",
  gradientAngle: 0,
  logoSize: 20,
  logoBg: "#ffffff",
  logoBgPicker: "#ffffff",
  logoBgOpacity: 1,
};

const resetBtn = $("resetBtn"); // Asegurate de tener el boton con id="resetBtn"

resetBtn.addEventListener("click", () => {
  // Restaurar valores por defecto
  for (const key in defaultValues) {
    if (inputs[key]) inputs[key].value = defaultValues[key];
  }

  // Reset logo
  logoImage = null;
  inputs.logoThumb.style.display = "none";
  inputs.logoFile.value = "";

  // Renderizar QR con valores por defecto
  renderQR(collectOptions());
  updateShapeSettings();
});

// ===== AUTORRENDER CADA 1s =====
setInterval(() => renderQR(collectOptions()), 1000);
setInterval(saveState, 3000);

// ===== SHAPE SETTINGS =====
const shapeSelect = $("shape"),
  shapeSettings = $("shapeSettings");
function updateShapeSettings() {
  shapeSettings.style.display =
    shapeSelect.value === "rounded" ? "grid" : "none";
}
shapeSelect.addEventListener("change", updateShapeSettings);
updateShapeSettings();
