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

    fetch("https://fjfh06.ddns.net/vpn/api/get_planes.php")
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
  fetch("https://fjfh06.ddns.net/vpn/api/ip.php")
    .then(res => res.text())
    .then(ipCliente => {
      ipCliente = ipCliente.trim();
      console.log("IP cliente:", ipCliente);

      // Obtener IP de referencia de tu DDNS
      fetch("https://fjfh06.ddns.net/vpn/api/ddns_ip.php")
        .then(r => r.text())
        .then(ipDDNS => {
          ipDDNS = ipDDNS.trim();
          console.log("IP DDNS:", ipDDNS);

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
});


function mostrarToast(mensaje, esError = false) {
    const toast = document.getElementById("toast");
    toast.textContent = mensaje;
    toast.style.background = esError
        ? "linear-gradient(135deg, #e53935, #b71c1c)" // rojo error
        : "linear-gradient(135deg, #4caf50, #2e7d32)"; // verde exito
    toast.classList.add("show");

    setTimeout(() => {
        toast.classList.remove("show");
    }, 3500); // se oculta en 3.5s
}

function enviarFormulario() {
    const nombre = document.getElementById('nombre').value.trim();
    const correo = document.getElementById('correo').value.trim();
    const asunto = document.getElementById('asunto').value.trim();
    const mensaje = document.getElementById('mensaje').value.trim();

    const correoRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!correoRegex.test(correo)) {
        mostrarToast('El correo no es valido.', true);
        return false;
    }

    if (!nombre || !correo || !asunto || !mensaje) {
        mostrarToast('Por favor, completa todos los campos.', true);
        return false;
    }

    fetch('https://fjfh06.ddns.net/vpn/api/contacto.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ nombre, correo, asunto, mensaje })
    })
    .then(res => res.json())
    .then(data => {
        mostrarToast(data.mensaje || 'Mensaje enviado correctamente.');
        document.getElementById('contactForm').reset();
    })
    .catch(err => {
        console.error('Error:', err);
        mostrarToast('Hubo un error al enviar el mensaje.', true);
    });

    return false;
}

function cambiarTextoBoton() {
  const boton = document.querySelector('.login-button');
  if(window.innerWidth <= 600) {
    boton.textContent = '🔐';
  } else {
    boton.textContent = 'Iniciar sesión';
  }
}

window.addEventListener('load', cambiarTextoBoton);
window.addEventListener('resize', cambiarTextoBoton);