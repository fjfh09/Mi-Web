document.addEventListener('DOMContentLoaded', () => {
    // Set dynamic year
    const yearSpan = document.getElementById('currentYear');
    if(yearSpan) {
        yearSpan.textContent = new Date().getFullYear();
    }

    // Mobile menu toggle
    const hamburger = document.querySelector('.hamburger');
    const navLinks = document.querySelector('.nav-links');
    const links = document.querySelectorAll('.nav-links li a');

    hamburger.addEventListener('click', () => {
        hamburger.classList.toggle('active');
        navLinks.classList.toggle('active');
    });

    links.forEach(link => {
        link.addEventListener('click', () => {
            hamburger.classList.remove('active');
            navLinks.classList.remove('active');
        });
    });

    // Cargar reCAPTCHA dinámicamente
    fetch('send_mail.php?getSiteKey=1')
        .then(res => res.json())
        .then(data => {
            if (data.siteKey) {
                window.clasesGemmaSiteKey = data.siteKey;
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
                                    'sitekey': data.siteKey
                                });
                            });
                        }
                    };
                    document.head.appendChild(script);
                }
            }
        })
        .catch(err => console.error("Error cargando Site Key reCAPTCHA:", err));

    // Navbar scroll effect
    const navbar = document.querySelector('.navbar');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.style.background = 'var(--glass-bg)';
            navbar.style.boxShadow = 'var(--glass-shadow)';
        } else {
            navbar.style.boxShadow = 'none';
            navbar.style.background = 'var(--glass-bg)';
        }
    });

    // Textarea auto-resize
    const messageInput = document.getElementById('message');
    if (messageInput) {
        messageInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    }

    // Form submission
    const form = document.getElementById('contactForm');
    const formStatus = document.getElementById('formStatus');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerText;

        const recaptchaToken = window.grecaptcha ? window.grecaptcha.getResponse() : '';
        if (!recaptchaToken && window.clasesGemmaSiteKey) {
            showToast('Por favor, completa el CAPTCHA.', 'error');
            return;
        }
        data.recaptchaToken = recaptchaToken;

        submitBtn.innerText = 'Enviando...';
        submitBtn.disabled = true;

        try {
            const response = await fetch('send_mail.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                showToast('¡Mensaje enviado con éxito! Te responderé pronto.', 'success');
                form.reset();
                if (messageInput) messageInput.style.height = 'auto';
            } else {
                showToast(result.error || 'Hubo un error al enviar el mensaje. Intenta de nuevo.', 'error');
            }
            if (window.grecaptcha) window.grecaptcha.reset();
        } catch (error) {
            showToast('Hubo un error de conexión. Intenta de nuevo.', 'error');
            if (window.grecaptcha) window.grecaptcha.reset();
        } finally {
            submitBtn.innerText = originalBtnText;
            submitBtn.disabled = false;
        }
    });

    // Toast Notification logic
    function showToast(message, type) {
        const toast = document.getElementById('toast');
        if (!toast) return;
        const toastMessage = toast.querySelector('.toast-message');
        const toastIcon = toast.querySelector('.toast-icon');

        toast.className = `toast show ${type}`;
        toastMessage.textContent = message;
        toastIcon.innerHTML = type === 'success' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-exclamation-circle"></i>';

        setTimeout(() => {
            toast.className = 'toast';
        }, 4000);
    }
});
