function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; } // Made by Javi fernandez (Twitter: @javifernandez.com)


/***********************
  Menu Component
 ***********************/

const Menu = props => {
  return /*#__PURE__*/(
    React.createElement("div", { className: `menu-container ${props.showMenu}` }, /*#__PURE__*/
      React.createElement("div", { className: "overlay" }), /*#__PURE__*/
      React.createElement("div", { className: "menu-items" }, /*#__PURE__*/
        React.createElement("ul", null, /*#__PURE__*/
          React.createElement("li", null, /*#__PURE__*/
            React.createElement("a", { href: "#welcome-section", onClick: props.toggleMenu }, "INICIO")), /*#__PURE__*/

            React.createElement("li", null, /*#__PURE__*/
            React.createElement("a", { href: "#vpn", onClick: props.toggleMenu }, "VENTA VPN")), /*#__PURE__*/



          React.createElement("li", null, /*#__PURE__*/
            React.createElement("a", { href: "#about", onClick: props.toggleMenu }, "SOBRE MI")), /*#__PURE__*/



          React.createElement("li", null, /*#__PURE__*/
            React.createElement("a", { href: "#projects", onClick: props.toggleMenu }, "PROYECTOS")), /*#__PURE__*/
            
            React.createElement("li", null, /*#__PURE__*/
            React.createElement("a", { href: "#curriculum", onClick: props.toggleMenu }, "CURRICULUM")), /*#__PURE__*/



          React.createElement("li", null, /*#__PURE__*/
            React.createElement("a", { href: "#contacto", onClick: props.toggleMenu }, "CONTACTO"))), /*#__PURE__*/




        React.createElement(SocialLinks, null))));



};


/***********************
  Nav Component
 ***********************/

const Nav = props => {
  const activeClass = props.scrollActive ? 'bg-active' : '';
  return /*#__PURE__*/(
    React.createElement(React.Fragment, null, /*#__PURE__*/
      React.createElement("nav", { id: "navbar", className: activeClass }, /*#__PURE__*/
        React.createElement("div", { className: "nav-wrapper" }, /*#__PURE__*/
          React.createElement("p", { className: "brand" }, "Javier ", /*#__PURE__*/

            React.createElement("strong", null, "Fernández")), /*#__PURE__*/

          React.createElement("button", {
            onClick: props.toggleMenu,
            className: props.showMenu === 'active' ? 'menu-button active' : 'menu-button',
            "aria-label": "Menú de navegación"
          }, /*#__PURE__*/

            React.createElement("span", null))))));





};



/***********************
  Header Component
 ***********************/

const Header = props => {
  return /*#__PURE__*/(
    React.createElement("header", { id: "welcome-section" }, /*#__PURE__*/
      React.createElement("div", { className: "forest" }), /*#__PURE__*/
      React.createElement("div", { className: "silhouette" }), /*#__PURE__*/
      React.createElement("div", { className: "moon" }), /*#__PURE__*/
      React.createElement("div", { className: "container" }, /*#__PURE__*/
        React.createElement("h1", null, /*#__PURE__*/
          React.createElement("span", { className: "line" }, "Desarrollador Web"), /*#__PURE__*/
          React.createElement("span", { className: "line" }, /*#__PURE__*/
            React.createElement("span", { className: "color" }, "Full-Stack"))), /*#__PURE__*/


        React.createElement("div", { className: "buttons" }, /*#__PURE__*/
          React.createElement("a", { href: "#vpn" }, "Servicio VPN"),
          React.createElement("a", { href: "#projects" }, "Mis proyectos"), /*#__PURE__*/
          React.createElement("a", { href: "archivos/curriculum/CV_JAVIER_FERNANDEZ.pdf", target: "_blank", className: "cta" }, "Curriculum"),
          React.createElement("a", { href: "#contacto", className: "cta" }, "Contacto")
        ))));

};





/***********************
  About Component
 ***********************/

