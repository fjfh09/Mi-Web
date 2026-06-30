// utilidades sin tildes en texto UI
const $ = (id) => document.getElementById(id);

// elementos
const canvas = $("canvas");
const ctx = canvas.getContext("2d");
const thumb = $("thumb");
const tctx = thumb.getContext("2d");

const inputs = {
  text: $("text"),
  size: $("size")>1000?1000:$("size"),
  margin: $("margin"),
  ec: $("ec"),
  shape: $("shape"),
  shapeScale: $("shapeScale"),
  roundedRadius: $("roundedRadius"),
  bgColor: $("bgColor"),
  bgColorPicker: $("bgColorPicker"),
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
  genBtn: $("genBtn"),
  dlPng: $("dlPng"),
  dlSvg: $("dlSvg"),
  meta: $("meta"),
  sizeInfo: $("sizeInfo"),
  zoomIn: $("zoomIn"),
  zoomOut: $("zoomOut"),
};

const bgColor = document.getElementById('bgColor');
const bgColorPicker = document.getElementById('bgColorPicker');

bgColorPicker.addEventListener('input', () => {
  bgColor.value = bgColorPicker.value;
});

bgColor.addEventListener('input', () => {
  if (/^#[0-9A-Fa-f]{6}$/.test(bgColor.value)) {
    bgColorPicker.value = bgColor.value;
  }
});

const color1 = document.getElementById('color1');
const color1Picker = document.getElementById('color1Picker');

color1Picker.addEventListener('input', () => {
  color1.value = color1Picker.value;
});

color1.addEventListener('input', () => {
  if (/^#[0-9A-Fa-f]{6}$/.test(color1.value)) {
    color1Picker.value = color1.value;
  }
});

const color2 = document.getElementById('color2');
const color2Picker = document.getElementById('color2Picker');

color2Picker.addEventListener('input', () => {
  color2.value = color2Picker.value;
});

color2.addEventListener('input', () => {
  if (/^#[0-9A-Fa-f]{6}$/.test(color2.value)) {
    color2Picker.value = color2.value;
  }
});

const logoBg = document.getElementById('logoBg');
const logoBgPicker = document.getElementById('logoBgPicker');

logoBgPicker.addEventListener('input', () => {
  logoBg.value = logoBgPicker.value;
});

logoBg.addEventListener('input', () => {
  if (/^#[0-9A-Fa-f]{6}$/.test(logoBg.value)) {
    logoBgPicker.value = logoBg.value;
  }
});


let logoImage = null;
let zoomScale = 1;

// manejar carga logo
inputs.logoFile.addEventListener("change", (e) => {
  const f = e.target.files[0];
  if (!f) {
    logoImage = null;
    inputs.logoThumb.style.display = "none";
    return;
  }
  const url = URL.createObjectURL(f);
  const img = new Image();
  img.onload = () => {
    logoImage = img;
    inputs.logoThumb.src = url;
    inputs.logoThumb.style.display = "inline-block";
    URL.revokeObjectURL(url);
  };
  img.src = url;
});

function makeQRMatrix(text, ecLevel) {
  // qrcode-generator usage: qrcode(typeNumber, errorCorrectionLevel)
  // typeNumber 0 = automatic
  try {
    const qr = qrcode(0, ecLevel);
    qr.addData(text);
    qr.make();
    const size = qr.getModuleCount();
    const matrix = [];
    for (let r = 0; r < size; r++) {
      const row = [];
      for (let c = 0; c < size; c++) {
        row.push(qr.isDark(r, c));
      }
      matrix.push(row);
    }
    return matrix;
  } catch (err) {
    console.error("error qr:", err);
    return null;
  }
}

