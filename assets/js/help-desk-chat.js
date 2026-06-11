const trigger = document.getElementById('helpDeskTrigger');
const popup = document.getElementById('helpDeskPopup');
const backdrop = document.getElementById('helpDeskBackdrop');
const closeButton = document.getElementById('closeHelpDesk');
const form = document.getElementById('helpDeskForm');
const input = document.getElementById('helpDeskInput');
const messages = document.getElementById('helpDeskMessages');
const suggestions = document.getElementById('chatSuggestions');
// const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

function openPopup() {
    popup.hidden = false;
    backdrop.hidden = false;
    document.body.classList.add('chat-open');
    input.focus();
}

function closePopup() {
    console.trace('CLOSE POPUP');

    popup.hidden = true;
    backdrop.hidden = true;
    document.body.classList.remove('chat-open');
}

function chatBox(element, message) {
    const userMessage = document.createElement('div');
    userMessage.className = 'chat-message user';
    userMessage.textContent = message;

    const timestamp = document.createElement('div');
    timestamp.className = 'chat-timestamp';
    timestamp.textContent = 'Sekarang';
    userMessage.appendChild(timestamp);

    return userMessage;
}

trigger.addEventListener('click', function (event) {
    openPopup();
})
closeButton.addEventListener('click', function (event) {
    closePopup();
    event.preventDefault();
})
form.addEventListener('submit', async function (event) {    
    event.preventDefault();

    const text = input.value.trim();
    if (!text) {
        return;
    }

    const userMessage = document.createElement('div');
    userMessage.className = 'chat-message user';
    userMessage.textContent = text;

    const timestamp = document.createElement('div');
    timestamp.className = 'chat-timestamp';
    timestamp.textContent = 'Sekarang';
    userMessage.appendChild(timestamp);

    messages.appendChild(userMessage);
    messages.scrollTop = messages.scrollHeight;

    input.value = '';

    try {
        const response = await fetch('http://127.0.0.1:8000/chat/', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ question: text })
        });

        const data = await response.json();        
        
        const assistantMessage = document.createElement('div');
        assistantMessage.className = 'chat-message assistant';
        assistantMessage.textContent = data.answer || 'Terima kasih, kami akan meninjau permintaan Anda dan membalas segera.';

        const assistantTimestamp = document.createElement('div');
        assistantTimestamp.className = 'chat-timestamp';
        assistantTimestamp.textContent = 'Baru saja';
        assistantMessage.appendChild(assistantTimestamp);

        messages.appendChild(assistantMessage);
        messages.scrollTop = messages.scrollHeight;
    } catch (error) {
        console.error('Failed to send chat message', error);

        const assistantMessage = document.createElement('div');
        assistantMessage.className = 'chat-message assistant';
        assistantMessage.textContent = 'Maaf, pesan tidak dapat dikirim. Silakan coba lagi.';

        console.log("Sorry");
        

        const assistantTimestamp = document.createElement('div');
        assistantTimestamp.className = 'chat-timestamp';
        assistantTimestamp.textContent = 'Baru saja';
        assistantMessage.appendChild(assistantTimestamp);

        messages.appendChild(assistantMessage);
        messages.scrollTop = messages.scrollHeight;
    }
});

if (suggestions) {
    suggestions.addEventListener('click', function (event) {
        const target = event.target;
        if (target.matches('.suggestion-box')) {
            input.value = target.textContent;
            input.focus();
        }
    });
}


if (!trigger || !popup || !backdrop || !closeButton || !form || !input || !messages) {
    console.warn('Help desk chat elements not found');
} else {
    function openPopup() {
        popup.hidden = false;
        backdrop.hidden = false;
        document.body.classList.add('chat-open');
        input.focus();
    }

    function closePopup() {
        popup.hidden = true;
        backdrop.hidden = true;
        document.body.classList.remove('chat-open');
    }

    trigger.addEventListener('click', function (event) {
        event.preventDefault();
        openPopup();
    });

    closeButton.addEventListener('click', function (event) {
        closePopup();
        event.preventDefault();
    });

    backdrop.addEventListener('click', function (event) {
        if (event.target === backdrop) {
            closePopup();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && popup.hidden === false) {
            closePopup();
        }
    });
}