const About = props => {
  const tech = {
    sass: 'fab fa-sass',
    css: 'fab fa-css3-alt',
    js: 'fab fa-js-square',
    html5: 'fab fa-html5', // HTML5
    python: 'fab fa-python', // Python
    java: 'fab fa-java', // Java
    react: 'fab fa-react',
    vue: 'fab fa-vuejs',
    d3: 'far fa-chart-bar',
    node: 'fab fa-node',
    docker: 'fab fa-docker',
    db: 'fas fa-database'
  };


  return /*#__PURE__*/(
    React.createElement("section", { id: "about" }, /*#__PURE__*/
      React.createElement("div", { className: "wrapper" }, /*#__PURE__*/
        React.createElement("article", null,

          // 🔽 QUIEN SOY
          React.createElement("div", { className: "title" },
            React.createElement("h2", null, "¿Quién soy?"),
            React.createElement("p", { className: "separator" })
          ),

          React.createElement("div", { className: "desc full about-profile-card" },
            React.createElement("img", {
              src: "archivos/curriculum/mi_foto.webp",
              alt: "Javier Fernández",
              className: "profile-image-about about-profile-img",
              width: "500",
              height: "720"
            }),
            React.createElement("div", { className: "about-profile-text" },
              React.createElement("h3", { className: "subtitle about-profile-subtitle" }, "Mi nombre es Javier Fernández."),
              React.createElement("p", null, "Soy graduado en Desarrollo de Aplicaciones Web (DAW) en el año 2026 por el IES Hermenegildo Lanz. Me apasiona el desarrollo de software de extremo a extremo, la contenerización de servicios y la autogestión de infraestructuras locales."),
              React.createElement("p", null, "Soy una persona muy resolutiva ante los problemas técnicos, proactiva y con un deseo constante de asimilar nuevos paradigmas de desarrollo y arquitecturas de red.")
            )
          ),

          React.createElement("div", { className: "title" },
            React.createElement("h2", null, "Sobre mí"),
            React.createElement("p", { className: "separator" })
          ),

          React.createElement("div", { className: "desc" },
            React.createElement("h3", { className: "subtitle" }, "Estudios y Formación"),
            React.createElement("p", null, "He culminado el Grado Superior en Desarrollo de Aplicaciones Web (DAW) en 2026 en el IES Hermenegildo Lanz. Anteriormente, cursé el Grado Medio de Sistemas Microinformáticos y Redes (SMR) en el mismo centro, sentando bases sólidas en redes, hardware e infraestructura."),
            React.createElement("p", null, "Mi meta inmediata es realizar el Grado Superior en Desarrollo de Aplicaciones Multiplataforma (DAM) para complementar mis conocimientos y especializarme también en el desarrollo nativo móvil y de escritorio.")
          ),

          React.createElement("div", { className: "desc" },
            React.createElement("h3", { className: "subtitle" }, "Infraestructura Homelab y DevOps"),
            React.createElement("p", null, "Tengo experiencia en el desarrollo con JavaScript (Node.js, React), HTML5, CSS3, Python y Java."),
            React.createElement("div", { className: "icons about-tech-icons" },
              ['html5', 'js', 'css', 'react', 'node', 'python', 'java', 'docker', 'db'].map((techKey) =>
                React.createElement("i", { className: tech[techKey], key: techKey, title: techKey.toUpperCase() })
              )
            ),
            React.createElement("p", null, "Administro y mantengo mi propio clúster local (homelab) virtualizado con Docker y Docker Compose en una Raspberry Pi. Esto incluye la gestión de servicios de infraestructura activa como Portainer (orquestación), Nextcloud (almacenamiento privado), Pi-hole (servidor DNS seguro), automatización de tareas con N8n, túneles VPN con WireGuard, servidores de bases de datos MariaDB/MySQL y proxy inverso Nginx con certificados de seguridad SSL auto-renovables.")
          ),

          React.createElement("div", { className: "title", id: "vpn" },
            React.createElement("p", { className: "space" }),
            React.createElement("p", { className: "space" }),
            React.createElement("h2", null, "Servicio VPN"),
            React.createElement("p", { className: "separator" })
          ),

          React.createElement("div", { className: "desc full2" },
            React.createElement("a", { href: "https://vpn.almagara.es", target: "_blank", "aria-label": "Ir al portal VPN" },
              React.createElement("img", {
                src: "archivos/vpn/wireguard_logo.webp",
                alt: "Imagen VPN",
                className: "vpn-img",
                style: { width: "160px", height: "auto" },
                width: "320",
                height: "320"
              })
            ),
            React.createElement("p", { className: "space" }),
            React.createElement("p", null, "Click en el logo de WireGuard para ver la información sobre el portal de adquisición y venta de configuraciones de VPN seguras y cifradas")
          ),

          React.createElement("div", { className: "title", id: "curriculum" },
            React.createElement("p", { className: "space" }),
            React.createElement("p", { className: "space" }),
            React.createElement("h2", null, "Currículum"),
            React.createElement("p", { className: "separator" })
          ),

          React.createElement("div", { className: "desc full2" },
            React.createElement("a", { href: "archivos/curriculum/CV_JAVIER_FERNANDEZ.pdf", target: "_blank", "aria-label": "Descargar currículum PDF" },
              React.createElement("img", {
                src: "archivos/curriculum/logo_curriculum.webp",
                alt: "Portada del currículum",
                style: { width: "160px", height: 'auto' },
                width: "320",
                height: "320"
              })
            ),
            React.createElement("p", { className: "space" }),
            React.createElement("p", null, "Haz clic en la imagen superior para ver o descargar mi Currículum detallado con todos mis estudios e hitos académicos.")
          )

        )
      )
    )
  );

};



