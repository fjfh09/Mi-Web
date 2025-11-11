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
            React.createElement("a", { href: "#contact", onClick: props.toggleMenu }, "CONTACTO"))), /*#__PURE__*/




        React.createElement(SocialLinks, null))));



};


/***********************
  Nav Component
 ***********************/

const Nav = props => {
  return /*#__PURE__*/(
    React.createElement(React.Fragment, null, /*#__PURE__*/
      React.createElement("nav", { id: "navbar" }, /*#__PURE__*/
        React.createElement("div", { className: "nav-wrapper" }, /*#__PURE__*/
          React.createElement("p", { className: "brand" }, "Javier ", /*#__PURE__*/

            React.createElement("strong", null, "Fernández")), /*#__PURE__*/

          React.createElement("a", {
            onClick: props.toggleMenu,
            className: props.showMenu === 'active' ? 'menu-button active' : 'menu-button'
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
          React.createElement("span", { className: "line" }, "Estudiante"), /*#__PURE__*/
          React.createElement("span", { className: "line" }, /*#__PURE__*/
            React.createElement("span", { className: "color" }, "&"), " Programador")), /*#__PURE__*/


        React.createElement("div", { className: "buttons" }, /*#__PURE__*/
          React.createElement("a", { href: "#vpn" }, "Venta de VPN"),
          React.createElement("a", { href: "#projects" }, "Mis proyectos"), /*#__PURE__*/
          React.createElement("a", { href: "archivos/curriculum/CV_JAVIER_FERNANDEZ.pdf", target: "_blank", className: "cta" }, "Curriculum"),
          React.createElement("a", { href: "#contact", className: "cta" }, "Contacto")
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
    node: 'fab fa-node'
  };


  return /*#__PURE__*/(
    React.createElement("section", { id: "about" }, /*#__PURE__*/
      React.createElement("div", { className: "wrapper" }, /*#__PURE__*/
        React.createElement("article", null,

          // 🔽 QUIEN SOY
          React.createElement("div", { className: "title" },
            React.createElement("h3", null, "¿Quién soy?"),
            React.createElement("p", { className: "separator" })
          ),

          React.createElement("div", { className: "desc full" },
            React.createElement("h4", { className: "subtitle" }, "Mi nombre es Javier."),
            React.createElement("p", null, "Soy un estudiante del IES Hermenegildo Lanz que ha aprendido a programar bots de Discord en JavaScript, nací en Granada, España."),
            React.createElement("p", null, "Mi punto fuerte es ser resolutivo cuando tengo algún problema con algún código; por ejemplo, me las ingenio para conseguir resolverlo. También tengo muchas ganas de aprender más lenguajes de programación, además de los que ya manejo.")
          ),

          React.createElement("div", { className: "title" },
            React.createElement("h3", null, "Sobre mí"),
            React.createElement("p", { className: "separator" })
          ),

          React.createElement("div", { className: "desc" },
            React.createElement("h4", { className: "subtitle" }, "Estudios"),
            React.createElement("p", null, "Estudio el Grado Superior en Desarrollo de Aplicaciones Web (DAW) en el IES Hermenegildo Lanz, y cuando termine este grado, me inscribiré en el Grado Superior de Desarrollo de Aplicaciones Multiplataforma (DAM)."),
            React.createElement("p", null, "Ingresé en el Grado Superior tras cursar el Grado Medio de Sistemas Microinformáticos y Redes en el IES Politécnico Hermenegildo Lanz.")
          ),

          React.createElement("div", { className: "desc" },
            React.createElement("h4", { className: "subtitle" }, "Experiencia en programación"),
            React.createElement("p", null, "He llegado a programar en JavaScript, HTML5 y CSS3. También he probado a programar en Python, TypeScript, Kotlin y Java."),
            React.createElement("div", { className: "icons" },
              ['html5', 'js', 'css', 'python', 'kotlin', 'java'].map((techKey) =>
                React.createElement("i", { className: tech[techKey], key: techKey })
              )
            ),
            React.createElement("p", null, "Aunque he programado en varios lenguajes, el que mejor manejo es JavaScript, debido a que tengo la posibilidad de crear páginas web e incluso bots de Discord. Mi mejor bot creado se llama Team Galaxy para el servidor Mystic Galaxy de Discord, donde soy propietario, y cuenta con 580 miembros.")
          ),

          React.createElement("div", { className: "title", id: "vpn" },
            React.createElement("p", { className: "space" }),
            React.createElement("p", { className: "space" }),
            React.createElement("h3", null, "Venta de VPN"),
            React.createElement("p", { className: "separator" })
          ),

          React.createElement("div", { className: "desc full2" },
            React.createElement("a", { href: "https://fjfh06.ddns.net/vpn/", target: "_blank" },
              React.createElement("img", {
                src: "archivos/vpn/wireguard_logo.png",
                alt: "Imagen VPN",
                className: "vpn-img", // <-- clase para aplicar los efectos
                style: { width: "200px", height: "auto" }
              })

            ),
            React.createElement("p", { className: "space" }),
            React.createElement("p", null, "Clicka en el logo y verás la información sobre la venta de una VPN.")
          ),

          React.createElement("div", { className: "title", id: "curriculum" },
            React.createElement("p", { className: "space" }),
            React.createElement("p", { className: "space" }),
            React.createElement("h3", null, "Mi Currículum"),
            React.createElement("p", { className: "separator" })
          ),

          React.createElement("div", { className: "desc full2" },
            React.createElement("a", { href: "archivos/curriculum/CV_JAVIER_FERNANDEZ.pdf", target: "_blank" },
              React.createElement("img", {
                src: "archivos/curriculum/logo_curriculum.png",
                alt: "Portada del currículum",
                style: { width: "200px", height: 'auto' }
              })
            ),
            React.createElement("p", { className: "space" }),
            React.createElement("p", null, "Para más información sobre mis estudios y experiencia pincha en la imagen.")
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
    node: 'fab fa-node'
  };


  const link = props.link || 'http://';
  const repo = props.repo || 'http://';

  return /*#__PURE__*/(
    React.createElement("div", { className: "project" }, /*#__PURE__*/
      React.createElement("a", { className: "project-link", href: link, target: "_blank", rel: "noopener noreferrer" }, /*#__PURE__*/
        React.createElement("img", { className: "project-image", src: props.img, alt: 'Proyecto de ' + props.title })), /*#__PURE__*/

      React.createElement("div", { className: "project-details" }, /*#__PURE__*/
        React.createElement("div", { className: "project-tile" }, /*#__PURE__*/
          React.createElement("p", { className: "icons" },
            props.tech.split(' ').map((t) => /*#__PURE__*/
              React.createElement("i", { className: tech[t], key: t }))),


          props.title, ' '),

        props.children, /*#__PURE__*/
        React.createElement("div", { className: "buttons" }, /*#__PURE__*/
          React.createElement("a", { href: repo, target: "_blank", rel: "noopener noreferrer" }, "Repositorio ", /*#__PURE__*/
            React.createElement("i", { className: "fas fa-external-link-alt" })), /*#__PURE__*/

          React.createElement("a", { href: link, target: "_blank", rel: "noopener noreferrer" }, "Ver en la web ", /*#__PURE__*/
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
          React.createElement("h3", { className: "title" }, "Mis Proyectos"), /*#__PURE__*/
          React.createElement("p", { className: "separator" }), /*#__PURE__*/
          React.createElement("p", { className: "subtitle" }, "Lista ", /*#__PURE__*/
            React.createElement("u", null, ""), "de todos los trabajos que he hecho por mi cuenta o en HLC.", ' ', /*#__PURE__*/
            React.createElement("p", { className: "space" }), /*#__PURE__*/
            React.createElement("a", { href: "https://github.com/fjfh09", target: "_blank", rel: "noopener noreferrer" }, "GITHUB:"), " Aquí se encuentran mis proyectos y repositorios guardados.")), /*#__PURE__*/






        React.createElement("div", { className: "projects-wrapper" }, /*#__PURE__*/

          React.createElement(Project, {
            title: "Granada GPT",
            img: '/archivos/logo-granada.png',
            tech: "html5 js node css ",
            link: "https://fjfh06.ddns.net/proyectoLMSGI/",
            repo: "https://github.com/fjfh09/fjfh09.github.io",
            className: "granada-project" // <- AÑADIDO
          },


            React.createElement("small", null, "IA del Granada C.F. para Lenguaje de Marcas"), /*#__PURE__*/


            React.createElement("p", null, "Esta IA sabe todo sobre el Granada C.F. puedes preguntarle cosas sobre el club pero si preguntas cualquier otra cosa no sabe responder esa cierta pregunta.")), /*#__PURE__*/

          React.createElement(Project, {
            title: "Discord Hunter",
            img: '/discord_hunter/fotos/logo_dh.png',
            tech: "html5 js node css",
            link: "/discord_hunter/index.html",
            repo: "https://github.com/fjfh09/fjfh09.github.io"
          }, /*#__PURE__*/

            React.createElement("small", null, "Minijuego de un bot de discord "), /*#__PURE__*/


            React.createElement("p", null, "Juego llamado Discord Hunter, que consiste en poner comandos y spamearlos: Tendrás que subir de nivel, de prestigio, comprar armas, conseguir armas míticas, crear tus propias armas. También podrás jugar diferentes modos de juegos con amigos, como supervivencia incursiones, battle royale y más modos que no puedo desvelar. Todo lo que se puede hacer en otros prestigios es sorpresa y lo descubriréis al llegar a un específico nivel.")), /*#__PURE__*/


          /*React.createElement(Project, {
            title: "San Valentin.",
            img: '',
            tech: "js css html ",
            link: "/sanvalentin/index.html",
            repo: "https://github.com/fjfh09/fjfh09.github.io"
          }, 

            React.createElement("small", null, "Esto es una web para mi novia del Dia de San Valentin."),


            React.createElement("p", null, "Esta pagina se hizo con ayuda del profesor de HLC que me dio varios consejos para que quedase lo mejor posible.")),
            */

          React.createElement(Project, {
            title: "Web de Cortes y Graena.",
            img: '/cortesygraena/archivos/informacion/plano.png',
            tech: "html5 js css",
            link: "/cortesygraena/index.html",
            repo: "https://github.com/fjfh09/fjfh09.github.io"
          }, /*#__PURE__*/

            React.createElement("small", null, "Web hecha para un proyecto de Aplicaciones Web"), /*#__PURE__*/


            React.createElement("p", null, "Información que corresponde a mi Pueblo.")), /*#__PURE__*/


          /*
          React.createElement(Project, {
            title: "Juego de Cartas.",
            img: '/juegocartas/images/image.png',
            tech: "html5 js css",
            link: "/juegocartas/index.html",
            repo: "https://github.com/fjfh09/fjfh09.github.io"
          },

            React.createElement("small", null, "Esto es un juego simulando el 'memory'"),
            React.createElement("p", null, "Es muy divertido y además hay fotos de algunos compañeros del Kung-Fu (deporte que practico)")
          
          )
          */
        ))));
            
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
        message: ''
      };
      this.handleChange = this.handleChange.bind(this);
      this.handleSubmit = this.handleSubmit.bind(this);
    }

    handleChange(event) {
      const { name, value } = event.target;
      this.setState({ [name]: value });
    }

    handleSubmit(event) {
      event.preventDefault();
      const { name, email, message } = this.state;
      const subject = encodeURIComponent(`Soy ${name}, `);
      const body = encodeURIComponent(message);
      const correo = encodeURIComponent(`Mi Correo es: ${email}`);
      window.location.href = `mailto:fjavier9906@gmail.com?subject=${subject}${correo}&body=${body}`;
    }

    render() {
      return (
        React.createElement("form", { id: "contact-form", onSubmit: this.handleSubmit },
          React.createElement("input", { placeholder: "Tu nombre", name: "name", type: "text", required: true, value: this.state.name, onChange: this.handleChange }),
          React.createElement("input", { placeholder: "Tu correo", name: "email", type: "email", required: true, value: this.state.email, onChange: this.handleChange }),
          React.createElement("textarea", { placeholder: "Mensaje", type: "text", name: "message", required: true, value: this.state.message, onChange: this.handleChange }),
          React.createElement("input", { className: "button", id: "submit", value: "Enviar", type: "submit" })
        )
      );
    }
  }

  return /*#__PURE__*/(
    React.createElement("section", { id: "contact" }, /*#__PURE__*/
      React.createElement("div", { className: "container" }, /*#__PURE__*/
        React.createElement("div", { className: "heading-wrapper" }, /*#__PURE__*/
          React.createElement("div", { className: "heading" }, /*#__PURE__*/
            React.createElement("p", { className: "title" }, "¿Quieres ", /*#__PURE__*/
              React.createElement("br", null), "contactar conmigo?"), /*#__PURE__*/



            React.createElement("p", { className: "separator" }), /*#__PURE__*/
            React.createElement("p", { className: "subtitle" }, "Envía un correo a ",
              '', /*#__PURE__*/
              React.createElement("span", { className: "mail" }, "fjavier9906", /*#__PURE__*/

                React.createElement("i", { className: "fas fa-at at" }), "gmail", /*#__PURE__*/

                React.createElement("i", { className: "fas fa-circle dot" }), "com"), ":")), /*#__PURE__*/






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
        React.createElement("h3", null, "GRACIAS POR VER"), /*#__PURE__*/
        React.createElement("p", null, "\xA9 ", new Date().getFullYear(), " Javi Fernandez."), /*#__PURE__*/
        React.createElement(SocialLinks, null))));



};




/***********************
  Social Links Component
 ***********************/

const SocialLinks = props => {
  return /*#__PURE__*/(
    React.createElement("div", { className: "social" }, /*#__PURE__*/
      React.createElement("a", {
        id: "profile-link",
        href: "https://www.instagram.com/jaavii0.6/",
        target: "_blank",
        rel: "noopener noreferrer",
        title: "Mi perfil de Instagram"
      },

        ' ', /*#__PURE__*/
        React.createElement("i", { className: "fab fa-instagram" })), /*#__PURE__*/

      React.createElement("a", {
        href: "https://twitter.com/fjfh_7",
        target: "_blank",
        rel: "noopener noreferrer",
        title: "Mi perfil de Twitter"
      },

        ' ', /*#__PURE__*/
        React.createElement("i", { className: "fab fa-twitter" })), /*#__PURE__*/

      React.createElement("a", {
        id: "profile-link",
        href: "https://github.com/fjfh09",
        target: "_blank",
        rel: "noopener noreferrer",
        title: "Mi perfil de GitHub"
      },

        ' ', /*#__PURE__*/
        React.createElement("i", { className: "fab fa-github" })), /*#__PURE__*/

      React.createElement("a", {
        href: "https://codepen.io/fjfh09",
        target: "_blank",
        rel: "noopener noreferrer",
        title: "Mi perfil de Codepen"
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
        menuState: false
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
        React.createElement(Nav, { toggleMenu: this.toggleMenu, showMenu: this.state.menuState }), /*#__PURE__*/
        React.createElement(Header, null), /*#__PURE__*/
        React.createElement(About, null), /*#__PURE__*/
        React.createElement(Projects, null), /*#__PURE__*/
        React.createElement(Contact, null), /*#__PURE__*/
        React.createElement(Footer, null)));


  }

  componentDidMount() {
    const navbar = document.querySelector('#navbar');
    const header = document.querySelector('#welcome-section');
    const forest = document.querySelector('.forest');
    const silhouette = document.querySelector('.silhouette');
    let forestInitPos = -300;

    window.onscroll = () => {
      let scrollPos = document.documentElement.scrollTop || document.body.scrollTop;

      if (scrollPos <= window.innerHeight) {
        silhouette.style.bottom = `${parseInt(scrollPos / 6)}px`;
        forest.style.bottom = `${parseInt(forestInitPos + scrollPos / 6)}px`;
      }

      if (scrollPos - 100 <= window.innerHeight)
        header.style.visibility = header.style.visibility === 'hidden' && 'visible'; else
        header.style.visibility = 'hidden';

      if (scrollPos + 100 >= window.innerHeight) navbar.classList.add('bg-active'); else
        navbar.classList.remove('bg-active');
    };

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