// ===================================================================
// CHATBOT FLOW - SISTEMA DE PREGUNTAS DINÁMICO
// ===================================================================

// ===============================
// VARIABLES DE ESTADO
// ===============================
let flowQuestions = [];
let currentStep = 0;
let waitingForAsesor = false;

// DNI
let dniValidadoData = null;

// TELÉFONO
let areaIngresada = "";
let telefonoIngresado = "";

// Estado global (NO redeclarar en otro archivo)
window.chatbotActivo = true;

// ===================================================================
// UTILIDAD: VALIDAR EMAIL
// ===================================================================
function validarEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// ===================================================================
// GUARDAR MENSAJE DEL BOT EN BD
// ===================================================================
async function guardarMensajeBot(texto) {
    if (!window.chatbotActivo || !window.chatId) return;

    const fd = new FormData();
    fd.append("chat_id", chatId);
    fd.append("sender", "bot");
    fd.append("message", texto);

    try {
        await fetch("/system/api/messages/send_message.php", {
            method: "POST",
            body: fd
        });
    } catch (e) {
        console.error("Error guardando mensaje bot:", e);
    }
}

// ===================================================================
// BOT DICE
// ===================================================================
function botSay(texto) {
    addBotMessage(texto);
    guardarMensajeBot(texto);
}

// ===================================================================
// CARGAR FLUJO DESDE BD
// ===================================================================
async function loadChatbotFlow() {
    try {
        const r = await fetch("/system/api/chatbot/get_full_flow.php");
        const d = await r.json();

        if (!d.success || !Array.isArray(d.data)) {
            console.error("Flujo inválido");
            return;
        }

        flowQuestions = d.data;
        currentStep = 0;

    } catch (e) {
        console.error("Error cargando flujo:", e);
    }
}

// ===================================================================
// MOSTRAR OPCIONES
// ===================================================================
function showOptions(options) {
    const container = document.createElement("div");
    container.className = "chat-options-container";

    options.forEach(opt => {
        const btn = document.createElement("button");
        btn.className = "chat-option-btn";
        btn.textContent = opt.texto;

        btn.onclick = async () => {
            container.remove();
            addUserMessage(opt.texto);
            await saveAnswer(flowQuestions[currentStep].id, opt.texto, opt.id);
            await avanzar();
        };

        container.appendChild(btn);
    });

    chatMessages.appendChild(container);
    scrollToBottom();
}

// ===================================================================
// CONFIRMACIÓN SI / NO
// ===================================================================
function confirmar(texto, onSi, onNo) {
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
        onNo();
    };

    chatMessages.appendChild(c);
    scrollToBottom();
}

// ===================================================================
// INPUT DEL USUARIO
// ===================================================================
async function handleUserInput(text) {

    if (!window.chatbotActivo) {
        enviarMensajeLibre(text);
        return;
    }

    const q = flowQuestions[currentStep];
    if (!q) return;

    addUserMessage(text);

    // ================= DNI =================
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
                    // ✅ ACÁ SE ENVÍA EL DNI REAL
                    await saveAnswer(
                        q.id,
                        d.nombre,
                        0,
                        d.cuil,
                        d.nombre,
                        text // DNI real
                    );
                    await avanzar();
                },
                resetChatbot
            );
            return;

        } catch {
            chatInput.disabled = false;
            botSay("❌ Error de conexión");
            return;
        }
    }

    // ================= CÓDIGO DE ÁREA =================
    if (q.pregunta.includes("código de área")) {

        const limpio = text.replace("+", "");

        if (!/^\d{1,4}$/.test(limpio)) {
            botSay("❌ Ingresá solo números");
            return;
        }

        areaIngresada = limpio;

        confirmar(
            `Ingresaste ${areaIngresada}, ¿es correcto?`,
            async () => {
                await saveAnswer(q.id, areaIngresada);
                await avanzar();
            },
            resetChatbot
        );
        return;
    }

    // ================= TELÉFONO =================
    if (q.pregunta.includes("número de teléfono")) {

        if (!/^\d+$/.test(text)) {
            botSay("❌ El teléfono solo puede contener números");
            return;
        }

        telefonoIngresado = text;

        confirmar(
            `Ingresaste ${areaIngresada} - ${telefonoIngresado}, ¿es correcto?`,
            async () => {
                await saveAnswer(q.id, `${areaIngresada}${telefonoIngresado}`);
                await avanzar();
            },
            resetChatbot
        );
        return;
    }

    // ================= EMAIL =================
    if (q.pregunta.includes("correo electrónico")) {

        if (!validarEmail(text)) {
            botSay("❌ El correo no tiene un formato válido");
            return;
        }

        confirmar(
            `Ingresaste el correo:\n${text}\n¿Es correcto?`,
            async () => {
                await saveAnswer(q.id, text);
                await avanzar();
            },
            resetChatbot
        );
        return;
    }

    // ================= OTROS =================
    await saveAnswer(q.id, text);
    await avanzar();
}

// ===================================================================
// AVANZAR FLUJO
// ===================================================================
async function avanzar() {
    currentStep++;

    if (currentStep >= flowQuestions.length) {
        waitingForAsesor = true;
        await finalizarChatbot();
        return;
    }

    const q = flowQuestions[currentStep];
    botSay(q.pregunta);

    if (q.tipo === "opcion") showOptions(q.opciones);
    else {
        chatInput.disabled = false;
        chatInput.focus();
    }
}

// ===================================================================
// FINALIZAR CHATBOT
// ===================================================================
async function finalizarChatbot() {
    try {
        await fetch("/system/api/chat/mark_waiting_asesor.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ chat_id: chatId })
        });
    } catch {}

    window.chatbotActivo = false;
    botSay("👨‍💼 Un asesor se comunicará contigo.");
}

// ===================================================================
// RESET
// ===================================================================
function resetChatbot() {
    currentStep = 0;
    areaIngresada = "";
    telefonoIngresado = "";
    dniValidadoData = null;
    window.chatbotActivo = true;

    botSay("👋 Empecemos de nuevo");

    setTimeout(() => {
        const q = flowQuestions[0];
        botSay(q.pregunta);
        if (q.tipo === "opcion") showOptions(q.opciones);
    }, 500);
}

// ===================================================================
// MENSAJE HUMANO (FUERA DEL CHATBOT)
// ===================================================================
async function enviarMensajeLibre(text) {
    if (!chatId) return;

    const fd = new FormData();
    fd.append("chat_id", chatId);
    fd.append("sender", "cliente");
    fd.append("message", text);

    const r = await fetch("/system/api/messages/send_message.php", {
        method: "POST",
        body: fd
    });

    const d = await r.json();
    if (d.success) addUserMessage(text);
}

// ===================================================================
// GUARDAR RESPUESTA
// ===================================================================
async function saveAnswer(questionId, answer, optionId = 0, cuil = "", nombre = "", dni = "") {
    if (!window.chatbotActivo) return;

    const fd = new FormData();
    fd.append("chat_id", chatId);
    fd.append("question_id", questionId);
    fd.append("answer", answer);
    fd.append("option_id", optionId);

    if (cuil) fd.append("cuil", cuil);
    if (nombre) fd.append("nombre", nombre);
    if (dni) fd.append("dni", dni);

    await fetch("/system/api/chatbot/save_answer.php", {
        method: "POST",
        body: fd
    });
}