/***********************
  Project Component
 ***********************/

const Project = props => {
  const tech = {
    sass: 'fab fa-sass',
    css: 'fab fa-css3-alt',
    js: 'fab fa-js-square',
    html5: 'fab fa-html5', // HTML5
    python: 'fab fa-python', // Python
    java: 'fab fa-java', // Java
    react: 'fab fa-react',
    vue: 'fab fa-vuejs',
    d3: 'far fa-chart-bar',
    node: 'fab fa-node',
    docker: 'fab fa-docker',
    db: 'fas fa-database'
  };


  const link = props.link || 'http://';
  const repo = props.repo || 'http://';

  return /*#__PURE__*/(
    React.createElement("div", { className: "project" }, /*#__PURE__*/
      React.createElement("a", { className: "project-link", href: link, target: "_blank", rel: "noopener noreferrer", "aria-label": `Ver imagen grande del proyecto ${props.title}` }, /*#__PURE__*/
        React.createElement("img", { className: "project-image", src: props.img, alt: 'Proyecto de ' + props.title, width: props.imgWidth, height: props.imgHeight })), /*#__PURE__*/

      React.createElement("div", { className: "project-details" }, /*#__PURE__*/
        React.createElement("div", { className: "project-tile" }, /*#__PURE__*/
          React.createElement("p", { className: "icons" },
            props.tech.split(' ').filter(Boolean).map((t) => /*#__PURE__*/
              React.createElement("i", { className: tech[t], key: t, title: t.toUpperCase() }))),


          props.title, ' '),

        props.children, /*#__PURE__*/
        React.createElement("div", { className: "buttons" }, /*#__PURE__*/
          React.createElement("a", { href: repo, target: "_blank", rel: "noopener noreferrer", "aria-label": `Ver repositorio de ${props.title}` }, "Repositorio ", /*#__PURE__*/
            React.createElement("i", { className: "fas fa-external-link-alt" })), /*#__PURE__*/

          React.createElement("a", { href: link, target: "_blank", rel: "noopener noreferrer", "aria-label": `Visitar web de ${props.title}` }, "Ver en la web ", /*#__PURE__*/
            React.createElement("i", { className: "fas fa-external-link-alt" }))))));





};



/***********************
  Projects Component
 ***********************/