// drawing shapes
function drawModule(ctx, x, y, s, shape, scale, radius) {
  const half = s / 2;
  const centerX = x + half;
  const centerY = y + half;
  const r = (s * scale) / 2;

  ctx.beginPath();
  if (shape === "square") {
    const pad = (s - s * scale) / 2;
    ctx.rect(x + pad, y + pad, s * scale, s * scale);
    ctx.fill();
  } else if (shape === "rounded") {
    const pad = (s - s * scale) / 2;
    const rr = Math.max(0, radius * s);
    const sx = x + pad,
      sy = y + pad,
      sw = s * scale,
      sh = s * scale;
    roundedRectPath(ctx, sx, sy, sw, sh, rr);
    ctx.fill();
  } else if (shape === "circle" || shape === "dots") {
    ctx.arc(centerX, centerY, r, 0, Math.PI * 2);
    ctx.fill();
  } else if (shape === "diamond") {
    ctx.moveTo(centerX, centerY - r);
    ctx.lineTo(centerX + r, centerY);
    ctx.lineTo(centerX, centerY + r);
    ctx.lineTo(centerX - r, centerY);
    ctx.closePath();
    ctx.fill();
  } else {
    // fallback square
    ctx.rect(x, y, s, s);
    ctx.fill();
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

function createFill(ctx, x0, y0, w, h, type, c1, c2, angleDeg) {
  if (type === "none" || c1 === c2) {
    ctx.fillStyle = c1;
    return null;
  }
  if (type === "linear") {
    // angle in degrees: 0 -> left->right
    const a = ((angleDeg % 360) * Math.PI) / 180;
    const cx = x0 + w / 2,
      cy = y0 + h / 2;
    const len = Math.max(w, h);
    const x1 = cx - Math.cos(a) * len;
    const y1 = cy - Math.sin(a) * len;
    const x2 = cx + Math.cos(a) * len;
    const y2 = cy + Math.sin(a) * len;
    const g = ctx.createLinearGradient(x1, y1, x2, y2);
    g.addColorStop(0, c1);
    g.addColorStop(1, c2);
    ctx.fillStyle = g;
    return g;
  } else if (type === "radial") {
    const cx = x0 + w / 2,
      cy = y0 + h / 2;
    const r = Math.max(w, h);
    const g = ctx.createRadialGradient(cx, cy, 0, cx, cy, r);
    g.addColorStop(0, c1);
    g.addColorStop(1, c2);
    ctx.fillStyle = g;
    return g;
  } else {
    ctx.fillStyle = c1;
    return null;
  }
}

function clearCanvas(ctx, w, h, bg) {
  ctx.save();
  ctx.setTransform(1, 0, 0, 1, 0, 0);
  ctx.clearRect(0, 0, w, h);
  if (bg) {
    ctx.fillStyle = bg;
    ctx.fillRect(0, 0, w, h);
  }
  ctx.restore();
}
// renderQR: renderiza directamente en canvas de forma robusta frente a zoom del navegador
function renderQR(options) {
  const matrix = makeQRMatrix(options.text, options.ec);
  if (!matrix) {
    alert("No se pudo generar el QR");
    return;
  }
  const modules = matrix.length;
  const margin = Number(options.margin) || 4;
  const totalModules = modules + margin * 2;
  const canvasSize = Math.max(128, Number(options.size) || 800);

  // dpr y preparacion del canvas para alta resolucion
  const dpr = window.devicePixelRatio || 1;
  canvas.width = Math.round(canvasSize * dpr);
  canvas.height = Math.round(canvasSize * dpr);
  canvas.style.width = canvasSize + "px";
  canvas.style.height = canvasSize + "px";

  // transformamos el contexto para trabajar en unidades logicas (CSS px)
  ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
  ctx.imageSmoothingEnabled = false;

  // limpiar y pintar fondo (con pequeno bleed para seguridad)
  const bleed = 1; // px logicos
  ctx.save();
  ctx.fillStyle = options.bgColor || "#ffffff";
  // rellenamos un poco mas para cubrir rounding issues
  ctx.fillRect(-bleed, -bleed, canvasSize + bleed * 2, canvasSize + bleed * 2);
  ctx.restore();

  // calculos logicos
  const modulePx = canvasSize / totalModules;
  const drawX = margin * modulePx;

  // Crear fill (gradiente o color) en canvas
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

  // Dibujo de modulos: convertimos a pixeles fisicos y ajustamos con floor/ceil para evitar gaps
  for (let r = 0; r < modules; r++) {
    for (let c = 0; c < modules; c++) {
      if (matrix[r][c]) {
        const x_log = drawX + c * modulePx;
        const y_log = drawX + r * modulePx;
        const s_log = modulePx;

        // convert to device pixels and snap
        const x_px = Math.floor(x_log * dpr);
        const y_px = Math.floor(y_log * dpr);
        const w_px = Math.ceil(s_log * dpr);
        const h_px = Math.ceil(s_log * dpr);

        // draw into an offscreen canvas and then drawImage, or draw directly using rounded shapes.
        // Para formas simples, dibujamos directas ajustando por dpr inverso en contexto transformado.
        // Calculamos coords en unidades logicas ajustadas para cubrir subpixel rounding:
        const x_adj = x_px / dpr;
        const y_adj = y_px / dpr;
        const w_adj = w_px / dpr;
        const h_adj = h_px / dpr;

        ctx.beginPath();
        if (options.shape === "square") {
          ctx.rect(x_adj, y_adj, w_adj, h_adj);
          ctx.fill();
        } else if (options.shape === "rounded") {
          const pad = (s_log - s_log * options.shapeScale) / 2;
          const sx = x_adj + pad;
          const sy = y_adj + pad;
          const sw = s_log * options.shapeScale;
          const sr = Math.max(0, Number(options.roundedRadius) * s_log);
          roundedRectPath(ctx, sx, sy, sw, sw, sr);
          ctx.fill();
        } else if (options.shape === "circle" || options.shape === "dots") {
          const cx = x_adj + w_adj / 2;
          const cy = y_adj + h_adj / 2;
          const r = Math.max(0.5, (Math.max(w_px, h_px) / 2 / dpr) * 0.999);
          ctx.arc(cx, cy, r, 0, Math.PI * 2);
          ctx.fill();
        } else if (options.shape === "diamond") {
          const cx = x_adj + w_adj / 2;
          const cy = y_adj + h_adj / 2;
          const r = Math.max(0.5, (Math.max(w_px, h_px) / 2 / dpr) * 0.999);
          ctx.moveTo(cx, cy - r);
          ctx.lineTo(cx + r, cy);
          ctx.lineTo(cx, cy + r);
          ctx.lineTo(cx - r, cy);
          ctx.closePath();
          ctx.fill();
        } else {
          ctx.rect(x_adj, y_adj, w_adj, h_adj);
          ctx.fill();
        }
      }
    }
  }

  // Logo: dibujar con canvas temporal de alta resolucion y luego colocar en contexto
  if (logoImage) {
    const logoPct = Math.max(6, Math.min(48, Number(options.logoSize) || 20));
    const logoPxLogical = Math.floor(canvasSize * (logoPct / 100));
    const targetPx = Math.round(logoPxLogical * dpr);

    // tmp canvas para rasterizar logo a alta calidad
    const tmp = document.createElement("canvas");
    tmp.width = Math.max(1, targetPx);
    tmp.height = Math.max(1, targetPx);
    const t = tmp.getContext("2d");
    t.imageSmoothingEnabled = true;
    // calcular ratio manteniendo proporcion
    const iw = logoImage.width,
      ih = logoImage.height;
    const ratio = Math.min(tmp.width / iw, tmp.height / ih);
    const drawW = Math.round(iw * ratio);
    const drawH = Math.round(ih * ratio);
    // centrar en tmp
    const ox = Math.round((tmp.width - drawW) / 2);
    const oy = Math.round((tmp.height - drawH) / 2);
    // opcional fondo detras logo
    if (options.logoBg) {
      t.fillStyle = options.logoBg;
      // pequeño bleed para seguridad
      t.fillRect(0, 0, tmp.width, tmp.height);
    } else {
      t.clearRect(0, 0, tmp.width, tmp.height);
    }
    t.drawImage(logoImage, ox, oy, drawW, drawH);

    // posicion en canvas principal (en unidades logicas, pero ctx ya escalado)
    const dx_log = Math.round((canvasSize - logoPxLogical) / 2);
    const dy_log = dx_log;
    // para evitar subpixel gaps convertimos a px fisicos y centramos
    const dx_px = Math.floor(dx_log * dpr);
    const dy_px = Math.floor(dy_log * dpr);
    const w_px = tmp.width;
    const h_px = tmp.height;
    // dibujar la imagen desde coordenadas px fisicos (drawImage acepta unidad logica, pero al tener transform dpr se ajusta)
    ctx.drawImage(
      tmp,
      0,
      0,
      w_px,
      h_px,
      dx_px / dpr,
      dy_px / dpr,
      w_px / dpr,
      h_px / dpr
    );
  }

  // actualizar thumb (ten en cuenta que thumb es pequeño)
  tctx.clearRect(0, 0, thumb.width, thumb.height);
  // renderizamos la imagen ya escalada del canvas principal en el thumb (canvas -> canvas)
  tctx.drawImage(canvas, 0, 0, thumb.width, thumb.height);

  // metadatos
  $(
    "meta"
  ).textContent = `modulos: ${modules} | margen: ${margin} | tamano: ${canvasSize}px | shape: ${options.shape}`;
  $(
    "sizeInfo"
  ).textContent = `canvas ${canvasSize} x ${canvasSize}px (dpr ${dpr})`;
}

// exportSVG: genera SVG vectorial para descarga (no usado para previsualizar)
function exportSVG(options) {
  const matrix = makeQRMatrix(options.text, options.ec);
  if (!matrix) return null;
  const modules = matrix.length;
  const margin = Number(options.margin) || 4;
  const canvasSize = Math.max(128, Number(options.size) || 800);
  const totalModules = modules + margin * 2;
  const modulePx = canvasSize / totalModules;
  const drawX = margin * modulePx;

  const xmlns = "http://www.w3.org/2000/svg";
  const svgParts = [];
  svgParts.push(
    `<svg xmlns="${xmlns}" width="${canvasSize}" height="${canvasSize}" viewBox="0 0 ${canvasSize} ${canvasSize}" preserveAspectRatio="xMinYMin meet" shape-rendering="crispEdges">`
  );
  // fondo 100% y bleed logico
  svgParts.push(
    `<rect x="-1" y="-1" width="${canvasSize + 2}" height="${
      canvasSize + 2
    }" fill="${options.bgColor}" />`
  );

  // gradients
  if (options.gradientType !== "none" && options.color1 !== options.color2) {
    if (options.gradientType === "linear") {
      const id = "g1";
      const a = Number(options.gradientAngle) || 0;
      const x1 = 50 - Math.cos((a * Math.PI) / 180) * 50;
      const y1 = 50 - Math.sin((a * Math.PI) / 180) * 50;
      const x2 = 50 + Math.cos((a * Math.PI) / 180) * 50;
      const y2 = 50 + Math.sin((a * Math.PI) / 180) * 50;
      svgParts.push(
        `<defs><linearGradient id="${id}" x1="${x1}%" y1="${y1}%" x2="${x2}%" y2="${y2}%"><stop offset="0%" stop-color="${options.color1}"/><stop offset="100%" stop-color="${options.color2}"/></linearGradient></defs>`
      );
    } else {
      const id = "g1";
      svgParts.push(
        `<defs><radialGradient id="${id}"><stop offset="0%" stop-color="${options.color1}"/><stop offset="100%" stop-color="${options.color2}"/></radialGradient></defs>`
      );
    }
  }
  const fillRef =
    options.gradientType === "none" || options.color1 === options.color2
      ? options.color1
      : "url(#g1)";

  // módulos en unidades logicas, formateados a 3 decimales
  const fmt = (v) => Number(v).toFixed(3);
  for (let r = 0; r < modules; r++) {
    for (let c = 0; c < modules; c++) {
      if (matrix[r][c]) {
        const x = drawX + c * modulePx;
        const y = drawX + r * modulePx;
        const s = modulePx;
        if (options.shape === "square") {
          svgParts.push(
            `<rect x="${fmt(x)}" y="${fmt(y)}" width="${fmt(s)}" height="${fmt(
              s
            )}" fill="${fillRef}" />`
          );
        } else if (options.shape === "rounded") {
          const pad = (s - s * options.shapeScale) / 2;
          const sw = s * options.shapeScale;
          const sr = Math.max(0, Number(options.roundedRadius) * s);
          svgParts.push(
            `<rect x="${fmt(x + pad)}" y="${fmt(y + pad)}" width="${fmt(
              sw
            )}" height="${fmt(sw)}" rx="${fmt(sr)}" ry="${fmt(
              sr
            )}" fill="${fillRef}" />`
          );
        } else if (options.shape === "circle" || options.shape === "dots") {
          const cx = x + s / 2,
            cy = y + s / 2,
            r0 = (s * options.shapeScale) / 2;
          svgParts.push(
            `<circle cx="${fmt(cx)}" cy="${fmt(cy)}" r="${fmt(
              r0
            )}" fill="${fillRef}" />`
          );
        } else if (options.shape === "diamond") {
          const cx = x + s / 2,
            cy = y + s / 2,
            r0 = (s * options.shapeScale) / 2;
          const p = `${fmt(cx)},${fmt(cy - r0)} ${fmt(cx + r0)},${fmt(
            cy
          )} ${fmt(cx)},${fmt(cy + r0)} ${fmt(cx - r0)},${fmt(cy)}`;
          svgParts.push(`<polygon points="${p}" fill="${fillRef}" />`);
        } else {
          svgParts.push(
            `<rect x="${fmt(x)}" y="${fmt(y)}" width="${fmt(s)}" height="${fmt(
              s
            )}" fill="${fillRef}" />`
          );
        }
      }
    }
  }

  // logo embed (base64) con dimensiones logicas
  if (logoImage) {
    const logoPct = Math.max(6, Math.min(48, Number(options.logoSize) || 20));
    const logoPx = Math.floor(canvasSize * (logoPct / 100));
    const lx = Math.round((canvasSize - logoPx) / 2);
    const ly = lx;
    if (options.logoBg) {
      const r0 = Math.max(6, logoPx * 0.08);
      svgParts.push(
        `<rect x="${fmt(lx - 6)}" y="${fmt(ly - 6)}" width="${fmt(
          logoPx + 12
        )}" height="${fmt(logoPx + 12)}" rx="${fmt(r0)}" fill="${
          options.logoBg
        }" />`
      );
    }
    // tmp canvas para base64
    const tmp = document.createElement("canvas");
    const dpr = window.devicePixelRatio || 1;
    const targetPx = Math.round(logoPx * dpr);
    const iw = logoImage.width,
      ih = logoImage.height;
    const ratio = Math.min(targetPx / iw, targetPx / ih);
    tmp.width = Math.max(1, Math.round(iw * ratio));
    tmp.height = Math.max(1, Math.round(ih * ratio));
    const tt = tmp.getContext("2d");
    tt.drawImage(logoImage, 0, 0, tmp.width, tmp.height);
    const data = tmp.toDataURL("image/png");
    const imgW_log = logoPx;
    const imgH_log = logoPx * (tmp.height / tmp.width);
    const dx_log = Math.round((canvasSize - imgW_log) / 2);
    const dy_log = Math.round((canvasSize - imgH_log) / 2);
    svgParts.push(
      `<image x="${fmt(dx_log)}" y="${fmt(dy_log)}" width="${fmt(
        imgW_log
      )}" height="${fmt(
        imgH_log
      )}" href="${data}" preserveAspectRatio="xMidYMid meet" />`
    );
  }

  svgParts.push("</svg>");
  return svgParts.join("");
}

// descargar helper
function downloadURI(uri, name) {
  const a = document.createElement("a");
  a.href = uri;
  a.download = name;
  document.body.appendChild(a);
  a.click();
  a.remove();
}

// eventos
function collectOptions() {
  const size = Number(inputs.size.value)>1000?1000:Number(inputs.size.value);
  const logoSize = (Number(inputs.logoSize.value) || 20)>30?30:(Number(inputs.logoSize.value) || 20);
  const shapeScale = (Number(inputs.shapeScale.value)<0.6 || Number(inputs.shapeScale.value)>1)?0.7:Number(inputs.shapeScale.value);
  const roundedRadius = (Number(inputs.roundedRadius.value)<0 || Number(inputs.roundedRadius.value)>0.5)?0.15:Number(inputs.roundedRadius.value);
  inputs.size.value = size;
  inputs.logoSize.value = logoSize;
  inputs.shapeScale.value = shapeScale;
  inputs.roundedRadius.value = roundedRadius;
  return {
    text: inputs.text.value,
    size,
    margin: Number(inputs.margin.value),
    ec: inputs.ec.value,
    shape: inputs.shape.value,
    shapeScale,
    roundedRadius,
    bgColor: inputs.bgColor.value || "#ffffff",
    bgColorPicker: inputs.bgColorPicker.value || "#ffffff",
    color1: inputs.color1.value || "#111827",
    color1Picker: inputs.color1Picker.value || "#111827",
    color2: inputs.color2.value || inputs.color1.value || "#111827",
    color2Picker: inputs.color2Picker.value || inputs.color1Picker.value || "#111827",
    gradientType: inputs.gradientType.value,
    gradientAngle: Number(inputs.gradientAngle.value) || 0,
    logoSize,
    logoBg: inputs.logoBg.value || "",
    logoBgPicker: inputs.logoBgPicker.value || "",
  };
}

inputs.genBtn.addEventListener("click", () => renderQR(collectOptions()));
inputs.dlPng.addEventListener("click", () => {
  renderQR(collectOptions()); // asegurar que canvas esta actualizado
  const uri = canvas.toDataURL("image/png");
  downloadURI(uri, "qr.png");
});
inputs.dlSvg.addEventListener("click", () => {
  const options = collectOptions();
  const svg = exportSVG(options);
  if (!svg) {
    alert("error generando svg");
    return;
  }
  const blob = new Blob([svg], { type: "image/svg+xml;charset=utf-8" });
  const url = URL.createObjectURL(blob);
  downloadURI(url, "qr.svg");
  setTimeout(() => URL.revokeObjectURL(url), 1500);
});

// generar al inicio
renderQR(collectOptions());

// zoom botones (solo cambia escala visual)
inputs.zoomIn.addEventListener("click", () => {
  zoomScale = Math.min(1, zoomScale + 0.1);
  canvas.style.transform = `scale(${zoomScale})`;
});
inputs.zoomOut.addEventListener("click", () => {
  zoomScale = Math.max(0.6, zoomScale - 0.1);
  canvas.style.transform = `scale(${zoomScale})`;
});

// enter rapido: ctrl+enter genera
inputs.text.addEventListener("keydown", (e) => {
  if ((e.ctrlKey || e.metaKey) && e.key === "Enter") {
    renderQR(collectOptions());
  }
});

// small autosave in localStorage for convenience
const STORAGE_KEY = "qr_avanzado_v1";
function saveState() {
  const s = collectOptions();
  // no guardar imagenes
  localStorage.setItem(STORAGE_KEY, JSON.stringify(s));
  renderQR(s);
}
function loadState() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) return;
    const s = JSON.parse(raw);
    inputs.text.value = s.text || inputs.text.value;
    inputs.size.value = s.size || inputs.size.value;
    inputs.margin.value = s.margin || inputs.margin.value;
    inputs.ec.value = s.ec || inputs.ec.value;
    inputs.shape.value = s.shape || inputs.shape.value;
    inputs.shapeScale.value = s.shapeScale || inputs.shapeScale.value;
    inputs.roundedRadius.value = s.roundedRadius || inputs.roundedRadius.value;
    inputs.bgColor.value = s.bgColor || inputs.bgColor.value;
    inputs.bgColorPicker.value = s.bgColorPicker || inputs.bgColorPicker.value;
    inputs.color1.value = s.color1 || inputs.color1.value;
    inputs.color1Picker.value = s.color1Picker || inputs.color1Picker.value;
    inputs.color2.value = s.color2 || inputs.color2.value;
    inputs.color2Picker.value = s.color2Picker || inputs.color2Picker.value;
    inputs.gradientType.value = s.gradientType || inputs.gradientType.value;
    inputs.gradientAngle.value = s.gradientAngle || inputs.gradientAngle.value;
    inputs.logoSize.value = s.logoSize || inputs.logoSize.value;
    inputs.logoBg.value = s.logoBg || inputs.logoBg.value;
    inputs.logoBgPicker.value = s.logoBgPicker || inputs.logoBgPicker.value;
    renderQR(collectOptions());
  } catch (e) {
    /* ignore */
  }
}

