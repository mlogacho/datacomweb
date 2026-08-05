document.addEventListener('DOMContentLoaded', function() {
    const launcher = document.getElementById('daia-chat-launcher');
    const chatWindow = document.getElementById('daia-chat-window');
    const closeBtn = document.getElementById('daia-close-btn');
    const chatWidget = document.getElementById('daia-chat-widget');
    const messagesContainer = document.getElementById('daia-chat-messages');
    const inputField = document.getElementById('daia-chat-input');
    const sendBtn = document.getElementById('daia-send-btn');
    const inputArea = document.getElementById('daia-chat-input-area');
    
    // LOPDP Consent Elements
    const consentOverlay = document.getElementById('daia-consent-overlay');
    const consentCheckbox = document.getElementById('daia-consent-checkbox');
    const consentBtn = document.getElementById('daia-consent-btn');

    let conversationHistory = [];

    // Check Consent state
    const hasConsent = localStorage.getItem('daiaConsent');
    const savedSession = sessionStorage.getItem('daiaConversation');

    if (savedSession && hasConsent) {
        conversationHistory = JSON.parse(savedSession);
        renderHistory();
        consentOverlay.style.display = 'none';
        inputArea.style.display = 'flex';
        // Si hay historial, mostrar chat abierto
        if(conversationHistory.length > 1) {
            openChat();
        }
    } else {
        // Mensaje inicial de bienvenida
        addMessage('assistant', '¡Hola! 👋 Soy DAIA, tu asesora comercial virtual en DataCom. \n\nMe encantaría ayudarte a impulsar tu empresa con nuestra tecnología. ¿Qué proyectos tienes en mente el día de hoy?\n\n(Si gustas, puedes dejarme tu nombre y correo para brindarte una atención más personalizada 😉)');
        
        if (hasConsent) {
            consentOverlay.style.display = 'none';
            inputArea.style.display = 'flex';
        } else {
            consentOverlay.style.display = 'flex';
            inputArea.style.display = 'none';
        }
    }

    // Consent Logic
    consentCheckbox.addEventListener('change', function() {
        consentBtn.disabled = !this.checked;
    });

    consentBtn.addEventListener('click', function() {
        localStorage.setItem('daiaConsent', 'true');
        consentOverlay.style.display = 'none';
        inputArea.style.display = 'flex';
        inputField.focus();
    });

    // Toggle Chat Window
    launcher.addEventListener('click', openChat);
    closeBtn.addEventListener('click', closeChat);

    function openChat() {
        chatWidget.classList.remove('daia-chat-closed');
        chatWidget.classList.add('daia-chat-open');
        inputField.focus();
    }

    function closeChat() {
        chatWidget.classList.remove('daia-chat-open');
        chatWidget.classList.add('daia-chat-closed');
    }

    // Send Message
    sendBtn.addEventListener('click', sendMessage);
    inputField.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    function sendMessage() {
        const text = inputField.value.trim();
        if (!text) return;

        inputField.value = '';
        addMessage('user', text);
        showTypingIndicator();

        // Prepare fetch
        fetch(daiaBotData.restUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': daiaBotData.nonce
            },
            body: JSON.stringify({ messages: conversationHistory })
        })
        .then(response => response.json())
        .then(data => {
            removeTypingIndicator();
            if (data.reply) {
                addMessage('assistant', data.reply);
            } else {
                addMessage('assistant', 'Lo siento, hubo un error de conexión. Intenta de nuevo en unos minutos.');
            }
        })
        .catch(error => {
            removeTypingIndicator();
            addMessage('assistant', 'Disculpa, estoy experimentando dificultades técnicas. Vuelve a intentarlo pronto.');
            console.error('DAIA Error:', error);
        });
    }

    function addMessage(role, content) {
        conversationHistory.push({ role, content });
        sessionStorage.setItem('daiaConversation', JSON.stringify(conversationHistory));
        appendMessageUI(role, content);
    }

    function renderHistory() {
        messagesContainer.innerHTML = '';
        conversationHistory.forEach(msg => {
            appendMessageUI(msg.role, msg.content);
        });
    }

    function appendMessageUI(role, content) {
        const msgDiv = document.createElement('div');
        msgDiv.classList.add('daia-message', 'daia-message-' + role);
        
        let htmlContent = content.replace(/\n/g, '<br>');
        
        // Detectar si hay viñetas simples o texto crudo y mejorarlo (básico)
        msgDiv.innerHTML = `<div class="daia-bubble">${htmlContent}</div>`;
        
        messagesContainer.appendChild(msgDiv);
        scrollToBottom();
    }

    function showTypingIndicator() {
        const typingDiv = document.createElement('div');
        typingDiv.id = 'daia-typing';
        typingDiv.classList.add('daia-message', 'daia-message-assistant');
        typingDiv.innerHTML = `
            <div class="daia-bubble daia-typing-bubble">
                <span class="daia-dot"></span>
                <span class="daia-dot"></span>
                <span class="daia-dot"></span>
            </div>
        `;
        messagesContainer.appendChild(typingDiv);
        scrollToBottom();
    }

    function removeTypingIndicator() {
        const typingDiv = document.getElementById('daia-typing');
        if (typingDiv) {
            typingDiv.remove();
        }
    }

    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
});