const Projects = props => {
  return /*#__PURE__*/(
    React.createElement("section", { id: "projects" }, /*#__PURE__*/
      React.createElement("div", { className: "projects-container" }, /*#__PURE__*/
        React.createElement("div", { className: "heading" }, /*#__PURE__*/
          React.createElement("h2", { className: "title" }, "Mis Proyectos"), /*#__PURE__*/
          React.createElement("p", { className: "separator" }), /*#__PURE__*/
          React.createElement("p", { className: "subtitle" }, "Una selección de mis últimos desarrollos. La mayoría están auto-alojados de forma local en mi Raspberry Pi mediante contenedores Docker y expuestos con proxies inversos seguros.", ' ', /*#__PURE__*/
            React.createElement("p", { className: "space" }), /*#__PURE__*/
            React.createElement("a", { href: "https://github.com/fjfh09", target: "_blank", rel: "noopener noreferrer" }, "GITHUB:"), " Aquí se encuentran mis proyectos y repositorios guardados.")), /*#__PURE__*/






        React.createElement("div", { className: "projects-wrapper" }, /*#__PURE__*/

          React.createElement(Project, {
            title: "Club Shaolin Las Gabias",
            img: '/archivos/logoClubShaolin.webp',
            imgWidth: "400",
            imgHeight: "320",
            tech: "react js python db docker",
            link: "https://clubshaolin.almagara.es",
            repo: "https://github.com/fjfh09/Mi-Web"
          },
            React.createElement("small", null, "Plataforma de Gestión Integral (Dockerizada)"),
            React.createElement("p", null, "Sistema de administración para el Club Shaolin Las Gabias en Granada. Desarrollado con frontend en React (Vite) y backend en Python. Permite la administración de alumnos, horarios, eventos y cobros con base de datos MySQL, todo contenerizado en Docker.")),

          React.createElement(Project, {
            title: "Almagara CA",
            img: '/archivos/logoAlmagaraTexto.webp',
            imgWidth: "400",
            imgHeight: "634",
            tech: "html5 js python docker",
            link: "https://app.almagara.es/",
            repo: "https://github.com/fjfh09/Mi-Web"
          },
            React.createElement("small", null, "Autoridad de Certificación Local"),
            React.createElement("p", null, "Aplicación para la generación, firma y gestión de certificados SSL/TLS y claves criptográficas. Cuenta con un backend robusto basado en Python (Uvicorn/FastAPI) e integrado con proxy inverso.")),

          React.createElement(Project, {
            title: "Portal VPN - WireGuard",
            img: '/archivos/vpn/wireguard_logo_red.webp',
            imgWidth: "400",
            imgHeight: "400",
            tech: "html5 css js docker",
            link: "https://vpn.almagara.es",
            repo: "https://github.com/fjfh09/Mi-Web"
          },
            React.createElement("small", null, "Portal de VPN Privada"),
            React.createElement("p", null, "Portal de información y pasarela para la adquisición de archivos de configuración VPN. Basado en el protocolo de alta velocidad WireGuard, gestionado y desplegado de forma segura mediante Docker.")),

          // React.createElement(Project, {
          //   title: "Granada GPT",
          //   img: '/archivos/logo-granada.png',
          //   tech: "html5 js node css",
          //   link: "https://fjfh.almagara.es/granadaGPT/",
          //   repo: "https://github.com/fjfh09/Mi-Web"
          // },
          //   React.createElement("small", null, "Asistente Virtual sobre el Granada C.F."),
          //   React.createElement("p", null, "Modelo conversacional que responde dudas históricas y estadísticas exclusivas del club de fútbol Granada C.F. Desarrollado con Node.js y JavaScript.")),

          React.createElement(Project, {
            title: "Web de Cortes y Graena",
            img: '/cortesygraena/archivos/informacion/plano.webp',
            imgWidth: "400",
            imgHeight: "213",
            tech: "html5 js css",
            link: "/cortesygraena/",
            repo: "https://github.com/fjfh09/Mi-Web"
          },
            React.createElement("small", null, "Portal de Información Municipal"),
            React.createElement("p", null, "Página web de carácter municipal para dar a conocer la historia, planos y guías de Cortes y Graena. Proyecto escolar enfocado en maquetación responsiva."))
        )
      )
    )
  );
};



/***********************
  Contact Component
 ***********************/