// boton para restablecer el QR predeterminado
// guarda los valores iniciales (los del HTML)
const defaultValues = {};
for (const key in inputs) {
  const el = inputs[key];
  if (el) defaultValues[key] = el.type === 'file' ? null : el.value;
}

// boton restablecer
const resetBtn = document.getElementById("resetBtn");
resetBtn.addEventListener("click", () => {
  // borrar configuracion guardada
  localStorage.removeItem(STORAGE_KEY);

  // restaurar valores iniciales
  for (const key in defaultValues) {
    if (inputs[key]) {
      inputs[key].value = defaultValues[key];
    }
  }

  // ocultar logo y resetear vista previa
  logoImage = null;
  inputs.logoThumb.style.display = "none";
  inputs.logoFile.value = "";

  // regenerar QR con valores originales
  renderQR(collectOptions());
  updateShapeSettings();
  loadState();
});

//Interactividad al elegir rounded
const shapeSelect = document.getElementById("shape");
  const shapeSettings = document.getElementById("shapeSettings");

  function updateShapeSettings() {
    // Mostrar los campos solo si se elige "redondeado"
    if (shapeSelect.value === "rounded") {
      shapeSettings.style.display = "grid";
    } else {
      shapeSettings.style.display = "none";
    }
  }

  // Detectar cambios
  shapeSelect.addEventListener("change", updateShapeSettings);

  // Ejecutar al cargar (por si "rounded" está seleccionado inicialmente)

// guardar cada 3s si hay cambios
setInterval(saveState, 3000);
loadState();
updateShapeSettings();