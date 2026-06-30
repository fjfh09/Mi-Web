// Obtener elementos del DOM
const sendButton = document.getElementById('send-button');
const userInput = document.getElementById('user-input');
const responseContainer = document.getElementById('response-container');
const responseDiv = document.getElementById('response');
const spinner = document.getElementById('spinner');

let thinkingDotsInterval;

// Funcion para manejar el envio de preguntas y respuestas
async function handleQuestion() {
    const pregunta = userInput.value.trim(); // Captura la pregunta del usuario

    if (pregunta !== "") {
        // Mostrar contenedor de respuesta
        responseContainer.style.display = 'block';

        // Iniciar animacion
        startThinkingAnimation();

        // Generar respuesta desde la IA
        const response = await getAIResponse(pregunta);

        // Detener animacion
        stopThinkingAnimation();

        // Mostrar la respuesta
        responseDiv.textContent = `${response}`;

        // Limpiar el input
        userInput.value = '';
    }
}

// Funcion para obtener la respuesta de la IA desde el servidor
async function getAIResponse(pregunta2) {
    try {
        const response = await fetch('https://fjfh.almagara.es/granadaGPT/get-ai-response', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ pregunta: pregunta2 }),
        });

        if (!response.ok) {
            throw new Error("Error en la respuesta del servidor");
        }

        const data = await response.json();
        return data.response;
    } catch (error) {
        console.log("Error al obtener respuesta de la IA:", error);
        return "Hubo un error al procesar tu pregunta.";
    }
}

// Mostrar spinner + texto pensando
function startThinkingAnimation() {
    spinner.style.display = 'block';
    
    let dots = 0;
    responseDiv.textContent = "Pensando";

    thinkingDotsInterval = setInterval(() => {
        dots = (dots + 1) % 4; // 0, 1, 2, 3
        responseDiv.textContent = "Pensando" + ".".repeat(dots);
    }, 250);
}


// Ocultar spinner
function stopThinkingAnimation() {
    spinner.style.display = 'none';
    clearInterval(thinkingDotsInterval);
}


// Evento al hacer clic en el boton de enviar
sendButton.addEventListener('click', handleQuestion);

// Permitir enviar con Enter
userInput.addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        handleQuestion();
        userInput.blur();
    }
});