const Contact = props => {
  // Definición del componente ContactForm dentro del componente Contact
  class ContactForm extends React.Component {
    constructor(props) {
      super(props);
      this.state = {
        name: '',
        email: '',
        phone: '',
        message: '',
        status: '', // '', 'sending', 'success', 'error', 'validation_error'
        toastActive: false,
        toastMsg: '',
        toastType: '',
        siteKey: ''
      };
      this.handleChange = this.handleChange.bind(this);
      this.handleSubmit = this.handleSubmit.bind(this);
      this.showToast = this.showToast.bind(this);
      this.hideToast = this.hideToast.bind(this);
      this.toastTimer = null;
    }

    componentWillUnmount() {
      if (this.toastTimer) {
        clearTimeout(this.toastTimer);
      }
    }

    componentDidMount() {
      fetch('send_mail.php?getSiteKey=1')
        .then(response => response.json())
        .then(data => {
          if (data.siteKey) {
            this.setState({ siteKey: data.siteKey });
            
            // Cargar script dinámicamente si no existe
            if (!document.getElementById('recaptcha-script')) {
              const script = document.createElement('script');
              script.id = 'recaptcha-script';
              script.src = 'https://www.google.com/recaptcha/api.js?render=explicit';
              script.async = true;
              script.defer = true;
              script.onload = () => {
                window.grecaptcha.ready(() => {
                  window.grecaptcha.render('recaptcha-container', {
                    'sitekey': data.siteKey
                  });
                });
              };
              document.head.appendChild(script);
            } else if (window.grecaptcha) {
              window.grecaptcha.ready(() => {
                window.grecaptcha.render('recaptcha-container', {
                  'sitekey': data.siteKey
                });
              });
            }
          }
        })
        .catch(err => console.error('Error fetching site key:', err));
    }

    handleChange(event) {
      const { name, value } = event.target;
      this.setState({ [name]: value });
    }

    showToast(message, type) {
      if (this.toastTimer) {
        clearTimeout(this.toastTimer);
      }
      this.setState({
        toastActive: true,
        toastMsg: message,
        toastType: type
      });
      this.toastTimer = setTimeout(this.hideToast, 5000);
    }

    hideToast() {
      this.setState({ toastActive: false });
    }

    handleSubmit(event) {
      event.preventDefault();
      const { name, email, phone, message } = this.state;

      // Validaciones del Frontend
      if (!name.trim()) {
        this.showToast('El nombre es obligatorio.', 'error');
        return;
      }
      if (name.trim().length < 2 || name.trim().length > 100) {
        this.showToast('El nombre debe tener entre 2 y 100 caracteres.', 'error');
        return;
      }

      if (!email.trim()) {
        this.showToast('El correo electrónico es obligatorio.', 'error');
        return;
      }
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email.trim())) {
        this.showToast('El formato del correo electrónico no es válido.', 'error');
        return;
      }

      if (!phone.trim()) {
        this.showToast('El número de teléfono es obligatorio.', 'error');
        return;
      }
      const phoneRegex = /^[0-9\s\+\-\(\)]{7,20}$/;
      if (!phoneRegex.test(phone.trim())) {
        this.showToast('El formato del teléfono no es válido (mínimo 7 caracteres, solo números, espacios, +, -, paréntesis).', 'error');
        return;
      }

      if (!message.trim()) {
        this.showToast('El mensaje es obligatorio.', 'error');
        return;
      }
      if (message.trim().length < 5 || message.trim().length > 5000) {
        this.showToast('El mensaje debe tener entre 5 y 5000 caracteres.', 'error');
        return;
      }

      const recaptchaToken = window.grecaptcha ? window.grecaptcha.getResponse() : '';
      if (!recaptchaToken && this.state.siteKey) {
        this.showToast('Por favor, completa el CAPTCHA.', 'error');
        return;
      }

      this.setState({ status: 'sending' });

      fetch('send_mail.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ name, email, phone, message, recaptchaToken })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          this.setState({
            name: '',
            email: '',
            phone: '',
            message: '',
            status: 'success'
          });
          if (window.grecaptcha) window.grecaptcha.reset();
          this.showToast('¡Mensaje enviado con éxito! Me pondré en contacto contigo pronto.', 'success');
        } else {
          this.setState({ status: 'error' });
          if (window.grecaptcha) window.grecaptcha.reset();
          this.showToast(data.error || 'Hubo un error al enviar el mensaje. Por favor, inténtalo de nuevo.', 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        this.setState({ status: 'error' });
        if (window.grecaptcha) window.grecaptcha.reset();
        this.showToast('No se pudo conectar con el servidor. Por favor, escríbeme directamente a fjavier9906@gmail.com.', 'error');
      });
    }

    render() {
      const toastClass = this.state.toastActive ? 'toast show' : 'toast';
      const toastTypeClass = this.state.toastType === 'success' ? 'success' : 'error';
      const toastIconClass = this.state.toastType === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';

      return React.createElement(React.Fragment, null,
        React.createElement("form", { id: "contact-form", onSubmit: this.handleSubmit },
          React.createElement("label", { htmlFor: "name-input", className: "sr-only" }, "Tu nombre completo"),
          React.createElement("input", { id: "name-input", placeholder: "Tu nombre", name: "name", type: "text", required: true, value: this.state.name, onChange: this.handleChange }),
          
          React.createElement("label", { htmlFor: "email-input", className: "sr-only" }, "Tu correo electrónico de contacto"),
          React.createElement("input", { id: "email-input", placeholder: "Tu correo", name: "email", type: "email", required: true, value: this.state.email, onChange: this.handleChange }),
          
          React.createElement("label", { htmlFor: "phone-input", className: "sr-only" }, "Tu número de teléfono de contacto"),
          React.createElement("input", { id: "phone-input", placeholder: "Tu teléfono", name: "phone", type: "tel", required: true, value: this.state.phone, onChange: this.handleChange }),
          
          React.createElement("label", { htmlFor: "message-input", className: "sr-only" }, "Mensaje de tu consulta"),
          React.createElement("textarea", { id: "message-input", placeholder: "Mensaje", type: "text", name: "message", required: true, value: this.state.message, onChange: this.handleChange }),
          
          React.createElement("div", { id: "recaptcha-container", style: { marginBottom: '15px' } }),

          React.createElement("input", { className: "button", id: "submit", value: this.state.status === 'sending' ? 'Enviando...' : 'Enviar', type: "submit", disabled: this.state.status === 'sending' })
        ),
        
        React.createElement("div", { className: "toast-container" },
          React.createElement("div", { className: `${toastClass} ${toastTypeClass}` },
            React.createElement("div", { className: "toast-content" },
              React.createElement("span", { className: "toast-icon" }, 
                React.createElement("i", { className: toastIconClass })
              ),
              React.createElement("span", { className: "toast-text" }, this.state.toastMsg)
            ),
            React.createElement("button", { className: "toast-close", onClick: this.hideToast, "aria-label": "Cerrar notificación" },
              React.createElement("i", { className: "fas fa-times" })
            )
          )
        )
      );
    }
  }

  return /*#__PURE__*/(
    React.createElement("section", { id: "contacto" }, /*#__PURE__*/
      React.createElement("div", { className: "container" }, /*#__PURE__*/
        React.createElement("div", { className: "heading-wrapper" }, /*#__PURE__*/
          React.createElement("div", { className: "heading" }, /*#__PURE__*/
            React.createElement("p", { className: "title" }, "¿Quieres ", /*#__PURE__*/
              React.createElement("br", null), "contactar conmigo?"), /*#__PURE__*/



            React.createElement("p", { className: "separator" }), /*#__PURE__*/
           ), /*#__PURE__*/






          React.createElement(SocialLinks, null)), /*#__PURE__*/

        // Renderizamos el componente ContactForm dentro del div con el id 'contact-form-container'
        React.createElement(ContactForm, null)
      ))
  );
};




