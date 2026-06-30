const email = "fjfh.vpn@gmail.com";
let plans = [];

// Cargar planes desde la API con cache localStorage
function loadPlans() {
    const cached = localStorage.getItem("planesCache");
    const cacheTime = localStorage.getItem("planesCacheTime");

    if (cached && cacheTime && Date.now() - cacheTime < (60000 * 30)) { // mins que quieras
        plans = JSON.parse(cached);
        generateTables();
        return;
    }

    fetch("https://vpn.almagara.es/api/get_planes.php")
        .then(res => res.json())
        .then(data => {
            plans = data;
            localStorage.setItem("planesCache", JSON.stringify(data));
            localStorage.setItem("planesCacheTime", Date.now());
            generateTables();
        })
        .catch(err => {
            console.error("Error cargando planes:", err);
            document.getElementById("plans").innerHTML =
                "<p>No se pudieron cargar los planes.</p>";
        });
}

function formatPrice(value) {
    let num = parseFloat(value);
    if (isNaN(num)) return value;

    // Pasamos siempre a 2 decimales
    let formatted = num.toFixed(2).replace(".", ",");

    // Quitamos ",00"
    if (formatted.endsWith(",00")) {
        return formatted.slice(0, -3);
    }

    // Si termina en ",0" → lo quitamos
    if (formatted.endsWith(",0")) {
        return formatted.slice(0, -2);
    }

    return formatted;
}




function generateTables() {
    let html = "";
    plans.forEach((plan, index) => {
        const numPerfiles = parseInt(plan.category.match(/\d+/)) || 1;
        const tableClass = (index % 2 === 0) ? "oscuro-claro" : "claro-oscuro";

        html += `<h2>${plan.category}</h2>`;
        html += `<table class="${tableClass}"><tr>
                    <th>Duracion</th>
                    ${numPerfiles > 1 ? `<th>Precio por perfil/mes</th>` : ""}
                    <th>Precio por mes</th>
                    <th>Precio total</th>
                    <th>Elige</th>
                 </tr>`;

        plan.data.forEach(p => {
            const duration = p[0];
            const priceMonth = parseFloat(p[1].replace("€", "").replace(",", "."));
            const priceTotal = parseFloat(p[2].replace("€", "").replace(",", "."));

            let durationMonths = 1;
            if (duration.includes("3")) durationMonths = 3;
            else if (duration.includes("6")) durationMonths = 6;
            else if (duration.includes("año")) durationMonths = 12;

            const pricePerProfile = numPerfiles > 1
                ? formatPrice(priceTotal / durationMonths / numPerfiles) + "€"
                : "";

            // aplicar formatPrice a los precios de BD
            const priceMonthFormatted = formatPrice(priceMonth) + "€";
            const priceTotalFormatted = formatPrice(priceTotal) + "€";

            html += `<tr>
                        <td data-label="Duracion">${duration}</td>
                        ${pricePerProfile ? `<td data-label="Precio por perfil/mes">${pricePerProfile}</td>` : ""}
                        <td data-label="Precio por mes">${priceMonthFormatted}</td>
                        <td data-label="Precio total">${priceTotalFormatted}</td>
                        <td data-label="Elige"><button onclick="purchasePlan('${plan.category}', '${duration}', '${priceTotalFormatted}')">Elegir</button></td>
                    </tr>`;
        });
        html += `</table>`;
    });
    document.getElementById("plans").innerHTML = html;
}

function purchasePlan(category, duration, price) {
    const asunto = "Compra de VPN - " + category;
    const mensaje = `Hola, estoy interesado en el plan:
- Categoria: ${category}
- Duracion: ${duration}
- Precio total: ${price}

Espero su respuesta.`;

    // Rellenar los campos del formulario
    document.getElementById("asunto").value = asunto;
    document.getElementById("mensaje").value = mensaje;

    // Hacer scroll hasta el formulario (opcional)
    document.getElementById("contactForm").scrollIntoView({ behavior: "smooth" });
}

// Llamada inicial
document.addEventListener("DOMContentLoaded", loadPlans);


