// server.js
const express = require('express');
const { GoogleGenerativeAI } = require('@google/generative-ai');
const { geminiAPIKey } = require("./oculto/credenciales.json");
const path = require('path');
const bodyParser = require('body-parser');


const app = express();
const port = 3001;
const cors = require('cors');
app.use(cors());

// Middleware
app.use(express.static(path.join(__dirname, 'public')));
app.use(bodyParser.json());

// Ruta para servir la página principal
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'index.html'));
});

// Endpoint para manejar preguntas de la IA
app.post('/get-ai-response', async (req, res) => {
    console.log("Solicitud recibida en /get-ai-response"); // <- Verificar si llega la petición
    console.log("Cuerpo de la solicitud:", req.body); // <- Ver qué datos llegan

    const { pregunta } = req.body;

    if (!pregunta) {
        console.log("Pregunta no proporcionada");
        return res.status(400).json({ response: 'Pregunta no proporcionada' });
    }

    try {
        const genAI = new GoogleGenerativeAI(geminiAPIKey);
        const systemInstruction = `Eres una IA del Granada Club de Futbol también conocido como Granada C.F., 
        solo sabes responder preguntas del Granada C.F. o de fútbol y te sabes toda la historia del Granada C.F. 
        La respuesta dámela en formato normal (sin negritas ni otros formatos), usa saltos de línea cada 100 caracteres 
        y estas actualizada hasta 2025. Los creadores de la IA son Javi y Gregorio, la web de Javi es https://fjfh06.ddns.net 
        y la de Gregorio es https://gregolopez.es, que tus respuestas sean mas emocionantes 
        entorno a la pregunta que te hagan o mas intrigantes`;
        const modelo = genAI.getGenerativeModel({ model: "gemini-2.0-flash", systemInstruction });

        const parts = [
            { text: "input: ¿Qué es el Granada C.F.?" },
            { text: "output: Es un club de futbol de la Ciudad de Granada que se fundo 1931" },
            { text: `input: ${pregunta}` }, 
            { text: `output: ` }
        ];
        const generationConfig = { maxOutputTokens: 400 };

        console.log("Enviando a la IA:", parts);
        const result = await modelo.generateContent({
            contents: [{ role: "user", parts }],
            generationConfig,
        });

        console.log("Respuesta de la IA:", result.response.text());
        res.json({ response: result.response.text() });
    } catch (error) {
        console.error("Error en la IA:", error);
        res.status(500).json({ response: 'Error al obtener respuesta de la IA' });
    }
});


// Iniciar el servidor
app.listen(port, () => {
    console.log(`Servidor funcionando en http://localhost:${port}`);
});
