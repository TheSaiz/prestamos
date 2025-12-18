// ===================================================================
// CHATBOT FLOW - SISTEMA DE PREGUNTAS DINÁMICO (PRODUCCIÓN - MODO ESPEJO)
// Para ubicar en: /system/js/chatbot_flow.js
// ===================================================================

let flowQuestions = [];
let currentStep = 0;

// DNI
let dniValidadoData = null;

// TELÉFONO
let areaIngresada = "";
let telefonoIngresado = "";

// Códigos de área
let areaCodes = {};
let areaCodesList = [];

window.chatbotActivo = true;

// ===================================================================
// VALIDAR EMAIL
// ===================================================================
function validarEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// ===================================================================
// UTILIDADES
// ===================================================================
function limpiarNumero(v) {
    return String(v || "").replace(/[^\d]/g, "");
}

function randomItem(arr) {
    return arr[Math.floor(Math.random() * arr.length)];
}

function sugerirCodigos(input) {
    return areaCodesList
        .map(c => {
            let score = 0;
            if (c.startsWith(input)) score += 10;
            score -= Math.abs(c.length - input.length);
            return { c, score };
        })
        .sort((a, b) => b.score - a.score)
        .slice(0, 3)
        .map(x => x.c);
}

// ===================================================================
// CARGAR CÓDIGOS DE ÁREA
// ===================================================================
async function loadAreaCodes() {
    try {
        const r = await fetch("/system/area_codes_ar.json", { cache: "no-store" });
        const text = await r.text();
        if (text.trim().startsWith("<")) throw new Error("HTML recibido");

        areaCodes = JSON.parse(text);
        areaCodesList = Object.keys(areaCodes);
    } catch {
        console.warn("⚠️ area_codes_ar.json no disponible");
        areaCodes = {};
        areaCodesList = [];
    }
}

// ===================================================================
// GUARDAR MENSAJE BOT
// ===================================================================
async function guardarMensajeBot(texto) {
    if (!window.chatbotActivo || !window.chatId) return;

    const fd = new FormData();
    fd.append("chat_id", chatId);
    fd.append("sender", "bot");
    fd.append("message", texto);

    await fetch("/system/api/messages/send_message.php", {
        method: "POST",
        body: fd
    });
}

function botSay(texto) {
    addBotMessage(texto);
    guardarMensajeBot(texto);
}

// ===================================================================
// CARGAR FLUJO
// ===================================================================
async function loadChatbotFlow() {
    const r = await fetch("/system/api/chatbot/get_full_flow.php", { cache: "no-store" });
    const d = await r.json();

    if (!d.success || !Array.isArray(d.data)) {
        console.error("Flujo inválido:", d);
        return;
    }

    flowQuestions = d.data;
    currentStep = 0;
}

// ===================================================================
// OPCIONES
// ===================================================================
function showOptions(options) {
    chatInput.disabled = true;

    const c = document.createElement("div");
    c.className = "chat-options-container";

    (options || []).forEach(opt => {
        const b = document.createElement("button");
        b.className = "chat-option-btn";
        b.textContent = opt.texto;

        b.onclick = async () => {
            c.remove();
            addUserMessage(opt.texto);
            await saveAnswer(flowQuestions[currentStep].id, opt.texto, opt.id);
            await avanzar();
        };

        c.appendChild(b);
    });

    chatMessages.appendChild(c);
    scrollToBottom();
}

// ===================================================================
// CONFIRMACIÓN SI / NO
// ===================================================================
function confirmar(texto, onSi, onNoRepetir) {
    chatInput.disabled = true;

    const c = document.createElement("div");
    c.className = "chat-options-container";
    c.innerHTML = `
        <div class="msg-bot"><strong>${texto}</strong></div>
        <button class="chat-option-btn">✅ Sí</button>
        <button class="chat-option-btn">❌ No</button>
    `;

    guardarMensajeBot(texto);

    c.children[1].onclick = async () => {
        c.remove();
        chatInput.disabled = false;
        await onSi();
    };

    c.children[2].onclick = () => {
        c.remove();
        chatInput.disabled = false;
        onNoRepetir();
    };

    chatMessages.appendChild(c);
    scrollToBottom();
}