document.addEventListener("DOMContentLoaded", () => {
  const ipElement = document.getElementById("mi-ip");
  const separaElement = document.getElementById("separador");
  const estadoElement = document.getElementById("mi-estado");

  // Obtener IP del cliente
  fetch("https://vpn.almagara.es/api/ip.php")
    .then(res => res.text())
    .then(ipCliente => {
      ipCliente = ipCliente.trim();
      console.log("IP cliente:", ipCliente);

      // Obtener IP de referencia de tu DDNS
      fetch("https://vpn.almagara.es/api/ddns_ip.php")
        .then(r => r.text())
        .then(ipDDNS => {
          ipDDNS = ipDDNS.trim();
          //console.log("IP DDNS:", ipDDNS);

          if (ipCliente === ipDDNS) {
            estadoElement.innerHTML = 'Tu estado: <span class="protegido">Protegido</span>';
            ipElement.textContent = "";
            separaElement.textContent = "";
          } else {
            estadoElement.innerHTML = 'Tu estado: <span class="desprotegido">Desprotegido</span>';
            ipElement.textContent = "Tu IP: " + ipCliente;
            separaElement.textContent = "·";
          }
        })
        .catch(err => {
          console.error("Error obteniendo IP DDNS:", err);
          estadoElement.innerHTML = 'Tu estado: <span class="desprotegido">Desconocido</span>';
          ipElement.textContent = "Tu IP: " + ipCliente;
          separaElement.textContent = "·";
        });
    })
    .catch(err => {
      console.error("Error obteniendo IP cliente:", err);
      estadoElement.innerHTML = 'Tu estado: <span class="desprotegido">Sin datos</span>';
      ipElement.textContent = "Tu IP: Sin datos";
      separaElement.textContent = "·";
    });

  // Cargar Google reCAPTCHA
  let siteKey = '';
  fetch("https://vpn.almagara.es/api/contacto.php?getSiteKey=1")
    .then(res => res.json())
    .then(data => {
        if (data.siteKey) {
            siteKey = data.siteKey;
            window.vpnSiteKey = siteKey; // Guardar globalmente para validación
            
            if (!document.getElementById('recaptcha-script')) {
                let container = document.getElementById('recaptcha-container');
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'recaptcha-container';
                    container.style.marginBottom = '15px';
                    const form = document.getElementById('contactForm');
                    if (form) {
                        const btn = form.querySelector('button[type="submit"]');
                        form.insertBefore(container, btn);
                    }
                }

                const script = document.createElement('script');
                script.id = 'recaptcha-script';
                script.src = 'https://www.google.com/recaptcha/api.js?render=explicit';
                script.async = true;
                script.defer = true;
                script.onload = () => {
                    if (document.getElementById('recaptcha-container')) {
                        window.grecaptcha.ready(() => {
                            window.grecaptcha.render('recaptcha-container', {
                                'sitekey': siteKey
                            });
                        });
                    }
                };
                document.head.appendChild(script);
            }
        }
    })
    .catch(err => console.error("Error cargando Site Key reCAPTCHA:", err));
});


let toastTimer = null;

function mostrarToast(mensaje, esError = false) {
    const toast = document.getElementById("toast");
    const toastText = toast.querySelector(".toast-text");
    const toastIcon = toast.querySelector(".toast-icon i");

    if (toastTimer) {
        clearTimeout(toastTimer);
    }

    toastText.textContent = mensaje;

    if (esError) {
        toast.classList.remove("success");
        toast.classList.add("error");
        toastIcon.className = "fas fa-exclamation-circle";
    } else {
        toast.classList.remove("error");
        toast.classList.add("success");
        toastIcon.className = "fas fa-check-circle";
    }

    toast.classList.add("show");

    toastTimer = setTimeout(hideToast, 5000);
}

function hideToast() {
    const toast = document.getElementById("toast");
    if (toast) {
        toast.classList.remove("show");
    }
}

function enviarFormulario() {
    const nombre = document.getElementById('nombre').value.trim();
    const correo = document.getElementById('correo').value.trim();
    const asunto = document.getElementById('asunto').value.trim();
    const mensaje = document.getElementById('mensaje').value.trim();

    const correoRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!correoRegex.test(correo)) {
        mostrarToast('El correo no es válido.', true);
        return false;
    }

    if (!nombre || !correo || !asunto || !mensaje) {
        mostrarToast('Por favor, completa todos los campos.', true);
        return false;
    }

    const recaptchaToken = window.grecaptcha ? window.grecaptcha.getResponse() : '';
    if (!recaptchaToken && window.vpnSiteKey) {
        mostrarToast('Por favor, completa el CAPTCHA.', true);
        return false;
    }

    fetch('https://vpn.almagara.es/api/contacto.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ nombre, correo, asunto, mensaje, recaptchaToken })
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) {
            mostrarToast(data.error, true);
        } else {
            mostrarToast(data.mensaje || 'Mensaje enviado correctamente.');
            document.getElementById('contactForm').reset();
        }
        if (window.grecaptcha) window.grecaptcha.reset();
    })
    .catch(err => {
        console.error('Error:', err);
        mostrarToast('Hubo un error al enviar el mensaje.', true);
        if (window.grecaptcha) window.grecaptcha.reset();
    });

    return false;
}