/***********************
  Footer Component
 ***********************/

const Footer = props => {
  return /*#__PURE__*/(
    React.createElement("footer", null, /*#__PURE__*/
      React.createElement("div", { className: "wrapper" }, /*#__PURE__*/
        React.createElement("h2", null, "PORTFOLIO"), /*#__PURE__*/
        React.createElement("p", null, "\xA9 ", new Date().getFullYear(), " Javier Fernandez"), /*#__PURE__*/
        React.createElement(SocialLinks, null))));
};




/***********************
  Social Links Component
 ***********************/

const SocialLinks = props => {
  return /*#__PURE__*/(
    React.createElement("div", { className: "social" }, /*#__PURE__*/
      React.createElement("a", {
        href: "https://www.instagram.com/jaavii0.6/",
        target: "_blank",
        rel: "noopener noreferrer",
        title: "Mi perfil de Instagram",
        "aria-label": "Instagram"
      },

        ' ', /*#__PURE__*/
        React.createElement("i", { className: "fab fa-instagram" })), /*#__PURE__*/

      React.createElement("a", {
        href: "https://twitter.com/fjfh_7",
        target: "_blank",
        rel: "noopener noreferrer",
        title: "Mi perfil de Twitter",
        "aria-label": "Twitter"
      },

        ' ', /*#__PURE__*/
        React.createElement("i", { className: "fab fa-twitter" })), /*#__PURE__*/

      React.createElement("a", {
        id: "profile-link",
        href: "https://github.com/fjfh09",
        target: "_blank",
        rel: "noopener noreferrer",
        title: "Mi perfil de GitHub",
        "aria-label": "GitHub"
      },

        ' ', /*#__PURE__*/
        React.createElement("i", { className: "fab fa-github" })), /*#__PURE__*/

      React.createElement("a", {
        href: "https://codepen.io/fjfh09",
        target: "_blank",
        rel: "noopener noreferrer",
        title: "Mi perfil de Codepen",
        "aria-label": "Codepen"
      },

        ' ', /*#__PURE__*/
        React.createElement("i", { className: "fab fa-codepen" }))));



};