// ===================================================================
// INPUT USUARIO
// ===================================================================
async function handleUserInput(text) {
    if (!window.chatbotActivo) {
        enviarMensajeLibre(text);
        return;
    }

    const q = flowQuestions[currentStep];
    if (!q) return;

    addUserMessage(text);

    // DNI
    if (q.id === 2) {
        botSay("⏳ Validando tu DNI...");
        chatInput.disabled = true;

        try {
            const r = await fetch("/system/api/chatbot/validar_dni.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ dni: text })
            });

            const d = await r.json();
            chatInput.disabled = false;

            if (!d.success) {
                botSay("❌ DNI inválido");
                return;
            }

            dniValidadoData = d;

            confirmar(
                `¿Sos ${d.nombre}?`,
                async () => {
                    await saveAnswer(q.id, d.nombre, 0, d.cuil, d.nombre, text);
                    await avanzar();
                },
                () => botSay(q.pregunta)
            );
            return;
        } catch {
            chatInput.disabled = false;
            botSay("❌ Error validando DNI");
            return;
        }
    }

    // CÓDIGO DE ÁREA (pregunta_id esperado: 4)
    if (q.pregunta?.includes("código de área")) {
        const limpio = limpiarNumero(text);

        if (!/^\d{1,4}$/.test(limpio)) {
            botSay("❌ Ingresá solo números (1 a 4 dígitos)");
            return;
        }

        if (!areaCodes[limpio]) {
            const sugerencias = sugerirCodigos(limpio);
            botSay(sugerencias.length
                ? `❌ Código inválido. Ej: ${sugerencias.join(", ")}`
                : "❌ Código inválido"
            );
            return;
        }

        areaIngresada = limpio;

        confirmar(
            `Ingresaste ${limpio} (${areaCodes[limpio].ciudad}), ¿es correcto?`,
            async () => {
                await saveAnswer(q.id, areaIngresada);
                await avanzar();
            },
            () => botSay(q.pregunta)
        );
        return;
    }

    // TELÉFONO (pregunta_id esperado: 5)
    if (q.pregunta?.includes("número de teléfono")) {
        const limpio = limpiarNumero(text);

        if (!/^\d+$/.test(limpio)) {
            botSay("❌ Solo números");
            return;
        }

        // espejo: total 10 dígitos (área + número)
        const requerido = 10 - (areaIngresada ? areaIngresada.length : 0);

        if (!areaIngresada) {
            botSay("⚠️ Primero ingresá tu código de área.");
            return;
        }

        if (limpio.length !== requerido) {
            botSay(`❌ El número debe tener ${requerido} dígitos`);
            return;
        }

        telefonoIngresado = limpio;

        confirmar(
            `Ingresaste ${areaIngresada} - ${telefonoIngresado}, ¿es correcto?`,
            async () => {
                // ✅ CLAVE: guardamos SOLO el teléfono SIN código de área (modo espejo)
                await saveAnswer(q.id, telefonoIngresado);
                await avanzar();
            },
            () => botSay(q.pregunta)
        );
        return;
    }

    // EMAIL (pregunta_id esperado: 8)
    if (q.pregunta?.includes("correo electrónico")) {
        if (!validarEmail(text)) {
            botSay("❌ Email inválido");
            return;
        }

        confirmar(
            `Ingresaste el correo:\n${text}\n¿Es correcto?`,
            async () => {
                await saveAnswer(q.id, text, 0, "", "", "", true);
                await avanzar();
            },
            () => botSay(q.pregunta)
        );
        return;
    }

    // OTROS
    await saveAnswer(q.id, text);
    await avanzar();
}

// ===================================================================
// AVANZAR
// ===================================================================
async function avanzar() {
    currentStep++;

    if (currentStep >= flowQuestions.length) {
        await finalizarChatbot();
        return;
    }

    const q = flowQuestions[currentStep];

    if (q.pregunta?.includes("código de área") && areaCodesList.length) {
        const ej = randomItem(areaCodesList);
        botSay(`${q.pregunta} (ej: ${ej})`);
    } else {
        botSay(q.pregunta);
    }

    if (q.tipo === "opcion") {
        showOptions(q.opciones || []);
    } else {
        chatInput.disabled = false;
        chatInput.focus();
    }
}

// ===================================================================
// FINALIZAR
// ===================================================================
async function finalizarChatbot() {
    const r = await fetch("/system/api/chat/mark_waiting_asesor.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ chat_id: chatId })
    });

    const d = await r.json();

    if (!d.success) {
        botSay("⚠️ Error al finalizar");
        return;
    }

    window.chatbotActivo = false;
    botSay("👨‍💼 Un asesor se comunicará contigo.");
}

// ===================================================================
// GUARDAR RESPUESTA
// ===================================================================
async function saveAnswer(questionId, answer, optionId = 0, cuil = "", nombre = "", dni = "", confirmado = false) {
    if (!window.chatbotActivo) return;

    const fd = new FormData();
    fd.append("chat_id", chatId);
    fd.append("question_id", questionId);
    fd.append("answer", answer);
    fd.append("option_id", optionId);

    if (cuil) fd.append("cuil", cuil);
    if (nombre) fd.append("nombre", nombre);
    if (dni) fd.append("dni", dni);
    if (confirmado) fd.append("confirmado", "1");

    await fetch("/system/api/chatbot/save_answer.php", {
        method: "POST",
        body: fd
    });
}

// ===================================================================
// INIT
// ===================================================================
(async () => {
    await loadAreaCodes();
    await loadChatbotFlow();
})();
