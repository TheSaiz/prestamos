/* ============================================================
   CHATBOT FLOW v2 — Préstamo Líder
   Flujo inteligente: DNI → Validación → Opciones → Banco → API
   ============================================================ */

let flowQuestions = [];
let currentStep = 0;

/* ============================
   UI HELPERS
   ============================ */

function addBotMessage(text) {
    const msg = document.createElement("div");
    msg.className = "msg-bot";
    msg.innerHTML = text.replace(/\n/g, "<br>");
    chatMessages.appendChild(msg);
    scrollToBottom();
}

function addUserMessage(text) {
    const msg = document.createElement("div");
    msg.className = "msg-user";
    msg.innerHTML = escapeHtml(text);
    chatMessages.appendChild(msg);
    scrollToBottom();
}

function showOptions(options) {
    const optionsContainer = document.createElement("div");
    optionsContainer.className = "chat-options-container";

    options.forEach(option => {
        const btn = document.createElement("button");
        btn.textContent = option.texto;
        btn.className = "chat-option-btn";
        btn.onclick = async () => {
            addUserMessage(option.texto);
            await saveAnswer(chatId, flowQuestions[currentStep].id, option.texto, option.id);
            nextStep();
        };
        optionsContainer.appendChild(btn);
    });

    chatMessages.appendChild(optionsContainer);
    scrollToBottom();
}

/* ============================
   LOAD CHATBOT FLOW
   ============================ */

async function loadChatbotFlow() {
    const response = await fetch("api/chatbot/get_full_flow.php");
    const data = await response.json();

    if (!data.success) {
        addBotMessage("⚠️ Error cargando el asistente. Intenta nuevamente.");
        return;
    }

    flowQuestions = data.data;
}

/* ============================
   HANDLE FLOW
   ============================ */

async function nextStep() {
    currentStep++;

    if (currentStep >= flowQuestions.length) {
        submitPrestamo(chatId);
        return;
    }

    const q = flowQuestions[currentStep];

    addBotMessage(q.pregunta);

    if (q.tipo === "opcion") {
        showOptions(q.opciones);
    } else {
        waitingForText = true;
        chatInput.disabled = false;
        chatInput.placeholder = "Escribe tu respuesta...";
        chatInput.focus();
    }
}

async function handleUserInput(text) {
    const q = flowQuestions[currentStep];

    addUserMessage(text);

    // ———————————————
    // PREGUNTA 2 — DNI
    // ———————————————
    if (q.id === 2) {
        const dniValidation = await validateDNI(text, chatId, q.id);

        if (dniValidation === true) nextStep();
        return;
    }

    // Teléfono
    if (q.id === 4) {
        const clean = text.replace(/\D/g, "");
        if (clean.length !== 10) {
            addBotMessage("❌ El teléfono debe tener 10 dígitos. Ej: 1123456789");
            return;
        }
    }

    // Email
    if (q.id === 5) {
        const emailOK = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(text);
        if (!emailOK) {
            addBotMessage("❌ Ingresa un email válido");
            return;
        }
    }

    await saveAnswer(chatId, q.id, text);
    nextStep();
}

/* ============================
   SAVE ANSWER
   ============================ */

async function saveAnswer(chatId, questionId, answer, optionId = 0, cuil = "", nombre = "") {
    const formData = new FormData();
    formData.append("chat_id", chatId);
    formData.append("question_id", questionId);
    formData.append("answer", answer);
    formData.append("option_id", optionId);

    if (cuil) formData.append("cuil", cuil);
    if (nombre) formData.append("nombre", nombre);

    const response = await fetch("api/chatbot/save_answer.php", {
        method: "POST",
        body: formData
    });

    const result = await response.json();
    return result.success;
}

/* ============================
   DNI VALIDATION
   ============================ */

async function validateDNI(dni, chatId, questionId) {
    try {
        const response = await fetch("api/chatbot/validar_dni.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ dni })
        });

        const result = await response.json();

        if (!result.success) {
            addBotMessage("❌ " + result.message);
            return false;
        }

        // Caso 1 — un solo CUIL
        if (result.casos === 1) {
            addBotMessage(`✅ Hola **${result.nombre}**`);
            await saveAnswer(chatId, questionId, dni, 0, result.cuil, result.nombre);
            return true;
        }

        // Caso 2 — varios CUIL
        addBotMessage("Encontramos más de un registro. Seleccioná tu identidad:");

        result.opciones.forEach(op => {
            const btn = document.createElement("button");
            btn.className = "chat-option-btn";
            btn.innerHTML = `${op.nombre}<br><small>${op.cuil}</small>`;

            btn.onclick = async () => {
                addUserMessage(op.nombre);
                await saveAnswer(chatId, questionId, op.nombre, 0, op.cuil, op.nombre);
                nextStep();
            };

            chatMessages.appendChild(btn);
        });

        return "multi";
    } catch (_) {
        addBotMessage("❌ Error validando DNI");
        return false;
    }
}

/* ============================
   SUBMIT PRESTAMO
   ============================ */

async function submitPrestamo(chatId) {
    addBotMessage("⏳ Procesando tu solicitud...");

    const formData = new FormData();
    formData.append("chat_id", chatId);

    const response = await fetch("api/chatbot/submit_prestamo.php", {
        method: "POST",
        body: formData
    });

    const result = await response.json();

    if (!result.success) {
        addBotMessage("❌ " + (result.message || "Error procesando solicitud"));
        return;
    }

    const d = result.data;

    addBotMessage(`
        <div class="final-message ${d.status}">
            <h3>${d.titulo}</h3>
            <p>${d.mensaje}</p>
            ${d.whatsapp ? `
                <a href="https://api.whatsapp.com/send?phone=${d.whatsapp}&text=Hola,%20me%20registré%20en%20la%20página" 
                   class="whatsapp-btn" target="_blank">📱 WhatsApp</a>
            ` : ""}
        </div>
    `);

    waitingForText = false;
    chatInput.disabled = true;
    chatSendBtn.disabled = true;
}