/***********************
  Main Component
 ***********************/

class App extends React.Component {
  constructor(...args) {
    super(...args); _defineProperty(this, "state",
      {
        menuState: false,
        scrollActive: false
      }); _defineProperty(this, "toggleMenu",


        () => {
          this.setState(state => ({
            menuState: !state.menuState ?
              'active' :
              state.menuState === 'deactive' ?
                'active' :
                'deactive'
          }));

        });
  }

  render() {
    return /*#__PURE__*/(
      React.createElement(React.Fragment, null, /*#__PURE__*/
        React.createElement(Menu, { toggleMenu: this.toggleMenu, showMenu: this.state.menuState }), /*#__PURE__*/
        React.createElement(Nav, { toggleMenu: this.toggleMenu, showMenu: this.state.menuState, scrollActive: this.state.scrollActive }), /*#__PURE__*/
        React.createElement(Header, null), /*#__PURE__*/
        React.createElement(About, null), /*#__PURE__*/
        React.createElement(Projects, null), /*#__PURE__*/
        React.createElement(Contact, null), /*#__PURE__*/
        React.createElement(Footer, null)));


  }

  componentDidMount() {
    const header = document.querySelector('#welcome-section');
    const forest = document.querySelector('.forest');
    const silhouette = document.querySelector('.silhouette');
    let forestInitPos = -300;
    
    let winHeight = 1000; // Default fallback
    setTimeout(() => { winHeight = window.innerHeight; }, 0); // Defer to avoid reflow
    window.addEventListener('resize', () => { winHeight = window.innerHeight; });

    const handleScroll = () => {
      let scrollPos = window.scrollY || window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;

      if (scrollPos <= winHeight) {
        if (silhouette) silhouette.style.bottom = `${parseInt(scrollPos / 6)}px`;
        if (forest) forest.style.bottom = `${parseInt(forestInitPos + scrollPos / 6)}px`;
      }

      if (scrollPos - 100 <= winHeight) {
        if (header) header.style.visibility = 'visible';
      } else {
        if (header) header.style.visibility = 'hidden';
      }

      if (scrollPos > 50) {
        if (!this.state.scrollActive) {
          this.setState({ scrollActive: true });
        }
      } else {
        if (this.state.scrollActive) {
          this.setState({ scrollActive: false });
        }
      }
    };

    window.addEventListener('scroll', handleScroll);
    // Ejecutar una vez al cargar por si se inicia con scroll
    handleScroll();

    (function navSmoothScrolling() {
      const internalLinks = document.querySelectorAll('a[href^="#"]');
      for (let i in internalLinks) {
        if (internalLinks.hasOwnProperty(i)) {
          internalLinks[i].addEventListener('click', e => {
            e.preventDefault();
            document.querySelector(internalLinks[i].hash).scrollIntoView({
              block: 'start',
              behavior: 'smooth'
            });

          });
        }
      }
    })();
  }
}



ReactDOM.render( /*#__PURE__*/React.createElement(App, null), document.getElementById('app'));